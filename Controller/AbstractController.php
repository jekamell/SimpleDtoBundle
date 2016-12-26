<?php

namespace Mell\Bundle\SimpleDtoBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Mell\Bundle\SimpleDtoBundle\Model\ApiFilter;
use Mell\Bundle\SimpleDtoBundle\Model\ApiFilterCollectionInterface;
use Mell\Bundle\SimpleDtoBundle\Model\Dto;
use Mell\Bundle\SimpleDtoBundle\Model\DtoCollectionInterface;
use Mell\Bundle\SimpleDtoBundle\Model\DtoInterface;
use Mell\Bundle\SimpleDtoBundle\Event\ApiEvent;
use Mell\Bundle\SimpleDtoBundle\Services\Dto\DtoManager;
use Mell\Bundle\SimpleDtoBundle\Services\RequestManager\RequestManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

abstract class AbstractController extends Controller
{
    const FORMAT_JSON = 'json';

    const LIST_LIMIT_DEFAULT = 100;
    const LIST_LIMIT_MAX = 1000;

    /** @return string */
    abstract public function getDtoType();

    /** @return string */
    abstract public function getEntityAlias();

    /** @return array */
    abstract public function getAllowedExpands();

    /**
     * @param Request $request
     * @param $entity
     * @param string|null $dtoGroup
     * @return DtoInterface|Response
     */
    protected function createResource(Request $request, $entity, $dtoGroup = null)
    {
        if (!$data = json_decode($request->getContent(), true)) {
            throw new BadRequestHttpException('Missing json data');
        }

        $dto = new Dto($this->getDtoType(), null, $dtoGroup ?: DtoInterface::DTO_GROUP_CREATE, $data);
        $entity = $this->getDtoManager()->createEntityFromDto($entity, $dto);

        $event = new ApiEvent($entity, ApiEvent::ACTION_CREATE);
        $this->getEventDispatcher()->dispatch(ApiEvent::EVENT_PRE_VALIDATE, $event);

        $errors = $this->get('validator')->validate($entity);
        if ($errors->count()) {
            return $errors;
        }

        $this->getEventDispatcher()->dispatch(ApiEvent::EVENT_PRE_PERSIST, $event);
        $this->getEntityManager()->persist($entity);

        $this->getEventDispatcher()->dispatch(ApiEvent::EVENT_PRE_FLUSH, $event);
        $this->getEntityManager()->flush();

        $event = new ApiEvent($entity, ApiEvent::ACTION_CREATE);
        $this->getEventDispatcher()->dispatch(ApiEvent::EVENT_POST_FLUSH, $event);

        return $this->getDtoManager()->createDto(
            $entity,
            $this->getDtoType(),
            $dtoGroup ?: DtoInterface::DTO_GROUP_READ,
            $this->get('simple_dto.request_manager')->getFields()
        );
    }

    /**
     * @param Request $request
     * @param $entity
     * @param string|null $dtoGroup
     * @return DtoInterface
     */
    protected function updateResource(Request $request, $entity, $dtoGroup = null)
    {
        if (!$data = json_decode($request->getContent(), true)) {
            throw new BadRequestHttpException('Missing json data');
        }

        $dto = new Dto($this->getDtoType(), $entity, $dtoGroup ?: DtoInterface::DTO_GROUP_UPDATE, $data);
        $entity = $this->getDtoManager()->createEntityFromDto($entity, $dto);

        $event = new ApiEvent($entity, ApiEvent::ACTION_UPDATE);
        $this->getEventDispatcher()->dispatch(ApiEvent::EVENT_PRE_VALIDATE, $event);

        $errors = $this->get('validator')->validate($entity);
        if ($errors->count()) {
            return $errors;
        }

        $this->getEventDispatcher()->dispatch(ApiEvent::EVENT_PRE_FLUSH, $event);
        $this->getEntityManager()->flush();
        $event = new ApiEvent($entity, ApiEvent::ACTION_UPDATE);
        $this->getEventDispatcher()->dispatch(ApiEvent::EVENT_POST_FLUSH, $event);

        return $dto;
    }

    /**
     * @param $entity
     * @param string|null $dtoGroup
     * @return DtoInterface
     */
    protected function readResource($entity, $dtoGroup = null)
    {
        $event = new ApiEvent($entity, ApiEvent::ACTION_READ);
        $this->getEventDispatcher()->dispatch(ApiEvent::EVENT_POST_READ, $event);

        return $this->getDtoManager()->createDto(
            $entity,
            $this->getDtoType(),
            $dtoGroup ?: DtoInterface::DTO_GROUP_READ,
            $this->get('simple_dto.request_manager')->getFields()
        );
    }

    /**
     * @param $entity
     */
    protected function deleteResource($entity)
    {
        $this->getEntityManager()->remove($entity);

        $event = new ApiEvent($entity, ApiEvent::ACTION_DELETE);
        $this->getEventDispatcher()->dispatch(ApiEvent::EVENT_PRE_FLUSH, $event);

        $this->getEntityManager()->flush();
        $event = new ApiEvent($entity, ApiEvent::ACTION_DELETE);
        $this->getEventDispatcher()->dispatch(ApiEvent::EVENT_POST_FLUSH, $event);
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param ApiFilterCollectionInterface $filters
     * @param string $dtoGroup
     * @return DtoCollectionInterface
     */
    protected function listResources(
        QueryBuilder $queryBuilder,
        ApiFilterCollectionInterface $filters = null,
        $dtoGroup = null
    ) {
        $event = new ApiEvent($queryBuilder, ApiEvent::ACTION_LIST);
        $this->getEventDispatcher()->dispatch(ApiEvent::EVENT_PRE_COLLECTION_LOAD, $event);

        if ($filters) {
            $this->processFilters($queryBuilder, $filters);
        }

        $this->processLimit($queryBuilder);
        $this->processOffset($queryBuilder);
        $this->processSort($queryBuilder);

        $paginator = new Paginator($queryBuilder->getQuery());
        $collection = $paginator;
        if ($this->getRequestManager()->isCountRequired()) {
            $count = $paginator->count();
        }

        $event = new ApiEvent($collection, ApiEvent::ACTION_LIST);
        $this->getEventDispatcher()->dispatch(ApiEvent::EVENT_POST_COLLECTION_LOAD, $event);

        return $this->getDtoManager()->createDtoCollection(
            $collection,
            $this->getDtoType(),
            $dtoGroup ?: DtoInterface::DTO_GROUP_LIST,
            $this->get('simple_dto.request_manager')->getFields(),
            isset($count) ? $count : null
        );
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->get('doctrine.orm.entity_manager');
    }

    /**
     * @param string $alias
     * @return QueryBuilder
     */
    protected function getQueryBuilder($alias = 't')
    {
        $queryBuilder = $this->getEntityManager()->getRepository($this->getEntityAlias())->createQueryBuilder($alias);
        $associationNames = $this->getEntityManager()->getClassMetadata($this->getEntityAlias())->getAssociationNames();
        foreach (array_keys($this->get('simple_dto.request_manager')->getExpands()) as $expand) {
            if (in_array($expand, $associationNames)) {
                $queryBuilder->leftJoin($alias . '.' . $expand, $expand);
                $queryBuilder->addSelect($expand);
            }
        }

        if (!empty($this->get('simple_dto.request_manager')->getExpands())) {
            $queryBuilder->addGroupBy($alias . '.id'); // TODO: use paginator instead
        }

        return $queryBuilder;
    }

    /**
     * @param DtoInterface|ConstraintViolationListInterface $data
     * @param int $statusCode
     * @param string $format
     * @return Response
     */
    protected function serializeResponse($data, $statusCode = Response::HTTP_OK, $format = self::FORMAT_JSON)
    {
        if ($data instanceof ConstraintViolationListInterface) {
            return $this->handleValidationError($data);
        }

        return new Response(
            $this->get('serializer')->serialize($data, $format, []),
            $statusCode,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * @param ConstraintViolationListInterface $data
     * @return JsonResponse
     */
    protected function handleValidationError(ConstraintViolationListInterface $data)
    {
        $errors = [];
        /** @var \Symfony\Component\Validator\ConstraintViolation $violation */
        foreach ($data as $violation) {
            if ($violation->getPropertyPath()) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            } else {
                $errors['_error'] = $violation->getMessage();
            }
        }

        return new JsonResponse($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    protected function processLimit(QueryBuilder $queryBuilder)
    {
        $limit = $this->getRequestManager()->getLimit() ?: static::LIST_LIMIT_DEFAULT;
        $queryBuilder->setMaxResults(min($limit, static::LIST_LIMIT_MAX));
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    protected function processOffset(QueryBuilder $queryBuilder)
    {
        $offset = $this->getRequestManager()->getOffset();
        if (!empty($offset)) {
            $queryBuilder->setFirstResult($offset);
        }
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    protected function processSort(QueryBuilder $queryBuilder)
    {
        $sort = $this->getRequestManager()->getSort();
        if (!empty($sort)) {
            $rootAliases = $queryBuilder->getRootAliases();
            foreach ($sort as $param => $direction) {
                $queryBuilder->addOrderBy(current($rootAliases) . '.' . $param, $direction);
            }
        }
    }

    /**
     * Append filter criteria to query builder
     * @param QueryBuilder $queryBuilder
     * @param ApiFilterCollectionInterface $filters
     */
    protected function processFilters(QueryBuilder $queryBuilder, ApiFilterCollectionInterface $filters)
    {
        $apiFiltersManager = $this->get('simple_dto.api_filter_manager');

        /** @var ApiFilter $filter */
        foreach ($filters as $i => $filter) {
            $queryBuilder->andWhere(
                sprintf(
                    current($queryBuilder->getRootAliases()) . '.%s %s %s',
                    $filter->getParam(),
                    $apiFiltersManager->getSqlOperationByOperation($filter->getOperation()),
                    $apiFiltersManager->isArrayOperation($filter->getOperation())
                        ? '(:' . $filter->getParam() . $i . ')'
                        : ':' . $filter->getParam() . $i
                )
            );
            $queryBuilder->setParameter($filter->getParam() . $i, $filter->getValue());
        }
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @return int
     */
    protected function getCollectionCount(QueryBuilder $queryBuilder)
    {
        $rootAlias = current($queryBuilder->getRootAliases());
        $builder = clone $queryBuilder;
        $builder->select('count(' . $rootAlias . ')');

        return (int)$builder->getQuery()->getSingleScalarResult();
    }

    /**
     * @return DtoManager
     */
    protected function getDtoManager()
    {
        return $this->get('simple_dto.dto_manager');
    }

    /**
     * @return object|ContainerAwareEventDispatcher|TraceableEventDispatcher
     */
    protected function getEventDispatcher()
    {
        return $this->get('event_dispatcher');
    }

    /**
     * @return RequestManager|object
     */
    protected function getRequestManager()
    {
        return $this->get('simple_dto.request_manager');
    }
}
