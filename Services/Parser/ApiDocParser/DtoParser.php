<?php

namespace Mell\Bundle\SimpleDtoBundle\Services\Parser\ApiDocParser;
use Mell\Bundle\SimpleDtoBundle\Services\Dto\DtoManagerInterface;
use Nelmio\ApiDocBundle\DataTypes;
use Nelmio\ApiDocBundle\Parser\ParserInterface;

/**
 * Support metadata parsing from dto configuration
 * Class DtoParser
 * @package Mell\Bundle\SimpleDtoBundle\Services\Parser\ApiDocParser
 */
class DtoParser implements ParserInterface
{
    /** @var array */
    protected $typeMap = [
        'integer' => DataTypes::INTEGER,
        'string' => DataTypes::STRING,
        'boolean' => DataTypes::BOOLEAN,
        'float' => DataTypes::FLOAT,
        'array' => DataTypes::COLLECTION,
        'date' => DataTypes::DATE,
        'datetime' => DataTypes::DATETIME,
    ];
    /** @var DtoManagerInterface */
    protected $dtoManager;

    /**
     * DtoParser constructor.
     * @param DtoManagerInterface $dtoManager
     */
    public function __construct(DtoManagerInterface $dtoManager)
    {
        $this->dtoManager = $dtoManager;
    }

    /**
     * Return true/false whether this class supports parsing the given class.
     *
     * @param  array $item containing the following fields: class, groups. Of which groups is optional
     * @return boolean
     */
    public function supports(array $item)
    {
        return $this->dtoManager->hasConfig($item['class']);
    }

    /**
     * @param  array $item The string type of input to parse.
     * @return array
     */
    public function parse(array $item)
    {
        $data = [];
        foreach ($this->dtoManager->getConfig($item['class'])['fields'] as $field => $config) {
            $data[$field] = $this->getPropertyPayload($config, $field);
        }

        return $data;
    }

    /**
     * @param array $config
     * @param string $field
     * @return array
     */
    public function getPropertyPayload(array $config, $field)
    {
        return [
            'dataType' => $this->typeMap[$config['type']],
            'required' => !empty($config['required']),
            'description' => !empty($config['description']) ? $config['description'] : $field,
            'readonly' => !empty($config['readonly'])
        ];
    }
}
