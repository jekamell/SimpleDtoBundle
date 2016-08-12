<?php

namespace Mell\Bundle\SimpleDtoBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class ApiEvent extends Event
{
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_READ = 'read';
    const ACTION_DELETE = 'delete';
    const ACTION_LIST = 'list';

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
