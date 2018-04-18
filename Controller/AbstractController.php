<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Mell\Bundle\SimpleDtoBundle\Model\ApiFilterCollectionInterface;
use Mell\Bundle\SimpleDtoBundle\Model\Dto;
use Mell\Bundle\SimpleDtoBundle\Model\DtoCollection;
use Mell\Bundle\SimpleDtoBundle\Model\DtoInterface;
use Mell\Bundle\SimpleDtoBundle\Event\ApiEvent;
use Mell\Bundle\SimpleDtoBundle\Model\DtoSerializableInterface;
use Mell\Bundle\SimpleDtoBundle\Services\Crud\CrudManager;
use Mell\Bundle\SimpleDtoBundle\Services\Dto\DtoManager;
use Mell\Bundle\SimpleDtoBundle\Services\RequestManager\RequestManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
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

    /** @var EntityManagerInterface */
    protected $entityManager;
    /** @var DtoManager */
    protected $dtoManager;
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;
    /** @var RequestManager */
    protected $requestManager;
    /** @var SerializerInterface */
    protected $serializer;
    /** @var CrudManager */
    protected $crudManager;
    /** @var RequestStack */
    protected $requestStack;

    /**
     * AbstractController constructor.
     * @param EntityManagerInterface $entityManager
     * @param DtoManager $dtoManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param RequestManager $requestManager
     * @param SerializerInterface $serializer
     * @param CrudManager $crudManager
     * @param RequestStack $requestStack
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        DtoManager $dtoManager,
        EventDispatcherInterface $eventDispatcher,
        RequestManager $requestManager,
        SerializerInterface $serializer,
        CrudManager $crudManager,
        RequestStack $requestStack
    ) {
        $this->entityManager = $entityManager;
        $this->dtoManager = $dtoManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->requestManager = $requestManager;
        $this->serializer = $serializer;
        $this->crudManager = $crudManager;
        $this->requestStack = $requestStack;
    }

    /**
     * @return string
     */
    abstract public function getEntityAlias(): string;

    /**
     * @param Request $request
     * @param DtoSerializableInterface $entity
     * @param callable|null $accessChecker
     * @return DtoSerializableInterface|ConstraintViolationListInterface
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function createResource(Request $request, DtoSerializableInterface $entity, Callable $accessChecker = null)
    {
        if (!$data = $this->getSerializer()->decode($request->getContent(), $this->getInputFormat())) {
            throw new BadRequestHttpException('Malformed request data');
        }

        return $this->get('simple_dto.crud_manager')->createResource($entity, $data, $accessChecker);
    }

    /**
     * @param Request $request
     * @param DtoSerializableInterface $entity
     * @param callable|null $accessChecker
     * @return DtoSerializableInterface|ConstraintViolationListInterface
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function updateResource(Request $request, DtoSerializableInterface $entity, Callable $accessChecker = null)
    {
        if (!$data = $this->getSerializer()->decode($request->getContent(), $this->getInputFormat())) {
            throw new BadRequestHttpException('Malformed request data');
        }

        return $this->getCrudManager()->updateResource($entity, $data, $accessChecker);
    }

    /**
     * @param DtoSerializableInterface $entity
     * @return Dto
     */
    protected function readResource(DtoSerializableInterface $entity): Dto
    {
        return $this->get('simple_dto.crud_manager')->readResource($entity);
    }

    /**
     * @param DtoSerializableInterface $entity
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function deleteResource(DtoSerializableInterface $entity): void
    {
        $this->get('simple_dto.crud_manager')->deleteResource($entity);
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
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
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
        if (!$request->headers->has('Accept') || $request->headers->get('Accept') === '*/*') {
            return static::FORMAT_JSON; // be default
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
        if (strpos($request->headers->get('Content-type'), self::CONTENT_TYPE_JSON) !== false) {
            return self::FORMAT_JSON;
        }
        if (strpos($request->headers->get('Content-type'), self::CONTENT_TYPE_XML) !== false) {
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
        return $this->entityManager;
    }

    /**
     * @return DtoManager
     */
    protected function getDtoManager(): DtoManager
    {
        return $this->dtoManager;
    }

    /**
     * @return EventDispatcherInterface
     */
    protected function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }

    /**
     * @return RequestManager
     */
    protected function getRequestManager(): RequestManager
    {
        return $this->requestManager;
    }

    /**
     * @return Serializer
     */
    protected function getSerializer(): Serializer
    {
        return $this->serializer;
    }

    /**
     * @return Request
     */
    protected function getRequest(): Request
    {
        return $this->requestStack->getCurrentRequest();
    }

    /**
     * @return CrudManager
     */
    protected function getCrudManager(): CrudManager
    {
        return $this->crudManager;
    }
}
