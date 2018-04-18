<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\Services\Dto;

use Mell\Bundle\SimpleDtoBundle\Event\ApiEvent;
use Mell\Bundle\SimpleDtoBundle\Model\Dto;
use Mell\Bundle\SimpleDtoBundle\Model\DtoCollection;
use Mell\Bundle\SimpleDtoBundle\Model\DtoManagerConfigurator;
use Mell\Bundle\SimpleDtoBundle\Model\DtoSerializableInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class DtoManager
 */
class DtoManager
{
    /** @var SerializerInterface */
    protected $serializer;
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;
    /** @var DtoManagerConfigurator */
    protected $configurator;

    /**
     * DtoManager constructor.
     * @param SerializerInterface $serializer
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(SerializerInterface $serializer, EventDispatcherInterface $eventDispatcher)
    {
        $this->serializer = $serializer;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param DtoManagerConfigurator $configurator
     */
    public function setConfigurator(DtoManagerConfigurator $configurator): void
    {
        $this->configurator = $configurator;
    }

    /**
     * @param DtoSerializableInterface $entity
     * @param string $group
     * @param array $fields
     * @param bool $throwEvent
     * @return Dto
     */
    public function createDto(
        DtoSerializableInterface $entity,
        string $group,
        array $fields,
        bool $throwEvent = true
    ): Dto {
        $dto = new Dto($entity, $group);

        if ($throwEvent) {
            $this->dispatch(new ApiEvent($dto, ApiEvent::ACTION_CREATE_DTO), ApiEvent::EVENT_PRE_DTO_ENCODE);
        }

        $dto->setRawData(
            $this->serializer->normalize(
                $entity,
                null,
                array_merge(['groups' => [$group],], $fields ? ['attributes' => $fields] : [])
            )
        );

        if ($throwEvent) {
            $this->dispatch(new ApiEvent($dto, ApiEvent::ACTION_CREATE_DTO), ApiEvent::EVENT_POST_DTO_ENCODE);
        }

        return $dto;
    }

    /**
     * @param array $collection
     * @param string $group
     * @param array $fields
     * @param null|integer $count
     * @return DtoCollection
     */
    public function createDtoCollection(
        array $collection,
        string $group,
        array $fields = [],
        ?int $count = null
    ): DtoCollection {
        $dtoCollection = new DtoCollection(
            $collection,
            $this->configurator->getCollectionKey(),
            $group,
            [],
            $count
        );
        $this->dispatch(
            new ApiEvent($dtoCollection, ApiEvent::ACTION_CREATE_DTO_COLLECTION),
            ApiEvent::EVENT_PRE_DTO_COLLECTION_ENCODE
        );

        foreach ($collection as $entity) {
            $dtoCollection->append($this->createDto($entity, $group, $fields, false));
        }

        $this->dispatch(
            new ApiEvent($dtoCollection, ApiEvent::ACTION_CREATE_DTO_COLLECTION),
            ApiEvent::EVENT_POST_DTO_COLLECTION_ENCODE
        );

        return $dtoCollection;
    }

    /**
     * @param DtoSerializableInterface $entity
     * @param string $data
     * @param string $format
     * @param string $group
     * @return DtoSerializableInterface
     */
    public function deserializeEntity(
        DtoSerializableInterface $entity,
        string $data,
        string $format,
        string $group
    ): DtoSerializableInterface {
        $this->serializer->deserialize(
            $data,
            get_class($entity),
            $format,
            [
                'object_to_populate' => $entity,
                'groups' => [$group],
            ]
        );

        return $entity;
    }

    /**
     * @param ApiEvent $apiEvent
     * @param string $eventName
     */
    private function dispatch(ApiEvent $apiEvent, string $eventName): void
    {
        $this->eventDispatcher->dispatch($eventName, $apiEvent);
    }
}
