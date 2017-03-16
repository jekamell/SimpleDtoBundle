<?php

namespace Mell\Bundle\SimpleDtoBundle\Services\Dto;

use Mell\Bundle\SimpleDtoBundle\Event\ApiEvent;
use Mell\Bundle\SimpleDtoBundle\Model\Dto;
use Mell\Bundle\SimpleDtoBundle\Model\DtoCollection;
use Mell\Bundle\SimpleDtoBundle\Model\DtoInterface;
use Mell\Bundle\SimpleDtoBundle\Model\DtoManagerConfigurator;
use Mell\Bundle\SimpleDtoBundle\Model\DtoSerializable;
use Mell\Bundle\SimpleDtoBundle\Serializer\Normalizer\DtoNormalizer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\Serializer;

/**
 * Class DtoManager
 * @package Mell\Bundle\SimpleDtoBundle\Services\Dto
 */
class DtoManager
{
    /** @var Serializer */
    protected $serializer;
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;
    /** @var DtoManagerConfigurator */
    protected $configurator;

    /**
     * DtoManager constructor.
     * @param Serializer $serializer
     * @param EventDispatcherInterface $eventDispatcher
     * @param DtoManagerConfigurator $configurator
     */
    public function __construct(
        Serializer $serializer,
        EventDispatcherInterface $eventDispatcher,
        DtoManagerConfigurator $configurator
    ) {
        $this->serializer = $serializer;
        $this->eventDispatcher = $eventDispatcher;
        $this->configurator = $configurator;
    }

    /**
     * @param DtoSerializable $entity
     * @param string $group
     * @param array $fields
     * @param bool $throwEvent
     * @return Dto
     */
    public function createDto(DtoSerializable $entity, string $group, array $fields, bool $throwEvent = true): Dto
    {
        $dto = new Dto($entity, $group);

        if ($throwEvent) {
            $this->dispatch(new ApiEvent($dto, ApiEvent::ACTION_CREATE_DTO), ApiEvent::EVENT_PRE_DTO_ENCODE);
        }

        $dto->setRawData(
            $this->serializer->normalize(
                $entity,
                DtoNormalizer::FORMAT_DTO,
                [
                    'groups' => [$group],
                    'fields' => $fields,
                ]
            )
        );

        return $dto;
    }

    /**
     * @param array $collection
     * @param string $group
     * @param array $fields
     * @param null|integer $count
     * @return DtoCollection
     */
    public function createDtoCollection(array $collection, string $group, array $fields = [], ?int $count = null): DtoCollection
    {
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
     * Convert dto to given entity
     * @param object $entity
     * @param DtoInterface $dto
     * @return object
     */
    public function createEntityFromDto($entity, DtoInterface $dto)
    {
//        $dtoConfig = $this->getConfig();
//        $group = $dto->getGroup();
//        $this->validateDto($dto, $dtoConfig, $dto->getType());
//
//        $fieldsConfig = $this->getDtoConfig($dto->getType())['fields'];
//        foreach ($dto->getRawData() as $property => $value) {
//            if (!isset($fieldsConfig[$property])) {
//                throw new BadRequestHttpException(sprintf('%s: field "%s" is not defined', $dto->getType(), $property));
//            }
//            if (!empty($fieldsConfig[$property]['readonly'])) {
//                continue;
//            }
//            if (isset($fieldsConfig[$property]['groups']) && !in_array($group, $fieldsConfig[$property]['groups'])) {
//                continue;
//            }
//            $setter = $this->getFieldSetter($fieldsConfig, $property);
//            call_user_func([$entity, $setter], $value);
//        }
//
//        return $entity;
    }

    /**
     * @param $entity
     * @param array $dtoData Predefined dto data
     * @param array $fields Required fields
     * @param array $config Fields configuration
     * @param string $group Dto group
     */
    protected function processFields($entity, array &$dtoData, array $fields, array $config, $group)
    {
//        /** @var array $options */
//        foreach ($config as $field => $options) {
//            // field was not required (@see dtoManager::getRequiredFields)
//            if (!empty($fields) && !in_array($field, $fields)) {
//                continue;
//            }
//            // field is not allowed for specified group
//            if (!empty($options['groups']) && !in_array($group, $options['groups'])) {
//                continue;
//            }
//
//            $getter = $this->getFieldGetter($options, $field);
//            $value = call_user_func([$entity, $getter]);
//            $dtoData[$field] = $this->dtoHelper->castValueType($options['type'], $value);
//        }
    }

    /**
     * @param $fieldsConfig
     * @param $property
     * @return string
     */
    private function getFieldSetter($fieldsConfig, $property)
    {
//        return isset($fieldsConfig[$property]['setter'])
//            ? $fieldsConfig[$property]['setter']
//            : $this->dtoHelper->getFieldSetter($property);
    }

    /**
     * @param $options
     * @param $field
     * @return string
     */
    private function getFieldGetter($options, $field)
    {
//        return isset($options['getter']) ? $options['getter'] : $this->dtoHelper->getFieldGetter($field);
    }

    /**
     * @param ApiEvent $apiEvent
     * @param string $eventName
     */
    private function dispatch(ApiEvent $apiEvent, $eventName)
    {
        $this->eventDispatcher->dispatch($eventName, $apiEvent);
    }
}
