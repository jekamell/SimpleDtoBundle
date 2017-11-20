<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\Model;

/**
 * Class Relation
 */
class Relation
{
    const TARGET_ENTITY_ATTRIBUTE_DEFAULT = 'id';
    const REPOSITORY_METHOD_DEFAULT = 'findOneBy';

    /** @var string */
    private $relation;
    /** @var */
    private $targetEntityClass;
    /** @var */
    private $attribute;
    /** @var array */
    private $groups = [];
    /** @var string */
    private $targetEntityAttribute = self::TARGET_ENTITY_ATTRIBUTE_DEFAULT;
    /** @var string */
    private $repositoryMethod = self::REPOSITORY_METHOD_DEFAULT;
    /** @var */
    private $setter;

    /**
     * Relation constructor.
     * @param string $relation
     * @param $targetEntityClass
     * @param $attribute
     * @param array $groups
     * @param string $targetEntityAttribute
     * @param string $repositoryMethod
     * @param $setter
     */
    public function __construct(
        string $relation,
        string $targetEntityClass,
        string $attribute,
        array $groups = [],
        ?string $targetEntityAttribute = null,
        ?string $repositoryMethod = null,
        ?string $setter = null
    ) {
        $this->relation = $relation;
        $this->targetEntityClass = $targetEntityClass;
        $this->attribute = $attribute;
        $this->groups = $groups;
        if ($targetEntityAttribute) {
            $this->targetEntityAttribute = $targetEntityAttribute;
        }
        if ($repositoryMethod) {
            $this->repositoryMethod = $repositoryMethod;
        }
        if ($setter) {
            $this->setter = $setter;
        }
    }

    /**
     * @return string
     */
    public function getRelation(): string
    {
        return $this->relation;
    }

    /**
     * @param string $name
     */
    public function setRelation(string $relation): Relation
    {
        $this->relation = $relation;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTargetEntityClass(): string
    {
        return $this->targetEntityClass;
    }

    /**
     * @param mixed $targetEntityClass
     */
    public function setTargetEntityClass(string $targetEntityClass): Relation
    {
        $this->targetEntityClass = $targetEntityClass;

        return $this;
    }

    /**
     * @return string
     */
    public function getAttribute(): string
    {
        return $this->attribute;
    }

    /**
     * @param mixed $attribute
     */
    public function setAttribute(string $attribute): Relation
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * @return array
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * @param array $groups
     */
    public function setGroups(array $groups): Relation
    {
        $this->groups = $groups;

        return $this;
    }

    /**
     * @return string
     */
    public function getTargetEntityAttribute(): ?string
    {
        return $this->targetEntityAttribute;
    }

    /**
     * @param string $targetEntityAttribute
     */
    public function setTargetEntityAttribute(string $targetEntityAttribute): Relation
    {
        $this->targetEntityAttribute = $targetEntityAttribute;

        return $this;
    }

    /**
     * @return string
     */
    public function getRepositoryMethod(): string
    {
        return $this->repositoryMethod;
    }

    /**
     * @param string $repositoryMethod
     */
    public function setRepositoryMethod(string $repositoryMethod): Relation
    {
        $this->repositoryMethod = $repositoryMethod;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSetter(): ?string
    {
        return $this->setter;
    }

    /**
     * @param mixed $setter
     */
    public function setSetter($setter): Relation
    {
        $this->setter = $setter;

        return $this;
    }
}
