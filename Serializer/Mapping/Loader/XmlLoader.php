<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\Serializer\Mapping\Loader;

use Mell\Bundle\SimpleDtoBundle\Serializer\Mapping\AttributeMetadata;
use Mell\Bundle\SimpleDtoBundle\Serializer\Mapping\ClassMetadataDecorator;
use Symfony\Component\Config\Util\XmlUtils;
use Symfony\Component\Serializer\Exception\MappingException;
use Symfony\Component\Serializer\Mapping\ClassMetadataInterface;
use Symfony\Component\Serializer\Mapping\Loader\XmlFileLoader;

/**
 * Class XmlLoader
 */
class XmlLoader extends XmlFileLoader
{
    /**
     * An array of {@class \SimpleXMLElement} instances.
     *
     * @var \SimpleXMLElement[]|null
     */
    private $classes;

    /**
     * {@inheritdoc}
     */
    public function loadClassMetadata(ClassMetadataInterface $classMetadata): bool
    {
        /** @var ClassMetadataDecorator $classMetadata */
        if (null === $this->classes) {
            $this->classes = $this->getClassesFromXml();
        }

        if (!$this->classes) {
            return false;
        }

        $attributesMetadata = $classMetadata->getAttributesMetadata();

        if (isset($this->classes[$classMetadata->getName()])) {
            $xml = $this->classes[$classMetadata->getName()];

            $this->processAttributes($classMetadata, $xml, $attributesMetadata);
            $this->processExpands($classMetadata, $xml);
            $this->processLinks($classMetadata, $xml);

            return true;
        }

        return false;
    }

    /**
     * Return the names of the classes mapped in this file.
     *
     * @return string[] The classes names
     */
    public function getMappedClasses(): array
    {
        if (null === $this->classes) {
            $this->classes = $this->getClassesFromXml();
        }

        return array_keys($this->classes);
    }

    /**
     * Parses a XML File.
     *
     * @param string $file Path of file
     * @return \SimpleXMLElement
     * @throws MappingException
     */
    private function parseFile($file): \SimpleXMLElement
    {
        try {
            $dom = XmlUtils::loadFile($file, __DIR__.'/schema/dic/serializer-mapping/serializer-mapping-1.0.xsd');
        } catch (\Exception $e) {
            throw new MappingException($e->getMessage(), $e->getCode(), $e);
        }

        return simplexml_import_dom($dom);
    }

    /**
     * @return array
     */
    private function getClassesFromXml(): array
    {
        $xml = $this->parseFile($this->file);
        $classes = [];

        foreach ($xml->class as $class) {
            $classes[(string) $class['name']] = $class;
        }

        return $classes;
    }

    /**
     * @param ClassMetadataDecorator $classMetadata
     * @param \SimpleXMLElement $xml
     * @param AttributeMetadata[] $attributesMetadata
     */
    protected function processAttributes(
        ClassMetadataDecorator $classMetadata,
        \SimpleXMLElement $xml,
        array $attributesMetadata
    ): void {
        foreach ($xml->attribute as $attribute) {
            $attributeName = (string)$attribute['name'];
            $attributeMetadata = new AttributeMetadata($attributeName);
            if (isset($attributesMetadata[$attributeName])) {
                $attributeMetadata->merge($attributesMetadata[$attributeName]);
            } else {
                $classMetadata->addAttributeMetadata($attributeMetadata);
            }
            foreach ($attribute->group as $group) {
                $attributeMetadata->addGroup((string)$group);
            }
            if (isset($attribute['max-depth'])) {
                $attributeMetadata->setMaxDepth((int)$attribute['max-depth']);
            }
        }
    }

    /**
     * @param ClassMetadataDecorator $classMetadata
     * @param \SimpleXMLElement $xml
     */
    protected function processExpands(ClassMetadataDecorator $classMetadata, \SimpleXMLElement $xml): void
    {
        $expands = [];
        foreach ($xml->expand as $expand) {
            $expands[] = (string) $expand['name'];
        }
        $classMetadata->setExpands($expands);
    }

    /**
     * @param ClassMetadataDecorator $classMetadata
     * @param \SimpleXMLElement $xml
     */
    protected function processLinks(ClassMetadataDecorator $classMetadata, \SimpleXMLElement $xml): void
    {
        $links = [];
        foreach ($xml->link as $link) {
            $links[(string) $link['name']] = [
                'route' => (string) $link['route'],
                'description' => $link['description'] ? (string) $link['description'] : ucfirst((string) $link['name']),
                'expression' => $link['expression'] ? (string) $link['expression'] : null,
            ];
        }
        $classMetadata->setLinks($links);
    }
}
