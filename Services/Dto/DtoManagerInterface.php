<?php

namespace Mell\Bundle\SimpleDtoBundle\Services\Dto;

use Mell\Bundle\SimpleDtoBundle\Model\DtoCollectionInterface;
use Mell\Bundle\SimpleDtoBundle\Model\DtoInterface;

interface DtoManagerInterface
{
    /**
     * @param object $entity
     * @param string $dtoType
     * @param string $group
     * @param array $fields
     * @return DtoInterface
     */
    public function createDto($entity, $dtoType, $group, array $fields);

    /**
     * @param array $collection
     * @param string $dtoType
     * @param $group
     * @param array $fields
     * @return DtoCollectionInterface
     */
    public function createDtoCollection($collection, $dtoType, $group, array $fields = []);

    /**
     * @param object $entity
     * @param DtoInterface $dto
     * @return object
     * @internal param string $group
     */
    public function createEntityFromDto($entity, DtoInterface $dto);

    /**
     * @param string $dtoType
     * @return bool
     */
    public function hasConfig($dtoType);

    /**
     * @param string $dtoType
     * @return array
     */
    public function getConfig($dtoType);
}
