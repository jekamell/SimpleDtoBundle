<?php

namespace Mell\Bundle\SimpleDtoBundle\Services\Dto\Builder;

use Mell\Bundle\SimpleDtoBundle\Helpers\DtoHelper;
use Mell\Bundle\SimpleDtoBundle\Model\DtoCollection;
use Mell\Bundle\SimpleDtoBundle\Model\DtoInterface;
use Mell\Bundle\SimpleDtoBundle\Services\Dto\DtoValidator;
use Mell\Bundle\SimpleDtoBundle\Services\RequestManager\RequestManager;

/**
 * Append expands information to DTO
 *
 * Class ExpandsBuilder
 * @package Mell\Bundle\SimpleDtoBundle\Services\Dto\Builder
 */
class ExpandsBuilder implements DtoBuilderInterface
{
    /** @var RequestManager */
    protected $requestManager;
    /** @var DtoValidator */
    protected $dtoValidator;
    /** @var DtoHelper */
    protected $dtoHelper;

    /**
     * ExpandsBuilder constructor.
     * @param RequestManager $requestManager
     * @param DtoValidator $dtoValidator
     * @param DtoHelper $dtoHelper
     */
    public function __construct(RequestManager $requestManager, DtoValidator $dtoValidator, DtoHelper $dtoHelper)
    {
        $this->requestManager = $requestManager;
        $this->dtoValidator = $dtoValidator;
        $this->dtoHelper = $dtoHelper;
    }

    /**
     * @param object $entity Data source for dto
     * @param DtoInterface $dto
     * @param array $config Whole dto configuration
     * @param string $type Dto type
     * @param string|null $group Dto group to process
     * @return null
     */
    public function build($entity, DtoInterface $dto, array $config, $type, $group = null)
    {
        $expands = $this->requestManager->getExpands();
        if (!$expands || empty($config[$type]['expands'])) {
            return;
        }
        $this->dtoValidator->validateExpands($config, $expands);

        $data = [];
        foreach ($expands as $expand => $fields) {
            $expandConfig = $config[$expand];
            $getter = !empty($expandConfig['getter'])
                ? $expandConfig['getter']
                : $this->dtoHelper->getFieldGetter($expand);
            if (!$expandObject = call_user_func([$entity, $getter])) {
                continue;
            }
            if (is_array($expandObject) || $expandObject instanceof \ArrayAccess) {
                $expandsCollection = $this->createDtoCollection(
                    $expandObject,
                    $config[$expandConfig['type']],
                    $fields,
                    $group
                );
                $data['_expands'][$expand] = $expandsCollection;
            } else {
                $expandObject = $this->createDto($expandObject, $config[$expandConfig['type']], $fields, $group);
                $data['_expands'][$expand] = $expandObject;
            }
        }

        $dto->append($data);
    }

    /**
     * @param object $entity
     * @param array $config
     * @param array $fields
     * @param string $group
     * @return array
     */
    protected function createDto($entity, array $config, array $fields, $group)
    {
        $data = [];
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

            $getter = isset($options['getter']) ? $options['getter'] : $this->dtoHelper->getFieldGetter($field);
            $value = call_user_func([$entity, $getter]);
            $data[$field] = $this->dtoHelper->castValueType($options['type'], $value);
        }

        return $data;
    }

    /**
     * @param $collection
     * @param array $config
     * @param array $fields
     * @param $group
     * @return DtoCollection
     */
    protected function createDtoCollection($collection, array $config, array $fields, $group)
    {
        $data = [];
        foreach ($collection as $item) {
            $data[] = $this->createDto($item, $config, $fields, $group);
        }

        return new DtoCollection($data, false);
    }
}
