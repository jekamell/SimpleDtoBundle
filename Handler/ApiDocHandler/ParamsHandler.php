<?php

namespace Mell\Bundle\SimpleDtoBundle\Handler\ApiDocHandler;

use Mell\Bundle\SimpleDtoBundle\Services\RequestManager\RequestManagerConfigurator;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Nelmio\ApiDocBundle\Extractor\HandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

class ParamsHandler implements HandlerInterface
{
    /** @var RequestManagerConfigurator */
    protected $requestManagerConfigurator;

    /**
     * ParamsHandler constructor.
     * @param RequestManagerConfigurator $requestManagerConfigurator
     */
    public function __construct(RequestManagerConfigurator $requestManagerConfigurator)
    {
        $this->requestManagerConfigurator = $requestManagerConfigurator;
    }

    /**
     * Parse route parameters in order to populate ApiDoc.
     *
     * @param ApiDoc $annotation
     * @param array $annotations
     * @param Route $route
     * @param \ReflectionMethod $method
     */
    public function handle(ApiDoc $annotation, array $annotations, Route $route, \ReflectionMethod $method)
    {
        if (!in_array(Request::METHOD_GET, $route->getMethods())) {
            return;
        }

        $annotation->addParameter($this->requestManagerConfigurator->getFieldsParam(), $this->getFieldsParams());
        $annotation->addParameter($this->requestManagerConfigurator->getExpandsParam(), $this->getExpandsParams());
    }

    /**
     * @return array
     */
    private function getFieldsParams()
    {
        return [
            'dataType' => 'string',
            'required' => false,
            'description' => 'Required fields',
        ];
    }

    /**
     * @return array
     */
    private function getExpandsParams()
    {
        return [
            'dataType' => 'string',
            'required' => false,
            'description' => 'Required expands',
        ];
    }
}
