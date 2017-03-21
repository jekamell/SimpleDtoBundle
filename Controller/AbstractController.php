<?php

namespace Mell\Bundle\SimpleDtoBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Mell\Bundle\SimpleDtoBundle\Model\ApiFilterCollectionInterface;
use Mell\Bundle\SimpleDtoBundle\Model\Dto;
use Mell\Bundle\SimpleDtoBundle\Model\DtoCollection;
use Mell\Bundle\SimpleDtoBundle\Model\DtoInterface;
use Mell\Bundle\SimpleDtoBundle\Event\ApiEvent;
use Mell\Bundle\SimpleDtoBundle\Model\DtoSerializableInterface;
use Mell\Bundle\SimpleDtoBundle\Services\Dto\DtoManager;
use Mell\Bundle\SimpleDtoBundle\Services\RequestManager\RequestManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Class AbstractController
 */
abstract class AbstractController extends Controller
{
    const FORMAT_JSON = 'json';
    const FORMAT_XML = 'xml';
    const CONTENT_TYPE_JSON = 'application/json';
    const CONTENT_TYPE_XML = 'application/xml';

    /** @return string */
    abstract public function getEntityAlias();

    /**
     * @param Request $request
     * @param DtoSerializableInterface $entity
     * @return DtoSerializableInterface|ConstraintViolationListInterface
     */
    protected function createResource(Request $request, DtoSerializableInterface $entity)
    {
        $entity = $this->getDtoManager()->deserializeEntity($entity, $request->getContent(), $this->getInputFormat());

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

        return $entity;
    }

    /**
     * @param Request $request
     * @param DtoSerializableInterface $entity
     * @return Dto|ConstraintViolationListInterface
     */
    protected function updateResource(Request $request, DtoSerializableInterface $entity)
    {
        if (!$data = $this->getSerializer()->decode($request->getContent(), $this->getInputFormat())) {
            throw new BadRequestHttpException('Malformed request data');
        }

        $dto = new Dto($entity, DtoInterface::DTO_GROUP_UPDATE, $data);
        $entity = $this->getDtoManager()->deserializeEntity($entity, $request->getContent(), $this->getInputFormat());

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
     * @param DtoSerializableInterface $entity
     * @return Dto
     */
    protected function readResource(DtoSerializableInterface $entity): Dto
    {
        $event = new ApiEvent($entity, ApiEvent::ACTION_READ);
        $this->getEventDispatcher()->dispatch(ApiEvent::EVENT_POST_READ, $event);

        return $this->getDtoManager()->createDto(
            $entity,
            DtoInterface::DTO_GROUP_READ,
            $this->getRequestManager()->getFields()
        );
    }

    /**
     * @param $entity
     */
    protected function deleteResource($entity): void
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
     * @return DtoCollection
     */
    protected function listResources(
        QueryBuilder $queryBuilder,
        ApiFilterCollectionInterface $filters = null
    ): DtoCollection {
        $event = new ApiEvent($queryBuilder, ApiEvent::ACTION_LIST, ['filters' => $filters]);
        $this->getEventDispatcher()->dispatch(ApiEvent::EVENT_PRE_COLLECTION_LOAD, $event);

        $paginator = new Paginator($queryBuilder->getQuery());
        $collection = iterator_to_array($paginator);
        if ($this->getRequestManager()->isCountRequired()) {
            $count = $paginator->count();
        }

        $event = new ApiEvent($collection, ApiEvent::ACTION_LIST);
        $this->getEventDispatcher()->dispatch(ApiEvent::EVENT_POST_COLLECTION_LOAD, $event);

        return $this->getDtoManager()->createDtoCollection(
            $collection,
            DtoInterface::DTO_GROUP_READ,
            $this->getRequestManager()->getFields(),
            $count ?? null
        );
    }

    /**
     * @param string $alias
     * @return QueryBuilder
     */
    protected function getQueryBuilder($alias = 't'): QueryBuilder
    {
        $queryBuilder = $this->getEntityManager()->getRepository($this->getEntityAlias())->createQueryBuilder($alias);
        $associationNames = $this->getEntityManager()->getClassMetadata($this->getEntityAlias())->getAssociationNames();
        foreach (array_keys($this->getRequestManager()->getExpands()) as $expand) {
            if (in_array($expand, $associationNames)) {
                $queryBuilder->leftJoin($alias . '.' . $expand, $expand);
                $queryBuilder->addSelect($expand);
            }
        }

        return $queryBuilder;
    }

    /**
     * @param Dto|DtoCollection|ConstraintViolationListInterface $data
     * @param int $statusCode
     * @return Response
     */
    protected function serializeResponse($data, int $statusCode = Response::HTTP_OK): Response
    {
        if ($data instanceof ConstraintViolationListInterface) {
            return $this->handleValidationError($data);
        }

        return new Response(
            $this->get('serializer')->serialize($data, $this->getOutputFormat()),
            $statusCode,
            ['Content-Type' => $this->getContentTypeByFormat($this->getOutputFormat())]
        );
    }

    /**
     * @param ConstraintViolationListInterface $data
     * @return Response
     */
    protected function handleValidationError(ConstraintViolationListInterface $data): Response
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

        return new Response(
            $this->getSerializer()->encode($errors, $this->getOutputFormat()),
            Response::HTTP_UNPROCESSABLE_ENTITY,
            ['Content-Type' => $this->getContentTypeByFormat($this->getOutputFormat())]
        );
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @return int
     */
    protected function getCollectionCount(QueryBuilder $queryBuilder): int
    {
        $rootAlias = current($queryBuilder->getRootAliases());
        $builder = clone $queryBuilder;
        $builder->select('count(' . $rootAlias . ')');

        return (int)$builder->getQuery()->getSingleScalarResult();
    }

    /**
     * @return string
     */
    protected function getOutputFormat(): string
    {
        $request = $this->getRequest();
        if (!$request->headers->has('Accept')) {
            throw new BadRequestHttpException('Request \'Accept\' http header is required');
        }
        if (strpos($request->headers->get('Accept'), self::CONTENT_TYPE_JSON) !== false) {
            return self::FORMAT_JSON;
        }
        if (strpos($request->headers->get('Accept'), self::CONTENT_TYPE_XML) !== false) {
            return self::FORMAT_XML;
        }

        throw new BadRequestHttpException(
            sprintf(
                'Request \'Accept\' http header must contain one of %s',
                implode(',', [self::CONTENT_TYPE_JSON, self::CONTENT_TYPE_XML])
            )
        );
    }

    /**
     * @return string
     */
    protected function getInputFormat(): string
    {
        $request = $this->getRequest();
        if (!$request->headers->has('Content-type')) {
            throw new BadRequestHttpException('Request \'Content-type\' http header is required');
        }
        if ($request->headers->get('Content-type') === self::CONTENT_TYPE_JSON) {
            return self::FORMAT_JSON;
        }
        if ($request->headers->get('Content-type') === self::CONTENT_TYPE_XML) {
            return self::FORMAT_XML;
        }

        throw new BadRequestHttpException(
            sprintf(
                'Request \'Content-type\' http header must be one of %s',
                implode(',', [self::CONTENT_TYPE_JSON, self::CONTENT_TYPE_XML])
            )
        );
    }

    /**
     * @param string $format
     * @return string
     */
    protected function getContentTypeByFormat(string $format): string
    {
        if ($format === self::FORMAT_JSON) {
            return self::CONTENT_TYPE_JSON;
        }
        if ($format === self::FORMAT_XML) {
            return self::CONTENT_TYPE_XML;
        }

        throw new \InvalidArgumentException(
            sprintf('Format must be one of %s', implode(',', [self::FORMAT_JSON, self::FORMAT_XML]))
        );
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager(): EntityManager
    {
        return $this->get('doctrine.orm.entity_manager');
    }

    /**
     * @return DtoManager
     */
    protected function getDtoManager(): DtoManager
    {
        return $this->get('simple_dto.dto_manager');
    }

    /**
     * @return TraceableEventDispatcher
     */
    protected function getEventDispatcher(): TraceableEventDispatcher
    {
        return $this->get('event_dispatcher');
    }

    /**
     * @return RequestManager
     */
    protected function getRequestManager(): RequestManager
    {
        return $this->get('simple_dto.request_manager');
    }

    /**
     * @return Serializer
     */
    protected function getSerializer(): Serializer
    {
        return $this->get('serializer');
    }

    /**
     * @return Request
     */
    protected function getRequest(): Request
    {
        return $this->get('request_stack')->getCurrentRequest();
    }
}
