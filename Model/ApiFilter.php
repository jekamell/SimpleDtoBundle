<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\Model;

/**
 * Class ApiFilter
 */
class ApiFilter
{
    const OPERATION_EQUAL = 'eq';
    const OPERATION_NOT_EQUAL = 'neq';
    const OPERATION_MORE_THEN = 'mt';
    const OPERATION_LESS_THEN = 'lt';
    const OPERATION_MORE_OR_EQUAL_THEN = 'meqt';
    const OPERATION_LESS_OR_EQUAL_THEN = 'leqt';
    const OPERATION_IN_ARRAY = 'ia';
    const OPERATION_NOT_IN_ARRAY = 'nia';
    const OPERATION_IS = 'is';
    const OPERATION_IS_NOT = 'isn';

    /** @var string */
    private $param;
    /** @var string */
    private $operation;
    /** @var mixed */
    private $value;

    /**
     * ApiFilter constructor.
     * @param string $param
     * @param string $operation
     * @param mixed $value
     */
    public function __construct($param, $operation, $value)
    {
        $this->param = $param;
        $this->operation = $operation;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getParam(): string
    {
        return $this->param;
    }

    /**
     * @param string $param
     */
    public function setParam(string $param): void
    {
        $this->param = $param;
    }

    /**
     * @return string
     */
    public function getOperation(): string
    {
        return $this->operation;
    }

    /**
     * @param string $operation
     */
    public function setOperation(string $operation): void
    {
        $this->operation = $operation;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }
}
