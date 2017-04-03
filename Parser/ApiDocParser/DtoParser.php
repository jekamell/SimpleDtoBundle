<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\Parser\ApiDocParser;

use Mell\Bundle\SimpleDtoBundle\Serializer\Mapping\AttributeMetadata;
use Nelmio\ApiDocBundle\DataTypes;
use Nelmio\ApiDocBundle\Parser\ParserInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;

/**
 * Support metadata parsing from dto configuration
 * Class DtoParser
 */
class DtoParser implements ParserInterface
{
    /** @var ClassMetadataFactoryInterface */
    protected $metadataFactory;
    /** @var array */
    protected $attributeDataTypeMap = [
        AttributeMetadata::TYPE_INTEGER => DataTypes::INTEGER,
        AttributeMetadata::TYPE_STRING => DataTypes::STRING,
        AttributeMetadata::TYPE_BOOLEAN => DataTypes::BOOLEAN,
        AttributeMetadata::TYPE_FLOAT => DataTypes::FLOAT,
        AttributeMetadata::TYPE_DATE => DataTypes::DATE,
        AttributeMetadata::TYPE_DATE_TIME => DataTypes::DATETIME,
    ];

    /**
     * DtoParser constructor.
     * @param ClassMetadataFactoryInterface $metadataFactory
     */
    public function __construct(ClassMetadataFactoryInterface $metadataFactory)
    {
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * @param  array $item containing the following fields: class, groups. Of which groups is optional
     * @return boolean
     */
    public function supports(array $item): bool
    {
        return class_exists($item['class']);
    }

    /**
     * @param  array $item The string type of input to parse.
     * @return array
     */
    public function parse(array $item): array
    {
        $data = [];
        /** @var AttributeMetadata $attribute */
        foreach ($this->metadataFactory->getMetadataFor($item['class'])->getAttributesMetadata() as $attribute) {
            if (empty(array_intersect($attribute->getGroups(), $item['groups']))) {
                continue;
            }
            $data[$attribute->getName()] = [
                'dataType' => $this->attributeDataTypeMap[$attribute->getType()] ?? DataTypes::STRING,
                'required' => $attribute->isRequired(),
                'description' => $attribute->getDescription() ?: ucfirst($attribute->getName()),
            ];
        }

        return $data;
    }
}
