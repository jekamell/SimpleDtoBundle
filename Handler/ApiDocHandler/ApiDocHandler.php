<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\Handler\ApiDocHandler;

use Mell\Bundle\SimpleDtoBundle\Serializer\Mapping\Factory\ClassMetadataFactory;
use Mell\Bundle\SimpleDtoBundle\Services\RequestManager\RequestManagerConfigurator;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Nelmio\ApiDocBundle\Extractor\HandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Validator\Mapping\Factory\MetadataFactoryInterface;

/**
 * Class ApiDocHandler
 */
class ApiDocHandler implements HandlerInterface
{
    const COLOR_TAG_EXPANDS = '#0f6ab4';

    /** @var RequestManagerConfigurator */
    protected $requestManagerConfigurator;
    /** @var MetadataFactoryInterface */
    protected $metadataFactory;
    /** @var bool */
    protected $hateoasEnabled = false;

    /**
     * ApiDocHandler constructor.
     * @param RequestManagerConfigurator $requestManagerConfigurator
     * @param ClassMetadataFactory $metadataFactory
     * @param bool $hateoasEnabled
     */
    public function __construct(
        RequestManagerConfigurator $requestManagerConfigurator,
        ClassMetadataFactory $metadataFactory,
        bool $hateoasEnabled
    ) {
        $this->requestManagerConfigurator = $requestManagerConfigurator;
        $this->metadataFactory = $metadataFactory;
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
    public function handle(ApiDoc $annotation, array $annotations, Route $route, \ReflectionMethod $method): void
    {
        $this->processParams($annotation, $route);
        $this->processExpands($method, $annotation, $route);
        $this->processShowLinks($annotation, $route);
        $this->processApiFilters($annotation, $route);
    }

    /**
     * @param ApiDoc $annotation
     * @param Route $route
     */
    protected function processParams(ApiDoc $annotation, Route $route): void
    {
        if (in_array(Request::METHOD_DELETE, $route->getMethods())) {
            return;
        }
        if (in_array(Request::METHOD_GET, $route->getMethods())) {
            $annotation->addParameter($this->requestManagerConfigurator->getFieldsParam(), $this->getFieldsParams());
            $annotation->addParameter($this->requestManagerConfigurator->getExpandsParam(), $this->getExpandsParams());
        }
        $output = $annotation->getOutput();
        if (!empty($output['collection'])) {
            $annotation->addParameter($this->requestManagerConfigurator->getLimitParam(), $this->getLimitParams());
            $annotation->addParameter($this->requestManagerConfigurator->getOffsetParam(), $this->getOffsetParams());
            $annotation->addParameter($this->requestManagerConfigurator->getSortParam(), $this->getSortParams());
            $annotation->addParameter($this->requestManagerConfigurator->getCountParam(), $this->getCountParams());
        }
    }

    /**
     * @param \ReflectionMethod $method
     * @param ApiDoc $annotation
     * @param Route $route
     */
    protected function processExpands(\ReflectionMethod $method, ApiDoc $annotation, Route $route): void
    {
        if (!in_array(Request::METHOD_GET, $route->getMethods())) {
            return;
        }

        if (!($class = $annotation->getOutput()['class'] ?? null)) {
            return;
        }

        $metadata = $this->metadataFactory->getMetadataFor($class);
        foreach ($metadata->getExpands() as $expand) {
            $annotation->addTag($expand, self::COLOR_TAG_EXPANDS);
        }
    }

    /**
     * @param ApiDoc $annotation
     * @param Route $route
     */
    protected function processShowLinks(ApiDoc $annotation, Route $route): void
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
    protected function processApiFilters(ApiDoc $annotation, Route $route): void
    {
        $filters = $route->getDefault('filters');
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
    private function getFieldsParams(): array
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
    private function getExpandsParams(): array
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
    private function getLinksParams(): array
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
    private function getLimitParams(): array
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
    private function getOffsetParams(): array
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
    private function getSortParams(): array
    {
        return [
            'dataType' => 'string',
            'required' => false,
            'description' => 'Collection sorting',
        ];
    }

    /**
     * @return array
     */
    private function getCountParams(): array
    {
        return [
            'dataType' => 'string',
            'required' => false,
            'description' => 'Require full collection size',
        ];
    }

    /**
     * @param array $filters
     * @return array
     */
    private function getFiltersParams(array $filters = []): array
    {
        return [
            'dataType' => 'string',
            'required' => false,
            'description' => sprintf('Api filters. Available: "%s"', implode(',', $filters))
        ];
    }
}
