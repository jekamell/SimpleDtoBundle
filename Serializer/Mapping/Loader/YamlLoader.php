<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\Serializer\Mapping\Loader;

use Mell\Bundle\SimpleDtoBundle\Model\Relation;
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
    public function loadClassMetadata(ClassMetadataInterface $classMetadata): bool
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
                            throw new MappingException(
                                sprintf(
                                    'The "groups" key must be an array of strings in "%s" for the attribute "%s" of the class "%s".',
                                    $this->file,
                                    $attribute,
                                    $classMetadata->getName()
                                )
                            );
                        }

                        foreach ($data['groups'] as $group) {
                            if (!is_string($group)) {
                                throw new MappingException(
                                    sprintf(
                                        'Group names must be strings in "%s" for the attribute "%s" of the class "%s".',
                                        $this->file,
                                        $attribute,
                                        $classMetadata->getName()
                                    )
                                );
                            }
                            $attributeMetadata->addGroup($group);
                        }
                    }
                    if (isset($data['max_depth'])) {
                        if (!is_int($data['max_depth'])) {
                            throw new MappingException(
                                sprintf(
                                    'The "max_depth" value must be an integer in "%s" for the attribute "%s" of the class "%s".',
                                    $this->file,
                                    $attribute,
                                    $classMetadata->getName()
                                )
                            );
                        }
                        $attributeMetadata->setMaxDepth($data['max_depth']);
                    }
                    if (isset($data['type'])) {
                        if (!in_array($data['type'], AttributeMetadata::getAvailableTypes())) {
                            throw new MappingException(
                                sprintf(
                                    'Type must be one of '
                                    .implode(', ', AttributeMetadata::getAvailableTypes())
                                    .' in "%s" for the attribute "%s" of the class "%s".',
                                    $this->file,
                                    $attribute,
                                    $classMetadata->getName()
                                )
                            );
                        }
                        $attributeMetadata->setType($data['type']);
                    }
                    if (isset($data['description'])) {
                        $attributeMetadata->setDescription($data['description']);
                    }
                    if (isset($data['required'])) {
                        if (!is_bool($data['required'])) {
                            throw new MappingException(
                                sprintf(
                                    'The "required" value must be boolean in "%s" for the attribute "%s" of the class "%s".',
                                    $this->file,
                                    $attribute,
                                    $classMetadata->getName()
                                )
                            );
                        }
                        $attributeMetadata->setRequired($data['required']);
                    }
                }
            }

            if (isset($yaml['expands']) && is_array($yaml['expands'])) {
                foreach ($yaml['expands'] as $expand) {
                    if (!is_string($expand)) {
                        throw new MappingException(
                            sprintf(
                                'The "expand" value must be string in "%s" for class "%s".',
                                $this->file,
                                $classMetadata->getName()
                            )
                        );
                    }
                }

                $classMetadata->setExpands($yaml['expands']);
            }

            $links = [];
            if (isset($yaml['links']) && is_array($yaml['links'])) {
                foreach ($yaml['links'] as $name => $link) {
                    if (isset($link['route']) && !is_string($link['route'])) {
                        throw new MappingException(
                            sprintf(
                                'The "route" value must be string in "%s" for the link "%s" of the class "%s".',
                                $this->file,
                                $name,
                                $classMetadata->getName()
                            )
                        );
                    }
                    if (isset($link['description']) && !is_string($link['description'])) {
                        throw new MappingException(
                            sprintf(
                                'The "description" value must be string in "%s" for the link "%s" of the class "%s".',
                                $this->file,
                                $yaml['links'],
                                $classMetadata->getName()
                            )
                        );
                    }
                    if (isset($link['expression']) && !is_string($link['expression'])) {
                        throw new MappingException(
                            sprintf(
                                'The "expression" value must be string in "%s" for the link "%s" of the class "%s".',
                                $this->file,
                                $yaml['links'],
                                $classMetadata->getName()
                            )
                        );
                    }
                    $links[$name] = [
                        'description' => $link['description'] ?? $name,
                        'expression' => $link['expression'] ?? null,
                    ];
                    if (isset($link['route'])) {
                        $links[$name]['route'] = $link['route'];
                    }
                }
            }
            $classMetadata->setLinks($links);
            $relations = [];
            if (isset($yaml['relations']) && is_array($yaml['relations'])) {
                foreach ($yaml['relations'] as $name => $relationConfig) {
                    // Required config params
                    if (!isset($relationConfig['targetEntity'])
                        || !isset($relationConfig['targetEntity']['class'])
                        || !is_string($relationConfig['targetEntity']['class'])) {
                        throw new MappingException(
                            sprintf(
                                'The "targetEntity.class" value must be string in "%s" for the relation "%s" of the class "%s".',
                                $this->file,
                                $name,
                                $classMetadata->getName()
                            )
                        );
                    }
                    if (!isset($relationConfig['attribute']) || !is_string($relationConfig['attribute'])) {
                        throw new MappingException(
                            sprintf(
                                'The "attribute" value must be string in "%s" for the relation "%s" of the class "%s".',
                                $this->file,
                                $name,
                                $classMetadata->getName()
                            )
                        );
                    }
                    // Optional config params
                    if (isset($relationConfig['groups']) && !is_array($relationConfig['groups'])) {
                        throw new MappingException(
                            sprintf(
                                'The "grops" value must be array in "%s" for the relation "%s" of the class "%s".',
                                $this->file,
                                $name,
                                $classMetadata->getName()
                            )
                        );
                    }
                    if (isset($relationConfig['targetEntity'])
                        && isset($relationConfig['targetEntity']['attribute'])
                        && !is_string($relationConfig['targetEntity']['attribute'])) {
                        throw new MappingException(
                            sprintf(
                                'The "targetEntity.attribute" value must be string in "%s" for the relation "%s" of the class "%s".',
                                $this->file,
                                $name,
                                $classMetadata->getName()
                            )
                        );
                    }
                    if (isset($relationConfig['repositoryMethod']) && !is_string($relationConfig['repositoryMethod'])) {
                        throw new MappingException(
                            sprintf(
                                'The "repositoryMethod" value must be string in "%s" for the relation "%s" of the class "%s".',
                                $this->file,
                                $name,
                                $classMetadata->getName()
                            )
                        );
                    }
                    if (isset($relationConfig['setter']) && !is_string($relationConfig['setter'])) {
                        throw new MappingException(
                            sprintf(
                                'The "setter" value must be string in "%s" for the relation "%s" of the class "%s".',
                                $this->file,
                                $name,
                                $classMetadata->getName()
                            )
                        );
                    }
                    $relations[] = new Relation(
                        $name,
                        $relationConfig['targetEntity']['class'],
                        $relationConfig['attribute'],
                        $relationConfig['groups'] ?? [],
                        $relationConfig['targetEntity']['attribute'] ?? null,
                        $relationConfig['repositoryMethod'] ?? null,
                        $relationConfig['setter'] ?? null
                    );
                }
            }
            $classMetadata->setRelations($relations);

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
