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
     * @param array $expands
     * @return DtoInterface
     */
    public function createDto($entity, $dtoType, $group, array $fields = [], array $expands = []);

    /**
     * @param array $collection
     * @param string $dtoType
     * @param $group
     * @param array $fields
     * @param array $expands
     * @param string|null $collectionKey
     * @return DtoCollectionInterface
     */
    public function createDtoCollection(
        array $collection,
        $dtoType, $group,
        array $fields = [],
        array $expands = [],
        $collectionKey = null
    );

    /**
     * @param object $entity
     * @param DtoInterface $dto
     * @param string $dtoType
     * @param string $group
     * @return object
     */
    public function createEntityFromDto($entity, DtoInterface $dto, $dtoType, $group);

    /**
     * @param string $config
     * @return bool
     */
    public function hasConfig($config);
}
