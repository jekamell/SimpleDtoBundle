<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\Serializer\Mapping;

/**
 * Class AttributeMetadata
 */
class AttributeMetadata extends \Symfony\Component\Serializer\Mapping\AttributeMetadata
{
    const TYPE_INTEGER = 'integer';
    const TYPE_STRING = 'string';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_FLOAT = 'float';
    const TYPE_DATE = 'date';
    const TYPE_DATE_TIME = 'datetime';
    const TYPE_ARRAY = 'array';

    /** @var string */
    public $type;
    /** @var string */
    public $description;
    /** @var bool */
    public $required = false;

    /**
     * @return array
     */
    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_INTEGER,
            self::TYPE_STRING,
            self::TYPE_BOOLEAN,
            self::TYPE_FLOAT,
            self::TYPE_DATE,
            self::TYPE_DATE_TIME,
            self::TYPE_ARRAY,
        ];
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @param bool $required
     */
    public function setRequired(bool $required): void
    {
        $this->required = $required;
    }
}
