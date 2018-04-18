<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\Services\Crud;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Mell\Bundle\SimpleDtoBundle\Event\ApiEvent;
use Mell\Bundle\SimpleDtoBundle\Model\Dto;
use Mell\Bundle\SimpleDtoBundle\Model\DtoInterface;
use Mell\Bundle\SimpleDtoBundle\Model\DtoSerializableInterface;
use Mell\Bundle\SimpleDtoBundle\Services\Dto\DtoManager;
use Mell\Bundle\SimpleDtoBundle\Services\RequestManager\RequestManager;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class CrudManager
 */
class CrudManager
{
    const FORMAT_JSON = 'json';
    const FORMAT_XML = 'xml';
    const CONTENT_TYPE_JSON = 'application/json';
    const CONTENT_TYPE_XML = 'application/xml';

    /** @var EntityManagerInterface */
    protected $entityManager;
    /** @var ValidatorInterface */
    protected $validator;
    /** @var SerializerInterface */
    protected $serializer;
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;
    /** @var DtoManager */
    protected $dtoManager;
    /** @var RequestManager */
    protected $requestManager;

    /**
     * CrudManager constructor.
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     * @param SerializerInterface $serializer
     * @param EventDispatcherInterface $eventDispatcher
     * @param DtoManager $dtoManager
     * @param RequestManager $requestManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        SerializerInterface $serializer,
        EventDispatcherInterface $eventDispatcher,
        DtoManager $dtoManager,
        RequestManager $requestManager
    ) {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->serializer = $serializer;
        $this->eventDispatcher = $eventDispatcher;
        $this->dtoManager = $dtoManager;
        $this->requestManager = $requestManager;
    }

    /**
     * @param DtoSerializableInterface $entity
     * @param array $data
     * @param callable|null $accessChecker
     * @param string $format @see self::FORMAT_JSON|self::FORMAT_XML
     * @return DtoSerializableInterface|ConstraintViolationListInterface
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     */
    public function createResource(
        DtoSerializableInterface $entity,
        array $data,
        Callable $accessChecker = null,
        string $format = self::FORMAT_JSON
    ) {
        $event = new ApiEvent($entity, ApiEvent::ACTION_CREATE, ['group' => 'create']);

        $this->eventDispatcher->dispatch(ApiEvent::EVENT_PRE_DESERIALIZE, $event);

        $entity = $this->dtoManager->deserializeEntity(
            $entity,
            $this->serializer->serialize($data, $format),
            $format,
            DtoInterface::DTO_GROUP_CREATE
        );

        $this->eventDispatcher->dispatch(ApiEvent::EVENT_POST_DESERIALIZE, $event);

        if ($accessChecker) {
            call_user_func($accessChecker);
        }

        $this->eventDispatcher->dispatch(ApiEvent::EVENT_PRE_VALIDATE, $event);

        $errors = $this->validator->validate($entity);
        if ($errors->count()) {
            return $errors;
        }

        $this->eventDispatcher->dispatch(ApiEvent::EVENT_PRE_PERSIST, $event);
        $this->entityManager->persist($entity);

        $this->eventDispatcher->dispatch(ApiEvent::EVENT_PRE_FLUSH, $event);
        $this->entityManager->flush();

        $event = new ApiEvent($entity, ApiEvent::ACTION_CREATE);
        $this->eventDispatcher->dispatch(ApiEvent::EVENT_POST_FLUSH, $event);

        return $entity;
    }

    /**
     * @param DtoSerializableInterface $entity
     * @return Dto
     */
    public function readResource(DtoSerializableInterface $entity): Dto
    {
        $event = new ApiEvent($entity, ApiEvent::ACTION_READ);
        $this->eventDispatcher->dispatch(ApiEvent::EVENT_POST_READ, $event);

        return $this->dtoManager->createDto($entity, DtoInterface::DTO_GROUP_READ, $this->requestManager->getFields());
    }

    /**
     * @param DtoSerializableInterface $entity
     * @param array $data
     * @param callable|null $accessChecker
     * @param string $format
     * @return DtoSerializableInterface|ConstraintViolationListInterface
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     */
    public function updateResource(
        DtoSerializableInterface $entity,
        array $data,
        Callable $accessChecker = null,
        string $format = self::FORMAT_JSON
    ) {
        $event = new ApiEvent($entity, ApiEvent::ACTION_UPDATE, ['group' => 'update']);

        $this->eventDispatcher->dispatch(ApiEvent::EVENT_PRE_DESERIALIZE, $event);

        $entity = $this->dtoManager->deserializeEntity(
            $entity,
            $this->serializer->serialize($data, $format),
            $format,
            DtoInterface::DTO_GROUP_UPDATE
        );

        $this->eventDispatcher->dispatch(ApiEvent::EVENT_POST_DESERIALIZE, $event);

        if ($accessChecker) {
            call_user_func($accessChecker);
        }

        $this->eventDispatcher->dispatch(ApiEvent::EVENT_PRE_VALIDATE, $event);

        $errors = $this->validator->validate($entity);
        if ($errors->count()) {
            return $errors;
        }

        $this->eventDispatcher->dispatch(ApiEvent::EVENT_PRE_FLUSH, $event);
        $this->entityManager->flush();
        $event = new ApiEvent($entity, ApiEvent::ACTION_UPDATE);
        $this->eventDispatcher->dispatch(ApiEvent::EVENT_POST_FLUSH, $event);

        return $entity;
    }

    /**
     * @param $entity
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     */
    public function deleteResource($entity): void
    {
        $this->entityManager->remove($entity);

        $event = new ApiEvent($entity, ApiEvent::ACTION_DELETE);
        $this->eventDispatcher->dispatch(ApiEvent::EVENT_PRE_FLUSH, $event);

        $this->entityManager->flush();
        $event = new ApiEvent($entity, ApiEvent::ACTION_DELETE);
        $this->eventDispatcher->dispatch(ApiEvent::EVENT_POST_FLUSH, $event);
    }
}
