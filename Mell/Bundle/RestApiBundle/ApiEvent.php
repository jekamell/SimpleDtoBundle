<?php

namespace Mell\Mell\Bundle\RestApiBundle;

use Symfony\Component\EventDispatcher\Event;

class ApiEvent extends Event
{
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
