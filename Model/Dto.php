<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\Model;

/**
 * Class Dto
 */
class Dto implements DtoInterface
{
    /** @var array */
    private $data;
    /** @var object */
    private $entity;
    /** @var string */
    private $group;

    /**
     * Dto constructor.
     * @param DtoSerializableInterface $entity
     * @param string $group
     * @param array $data
     */
    public function __construct(DtoSerializableInterface $entity, string $group, array $data = [])
    {
        $this->data = $data;
        $this->entity = $entity;
        $this->group = $group;
    }

    /**
     * {@inheritdoc}
     */
    function jsonSerialize(): array
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function getRawData(): array
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function setRawData(array $data): void
    {
        $this->data = $data;
    }


    /**
     * {@inheritdoc}
     */
    public function setOriginalData($data): void
    {
        $this->entity = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getOriginalData()
    {
        return $this->entity;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * {@inheritdoc}
     */
    public function setGroup(string $group): void
    {
        $this->group = $group;
    }
}
