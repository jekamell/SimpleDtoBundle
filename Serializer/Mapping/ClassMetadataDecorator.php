<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\Serializer\Mapping;

use Symfony\Component\Serializer\Mapping\AttributeMetadataInterface;
use Symfony\Component\Serializer\Mapping\ClassMetadataInterface;

/**
 * Class ClassMetadata
 */
class ClassMetadataDecorator implements ClassMetadataInterface
{
    /** @var ClassMetadataInterface */
    protected $decorated;
    /** @var array */
    protected $expands = [];
    /** @var array */
    protected $links = [];

    /**
     * ClassMetadata constructor.
     * @param ClassMetadataInterface $decorated
     */
    public function __construct(ClassMetadataInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * @return array
     */
    public function getExpands(): array
    {
        return $this->expands;
    }

    /**
     * @param array $expands
     */
    public function setExpands(array $expands): void
    {
        $this->expands = $expands;
    }

    /**
     * @return array
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * @param array $links
     */
    public function setLinks(array $links): void
    {
        $this->links = $links;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->decorated->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeMetadata(AttributeMetadataInterface $attributeMetadata): void
    {
        $this->decorated->addAttributeMetadata($attributeMetadata);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributesMetadata(): array
    {
        return $this->decorated->getAttributesMetadata();
    }

    /**
     * {@inheritdoc}
     */
    public function merge(ClassMetadataInterface $classMetadata): void
    {
        $this->decorated->merge($classMetadata);
    }

    /**
     * {@inheritdoc}
     */
    public function getReflectionClass(): \ReflectionClass
    {
        return $this->decorated->getReflectionClass();
    }
}
