<?php

namespace Mell\Bundle\SimpleDtoBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class ApiEvent
 * @package Mell\Bundle\SimpleDtoBundle\Event
 */
class ApiEvent extends Event
{
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_READ = 'read';
    const ACTION_DELETE = 'delete';
    const ACTION_LIST = 'list';

    const EVENT_PRE_VALIDATE = 'simple_dto.pre_validate';
    const EVENT_PRE_COLLECTION_LOAD = 'simple_dto.pre_collection_load';
    const EVENT_POST_COLLECTION_LOAD = 'simple_dto.post_collection_load';
    const EVENT_PRE_PERSIST = 'simple_dto.pre_persist';
    const EVENT_PRE_FLUSH = 'simple_dto.pre_flush';
    const EVENT_POST_READ = 'simple_dto.post_read';
    const EVENT_PRE_DTO_ENCODE = 'simple_dto.pre_dto_encode';
    const EVENT_POST_DTO_ENCODE = 'simple_dto.post_dto_encode';
    const EVENT_PRE_DTO_DECODE = 'simple_dto.pre_dto_decode';
    const EVENT_POST_DTO_DECODE = 'simple_dto.post_dto_decode';

    /** @var array */
    protected $data;
    /** @var string */
    protected $action;

    /**
     * ApiEvent constructor.
     * @param $data
     * @param string $action
     */
    public function __construct($data, $action)
    {
        $this->data = $data;
        $this->action = $action;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }
}
