<?php

namespace Mell\Bundle\SimpleDtoBundle\Services\Dto;

use Mell\Bundle\SimpleDtoBundle\Helpers\DtoHelper;
use Mell\Bundle\SimpleDtoBundle\Model\Dto;
use Mell\Bundle\SimpleDtoBundle\Model\DtoCollection;
use Mell\Bundle\SimpleDtoBundle\Model\DtoInterface;

/**
 * Class DtoExpandsManager
 * @package Mell\Bundle\SimpleDtoBundle\Services\Dto
 */
class DtoExpandsManager
{
    /** @var DtoHelper */
    protected $dtoHelper;

    /**
     * DtoExpandsManager constructor.
     * @param DtoHelper $dtoHelper
     */
    public function __construct(DtoHelper $dtoHelper)
    {
        $this->dtoHelper = $dtoHelper;
    }

    /**
     * @param DtoInterface $dto
     * @param array $expands
     * @param array $config
     * @return DtoInterface
     */
    public function processExpands(DtoInterface $dto, array $expands, array $config)
    {
        $data = [];
        foreach ($expands as $expand => $fields) {
            $expandConfig = $config[$dto->getType()]['expands'][$expand];
            $getter = !empty($expandConfig['getter'])
                ? $expandConfig['getter']
                : $this->dtoHelper->getFieldGetter($expand);
            if (!$expandObject = call_user_func([$dto->getOriginalData(), $getter])) {
                continue;
            }
            if (is_array($expandObject) || $expandObject instanceof \ArrayAccess) {
                $expandsCollection = $this->createDtoCollection(
                    $expandObject,
                    $expandConfig['type'],
                    $config,
                    $fields,
                    $dto->getGroup()
                );
                $data['_expands'][$expand] = $expandsCollection;
            } else {
                $expandObject = $this->createDto(
                    $expandObject,
                    $expandConfig['type'],
                    $fields,
                    $config,
                    $dto->getGroup()
                );
                $data['_expands'][$expand] = $expandObject;
            }
        }

        return $dto->append($data);
    }

    /**
     * @param object $entity
     * @param string $type
     * @param array $fields
     * @param array $config
     * @param string $group
     * @return Dto
     */
    protected function createDto($entity, $type, array $fields, array $config, $group)
    {
        $dto = new Dto($type, $entity, []);
        $config = $config[$type];
        /** @var array $options */
        foreach ($config['fields'] as $field => $options) {
            // field was not required (@see dtoManager::getRequiredFields)
            if (!empty($fields) && !in_array($field, $fields)) {
                continue;
            }
            // field is not allowed for specified group
            if (!empty($options['groups']) && !in_array($group, $options['groups'])) {
                continue;
            }

            $getter = isset($options['getter']) ? $options['getter'] : $this->dtoHelper->getFieldGetter($field);
            $value = call_user_func([$entity, $getter]);
            $data[$field] = $this->dtoHelper->castValueType($options['type'], $value);
            $dto->append([$field => $value]);
        }

        return $dto;
    }

    /**
     * @param array $collection
     * @param string $type
     * @param array $config
     * @param array $fields
     * @param string $group
     * @return DtoCollection
     */
    protected function createDtoCollection($collection, $type, array $config, array $fields, $group)
    {
        $data = [];
        foreach ($collection as $item) {
            $data[] = $this->createDto($item, $type, $fields, $config, $group);
        }

        return new DtoCollection($type, $collection, false, $group, $data);
    }
}
