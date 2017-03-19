<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\Serializer\Mapping\Loader;

use Mell\Bundle\SimpleDtoBundle\Serializer\Mapping\AttributeMetadata;
use Mell\Bundle\SimpleDtoBundle\Serializer\Mapping\ClassMetadataDecorator;
use Symfony\Component\Serializer\Exception\MappingException;
use Symfony\Component\Serializer\Mapping\ClassMetadataInterface;
use Symfony\Component\Serializer\Mapping\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Parser;

/**
 * Class YamlLoader
 */
class YamlLoader extends YamlFileLoader
{
    private $yamlParser;

    /** @var array */
    private $classes = null;

    /**
     * {@inheritdoc}
     */
    public function loadClassMetadata(ClassMetadataInterface $classMetadata)
    {
        /** @var ClassMetadataDecorator $classMetadata */
        if (null === $this->classes) {
            $this->classes = $this->getClassesFromYaml();
        }

        if (!$this->classes) {
            return false;
        }

        if (isset($this->classes[$classMetadata->getName()])) {
            $yaml = $this->classes[$classMetadata->getName()];
            if (isset($yaml['attributes']) && is_array($yaml['attributes'])) {
                $attributesMetadata = $classMetadata->getAttributesMetadata();

                foreach ($yaml['attributes'] as $attribute => $data) {
                    $attributeMetadata = new AttributeMetadata($attribute);
                    if (isset($attributesMetadata[$attribute])) {
                        $attributeMetadata->merge($attributesMetadata[$attribute]);
                    } else {
                        $attributesMetadata = $classMetadata->getAttributesMetadata();
                        $classMetadata->addAttributeMetadata($attributeMetadata);
                    }
                    if (isset($data['groups'])) {
                        if (!is_array($data['groups'])) {
                            throw new MappingException('The "groups" key must be an array of strings in "%s" for the attribute "%s" of the class "%s".', $this->file, $attribute, $classMetadata->getName());
                        }

                        foreach ($data['groups'] as $group) {
                            if (!is_string($group)) {
                                throw new MappingException('Group names must be strings in "%s" for the attribute "%s" of the class "%s".', $this->file, $attribute, $classMetadata->getName());
                            }
                            $attributeMetadata->addGroup($group);
                        }
                    }
                    if (isset($data['max_depth'])) {
                        if (!is_int($data['max_depth'])) {
                            throw new MappingException('The "max_depth" value must be an integer in "%s" for the attribute "%s" of the class "%s".', $this->file, $attribute, $classMetadata->getName());
                        }
                        $attributeMetadata->setMaxDepth($data['max_depth']);
                    }
                    if (isset($data['type'])) {
                        if (!in_array($data['type'], AttributeMetadata::getAvailableTypes())) {
                            throw new MappingException('Type must be one of ' . implode(', ', AttributeMetadata::getAvailableTypes()) . ' in "%s" for the attribute "%s" of the class "%s".', $this->file, $attribute, $classMetadata->getName());
                        }
                        $attributeMetadata->setType($data['type']);
                    }
                    if (isset($data['description'])) {
                        $attributeMetadata->setDescription($data['description']);
                    }
                    if (isset($data['required'])) {
                        if (!is_bool($data['required'])) {
                            throw new MappingException('The "required" value must be an boolean in "%s" for the attribute "%s" of the class "%s".', $this->file, $attribute, $classMetadata->getName());
                        }
                        $attributeMetadata->setRequired($data['required']);
                    }
                }
            }

            if (isset($yaml['expands']) && is_array($yaml['expands'])) {
                foreach ($yaml['expands'] as $expand) {
                    if (!is_string($expand)) {
                        throw new MappingException();
                    }
                }

                $classMetadata->setExpands($yaml['expands']);
            }

            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    private function getClassesFromYaml(): array
    {
        if (!stream_is_local($this->file)) {
            throw new MappingException(sprintf('This is not a local file "%s".', $this->file));
        }

        if (null === $this->yamlParser) {
            $this->yamlParser = new Parser();
        }

        $classes = $this->yamlParser->parse(file_get_contents($this->file));

        if (empty($classes)) {
            return array();
        }

        if (!is_array($classes)) {
            throw new MappingException(sprintf('The file "%s" must contain a YAML array.', $this->file));
        }

        return $classes;
    }
}
