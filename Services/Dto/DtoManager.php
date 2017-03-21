<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\Services\Dto;

use Mell\Bundle\SimpleDtoBundle\Event\ApiEvent;
use Mell\Bundle\SimpleDtoBundle\Model\Dto;
use Mell\Bundle\SimpleDtoBundle\Model\DtoCollection;
use Mell\Bundle\SimpleDtoBundle\Model\DtoManagerConfigurator;
use Mell\Bundle\SimpleDtoBundle\Model\DtoSerializableInterface;
use Mell\Bundle\SimpleDtoBundle\Serializer\Normalizer\DtoNormalizer;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Serializer\Serializer;

/**
 * Class DtoManager
 * @package Mell\Bundle\SimpleDtoBundle\Services\Dto
 */
class DtoManager
{
    /** @var Serializer */
    protected $serializer;
    /** @var EventDispatcher */
    protected $eventDispatcher;
    /** @var DtoManagerConfigurator */
    protected $configurator;

    /**
     * DtoManager constructor.
     * @param Serializer $serializer
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct(Serializer $serializer, EventDispatcher $eventDispatcher)
    {
        $this->serializer = $serializer;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param DtoManagerConfigurator $configurator
     */
    public function setConfigurator(DtoManagerConfigurator $configurator)
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
                DtoNormalizer::FORMAT_DTO,
                ['groups' => [$group], 'fields' => $fields,]
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
     * @return DtoSerializableInterface
     */
    public function deserializeEntity(
        DtoSerializableInterface $entity,
        string $data,
        string $format
    ): DtoSerializableInterface {
        $this->serializer->deserialize($data, get_class($entity), $format, ['object_to_populate' => $entity]);

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
