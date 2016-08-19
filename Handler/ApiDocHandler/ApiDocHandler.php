<?php

namespace Mell\Bundle\SimpleDtoBundle\Handler\ApiDocHandler;

use Mell\Bundle\SimpleDtoBundle\Services\RequestManager\RequestManagerConfigurator;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Nelmio\ApiDocBundle\Extractor\HandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

/**
 * Class ApiDocHandler
 * @package Mell\Bundle\SimpleDtoBundle\Handler\ApiDocHandler
 */
class ApiDocHandler implements HandlerInterface
{
    const METHOD_EXPANDS = 'getAllowedExpands';
    const COLOR_TAG_EXPANDS = '#0f6ab4';
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
        $this->processParams($annotation);
        $this->processExpands($method, $annotation);
    }

    /**
     * @param ApiDoc $annotation
     */
    protected function processParams(ApiDoc $annotation)
    {
        $annotation->addParameter($this->requestManagerConfigurator->getFieldsParam(), $this->getFieldsParams());
        $annotation->addParameter($this->requestManagerConfigurator->getExpandsParam(), $this->getExpandsParams());
    }

    /**
     * @param \ReflectionMethod $method
     * @param ApiDoc $annotation
     */
    protected function processExpands(\ReflectionMethod $method, ApiDoc $annotation)
    {
        $class = new $method->class;
        if (method_exists($class, self::METHOD_EXPANDS)) {
            $expands = call_user_func([$class, self::METHOD_EXPANDS]);
            foreach ($expands as $expand) {
                $annotation->addTag($expand, self::COLOR_TAG_EXPANDS);
            }
        }
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
