<?php

namespace Mell\Bundle\SimpleDtoBundle\Services\Dto\Builder;

use Mell\Bundle\SimpleDtoBundle\Helpers\DtoHelper;
use Mell\Bundle\SimpleDtoBundle\Model\DtoInterface;
use Mell\Bundle\SimpleDtoBundle\Services\RequestManager\RequestManager;

/**
 * Append fields information to DTO
 *
 * Class FieldsBuilder
 * @package Mell\Bundle\SimpleDtoBundle\Services\Dto\Builder
 */
class FieldsBuilder implements DtoBuilderInterface
{
    /** @var RequestManager */
    protected $requestManager;
    /** @var DtoHelper */
    protected $dtoHelper;

    /**
     * FieldsBuilder constructor.
     * @param RequestManager $requestManager
     * @param DtoHelper $dtoHelper
     */
    public function __construct(RequestManager $requestManager, DtoHelper $dtoHelper)
    {
        $this->requestManager = $requestManager;
        $this->dtoHelper = $dtoHelper;
    }

    /**
     * @param object $entity Data source for dto
     * @param DtoInterface $dto
     * @param array $config
     * @param string $type Dto type
     * @param string|null $group Dto group to process
     * @return null
     */
    public function build($entity, DtoInterface $dto, array $config, $type, $group = null)
    {
        $fields = $this->requestManager->getFields();
        $fieldsConfig = $config[$type]['fields'];
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

            $getter = isset($options['getter']) ? $options['getter'] : $this->dtoHelper->getFieldGetter($field);
            $value = call_user_func([$entity, $getter]);
            $dto->append([$field => $this->dtoHelper->castValueType($options['type'], $value)]);
        }
    }
}
