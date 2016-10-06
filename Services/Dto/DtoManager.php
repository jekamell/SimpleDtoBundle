<?php

namespace Mell\Bundle\SimpleDtoBundle\Services\Dto;

use Mell\Bundle\SimpleDtoBundle\Event\ApiEvent;
use Mell\Bundle\SimpleDtoBundle\Exceptions\DtoException;
use Mell\Bundle\SimpleDtoBundle\Helpers\DtoHelper;
use Mell\Bundle\SimpleDtoBundle\Model\Dto;
use Mell\Bundle\SimpleDtoBundle\Model\DtoCollection;
use Mell\Bundle\SimpleDtoBundle\Model\DtoCollectionInterface;
use Mell\Bundle\SimpleDtoBundle\Model\DtoInterface;
use Mell\Bundle\SimpleDtoBundle\Model\DtoManagerConfigurator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class DtoManager
 * @package Mell\Bundle\SimpleDtoBundle\Services\Dto
 */
class DtoManager implements DtoManagerInterface
{
    /** @var DtoValidator */
    protected $dtoValidator;
    /** @var DtoHelper */
    protected $dtoHelper;
    /** @var DtoManagerConfigurator */
    protected $configurator;
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * DtoManager constructor.
     * @param DtoValidator $dtoValidator
     * @param DtoHelper $dtoHelper
     * @param DtoManagerConfigurator $configurator
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        DtoValidator $dtoValidator,
        DtoHelper $dtoHelper,
        DtoManagerConfigurator $configurator,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->dtoValidator = $dtoValidator;
        $this->dtoHelper = $dtoHelper;
        $this->configurator = $configurator;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Convert entity to dto
     * @param object $entity
     * @param string $dtoType
     * @param string $group
     * @param array $fields
     * @param bool $throwEvent
     * @return DtoInterface
     */
    public function createDto($entity, $dtoType, $group, array $fields, $throwEvent = true)
    {
        $dtoConfig = $this->dtoHelper->getDtoConfig();
        $this->validateDtoConfig($dtoConfig, $dtoType, $entity);

        $dto = new Dto($dtoType, $entity, $group);

        if ($throwEvent) {
            $this->dispatch(new ApiEvent($dto, ApiEvent::ACTION_CREATE_DTO), ApiEvent::EVENT_PRE_DTO_ENCODE);
        }

        $fieldsConfig = $dtoConfig[$dtoType]['fields'];
        /** @var array $options */
        foreach ($fieldsConfig as $field => $options) {
            // field was not required (@see dtoManager::getRequiredFields)
            if (!empty($fields) && !in_array($field, $fields)) {
                continue;
            }
            // field is not allowed for specified group
            if (!empty($options['groups']) && !in_array($group, $options['groups'])) {
                continue;
            }

            $getter = $this->getFieldGetter($options, $field);
            $value = call_user_func([$entity, $getter]);
            $dto->append([$field => $this->dtoHelper->castValueType($options['type'], $value)]);
        }

        if ($throwEvent) {
            $this->dispatch(new ApiEvent($dto, ApiEvent::ACTION_CREATE_DTO), ApiEvent::EVENT_POST_DTO_ENCODE);
        }

        return $dto;
    }

    /**
     * Create collection of dto's by given data
     * @param array $collection
     * @param string $dtoType
     * @param string $group
     * @param array $fields
     * @param null|integer $count
     * @return DtoCollectionInterface
     */
    public function createDtoCollection($collection, $dtoType, $group, array $fields = [], $count = null)
    {
        $dtoCollection = new DtoCollection(
            $dtoType,
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

        foreach ($collection as $item) {
            $dtoCollection->append($this->createDto($item, $dtoType, $group, $fields, false));
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
        $dtoConfig = $this->dtoHelper->getDtoConfig();
        $dtoType = $dto->getType();
        $group = $dto->getGroup();
        $this->validateDto($dto, $dtoConfig, $dtoType);

        $fieldsConfig = $dtoConfig[$dtoType]['fields'];
        foreach ($dto->getRawData() as $property => $value) {
            if (!isset($fieldsConfig[$property])) {
                throw new BadRequestHttpException(sprintf('%s: field "%s" is not defined', $dtoType, $property));
            }
            if (!empty($fieldsConfig[$property]['readonly'])) {
                continue;
            }
            if (isset($fieldsConfig[$property]['groups']) && !in_array($group, $fieldsConfig[$property]['groups'])) {
                continue;
            }
            $value = $this->dtoHelper->castValueType($fieldsConfig[$property]['type'], $value, false);
            $setter = $this->getFieldSetter($fieldsConfig, $property);
            call_user_func([$entity, $setter], $value);
        }

        return $entity;
    }

    /**
     * Whether is dto config exists
     * @param string $dtoType DtoConfig name (UserDto as example)
     * @return bool
     */
    public function hasConfig($dtoType)
    {
        return array_key_exists($dtoType, $this->dtoHelper->getDtoConfig());
    }

    /**
     * Get dto configuration by type
     * @param string $dtoType
     * @return array
     * @throws DtoException
     */
    public function getConfig($dtoType)
    {
        if ($this->hasConfig($dtoType)) {
            return $this->dtoHelper->getDtoConfig()[$dtoType];
        }

        throw new DtoException(sprintf('Dto config not found: %s', $dtoType));
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
        /** @var array $options */
        foreach ($config as $field => $options) {
            // field was not required (@see dtoManager::getRequiredFields)
            if (!empty($fields) && !in_array($field, $fields)) {
                continue;
            }
            // field is not allowed for specified group
            if (!empty($options['groups']) && !in_array($group, $options['groups'])) {
                continue;
            }

            $getter = $this->getFieldGetter($options, $field);
            $value = call_user_func([$entity, $getter]);
            $dtoData[$field] = $this->dtoHelper->castValueType($options['type'], $value);
        }
    }

    /**
     * @param array $dtoConfig
     * @param string $dtoType
     * @param $object
     */
    protected function validateDtoConfig($dtoConfig, $dtoType, $object)
    {
        $this->dtoValidator->validateDtoConfig($dtoConfig, $object, $dtoType);
    }

    /**
     * @param DtoInterface $dto
     * @param array $config
     * @param string $dtoType
     */
    protected function validateDto(DtoInterface $dto, $config, $dtoType)
    {
        $this->dtoValidator->validateDto($dto, $config, $dtoType);
    }

    /**
     * @param $fieldsConfig
     * @param $property
     * @return string
     */
    private function getFieldSetter($fieldsConfig, $property)
    {
        return isset($fieldsConfig[$property]['setter'])
            ? $fieldsConfig[$property]['setter']
            : $this->dtoHelper->getFieldSetter($property);
    }

    /**
     * @param $options
     * @param $field
     * @return string
     */
    private function getFieldGetter($options, $field)
    {
        return isset($options['getter']) ? $options['getter'] : $this->dtoHelper->getFieldGetter($field);
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
