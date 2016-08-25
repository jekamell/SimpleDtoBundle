<?php

namespace Mell\Bundle\SimpleDtoBundle\Services\Dto\Builder;

use Mell\Bundle\SimpleDtoBundle\Model\DtoInterface;

/**
 * Interface DtoBuilderInterface
 * @package Mell\Bundle\SimpleDtoBundle\Services\Dto\Builder
 */
interface DtoBuilderInterface
{
    /**
     * @param object $entity Data source for dto
     * @param DtoInterface $dto
     * @param array $config Whole dto configuration
     * @param string $type
     * @param string|null $group Dto group to process
     * @return null
     */
    public function build($entity, DtoInterface $dto, array $config, $type, $group = null);
}
