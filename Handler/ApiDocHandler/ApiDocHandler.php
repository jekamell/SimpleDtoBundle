<?php

namespace Mell\Bundle\SimpleDtoBundle\Handler\ApiDocHandler;

use Mell\Bundle\SimpleDtoBundle\Services\RequestManager\RequestManagerConfigurator;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Nelmio\ApiDocBundle\Extractor\HandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
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
    /** @var bool */
    protected $hateoasEnabled;

    /**
     * ParamsHandler constructor.
     * @param RequestManagerConfigurator $requestManagerConfigurator
     * @param $hateoasEnabled
     */
    public function __construct(RequestManagerConfigurator $requestManagerConfigurator, $hateoasEnabled)
    {
        $this->requestManagerConfigurator = $requestManagerConfigurator;
        $this->hateoasEnabled = $hateoasEnabled;
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
        $this->processParams($annotation, $route);
        $this->processExpands($method, $annotation, $route);
        $this->processShowLinks($annotation, $route);
        $this->processApiFilters($annotation, $route);
    }

    /**
     * @param ApiDoc $annotation
     */
    protected function processParams(ApiDoc $annotation, Route $route)
    {
        if (in_array(Request::METHOD_DELETE, $route->getMethods())) {
            return;
        }
        $annotation->addParameter($this->requestManagerConfigurator->getFieldsParam(), $this->getFieldsParams());
        $annotation->addParameter($this->requestManagerConfigurator->getExpandsParam(), $this->getExpandsParams());
        $output = $annotation->getOutput();
        if (!empty($output['collection'])) {
            $annotation->addParameter($this->requestManagerConfigurator->getLimitParam(), $this->getLimitParams());
            $annotation->addParameter($this->requestManagerConfigurator->getOffsetParam(), $this->getOffsetParams());
            $annotation->addParameter($this->requestManagerConfigurator->getSortParam(), $this->getSortParams());
        }
    }

    /**
     * @param \ReflectionMethod $method
     * @param ApiDoc $annotation
     * @param Route $route
     */
    protected function processExpands(\ReflectionMethod $method, ApiDoc $annotation, Route $route)
    {
        if (in_array(Request::METHOD_DELETE, $route->getMethods())) {
            return;
        }

        $class = new $method->class;
        if (method_exists($class, self::METHOD_EXPANDS)) {
            $expands = call_user_func([$class, self::METHOD_EXPANDS]);
            foreach ($expands as $expand) {
                $annotation->addTag($expand, self::COLOR_TAG_EXPANDS);
            }
        }
    }

    /**
     * @param ApiDoc $annotation
     * @param Route $route
     */
    protected function processShowLinks(ApiDoc $annotation, Route $route)
    {
        if (in_array(Request::METHOD_DELETE, $route->getMethods())) {
            return;
        }
        if ($this->hateoasEnabled) {
            $annotation->addParameter($this->requestManagerConfigurator->getLinksParam(), $this->getLinksParams());
        }
    }

    /**
     * @param ApiDoc $annotation
     * @param Route $route
     */
    protected function processApiFilters(ApiDoc $annotation, Route $route)
    {
        $filters = $route->getDefault('_filters');
        if ($filters) {
            $annotation->addParameter(
                $this->requestManagerConfigurator->getApiFilterParam(),
                $this->getFiltersParams($filters)
            );
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

    /**
     * @return array
     */
    private function getLinksParams()
    {
        return [
            'dataType' => 'string',
            'required' => false,
            'description' => 'Require HATEOAS links',
        ];
    }

    /**
     * @return array
     */
    private function getLimitParams()
    {
        return [
            'dataType' => 'string',
            'required' => false,
            'description' => 'Collection limit',
        ];
    }

    /**
     * @return array
     */
    private function getOffsetParams()
    {
        return [
            'dataType' => 'string',
            'required' => false,
            'description' => 'Collection offset',
        ];
    }

    /**
     * @return array
     */
    private function getSortParams()
    {
        return [
            'dataType' => 'string',
            'required' => false,
            'description' => 'Collection sorting',
        ];
    }

    /**
     * @param array $filters
     * @return array
     */
    private function getFiltersParams(array $filters = [])
    {
        return [
            'dataType' => 'string',
            'required' => false,
            'description' => sprintf('Api filters. Use one or few of %s', implode(',', $filters))
        ];
    }
}
