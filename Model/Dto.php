<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\Model;

/**
 * Class Dto
 * @package Mell\Bundle\SimpleDtoBundle\Model
 */
class Dto implements \JsonSerializable
{
    /** @var array */
    private $data;
    /** @var object */
    private $entity;
    /** @var string */
    private $group;

    /**
     * Dto constructor.
     * @param object $entity
     * @param string|null $group
     * @param array $data
     * @internal param string $type
     */
    public function __construct($entity, string $group, array $data = [])
    {
        $this->data = $data;
        $this->entity = $entity;
        $this->group = $group;
    }

    /**
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    function jsonSerialize()
    {
        return $this->data;
    }

    /** @return array */
    public function getRawData()
    {
        return $this->data;
    }

    /**
     * @inheritdoc
     */
    public function setRawData(array $data)
    {
        $this->data = $data;

        return $this;
    }


    /**
     * Set dto source
     *
     * @param $data
     * @return $this
     */
    public function setOriginalData($data)
    {
        $this->entity = $data;

        return $this;
    }

    /**
     * get dto source
     *
     * @return mixed
     */
    public function getOriginalData()
    {
        return $this->entity;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param string $group
     * @return $this
     */
    public function setGroup($group)
    {
        $this->group = $group;

        return $this;
    }
}
