<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\Services\Dto;

use Mell\Bundle\SimpleDtoBundle\Model\DtoInterface;
use Mell\Bundle\SimpleDtoBundle\Model\DtoSerializableInterface;
use Mell\Bundle\SimpleDtoBundle\Serializer\Mapping\ClassMetadataDecorator;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Router;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;

/**
 * Class DtoLinksManager
 * @package Mell\Bundle\SimpleDtoBundle\Services\Dto
 */
class DtoLinksManager
{
    /** @var ExpressionLanguage */
    protected $expressionLanguage;
    /** @var Router */
    protected $router;
    /** @var ClassMetadataFactory */
    protected $metadataFactory;
    /** @var array */
    protected $expressionVars = [];
    /** @var RouteCollection */
    protected $routeCollection;

    /**
     * DtoLinksManager constructor.
     * @param ExpressionLanguage $expressionLanguage
     * @param Router $router
     * @param ClassMetadataFactory $metadataFactory
     */
    public function __construct(
        ExpressionLanguage $expressionLanguage,
        Router $router,
        ClassMetadataFactory $metadataFactory
    ) {
        $this->expressionLanguage = $expressionLanguage;
        $this->router = $router;
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * @param DtoInterface $dto
     */
    public function processLinks(DtoInterface $dto): void
    {
        $class = get_class($dto->getOriginalData());
        /** @var ClassMetadataDecorator $classMetadata */
        if (!$classMetadata = $this->metadataFactory->getMetadataFor($class)) {
            return;
        }
        if (!$links = $classMetadata->getLinks()) {
            return;
        }

        $this->expressionVars['dto'] = $dto;
        $data = [];
        foreach ($links as $link => $params) {
            if (!empty($params['expression']) && !$this->evaluate($params['expression'])) {
                continue;
            }
            $route = $this->getRoute($params['route']);
            $data['_links'][$link] = $this->generateLinkData($dto->getOriginalData(), $params, $route, $link);
        }

        $dto->setRawData(array_merge($dto->getRawData(), $data));
    }

    /**
     * @return array
     */
    public function getExpressionVars(): array
    {
        return $this->expressionVars;
    }

    /**
     * @param array $expressionVars
     */
    public function setExpressionVars(array $expressionVars): void
    {
        $this->expressionVars = $expressionVars;
    }

    /**
     * @param string $expression
     * @return bool
     */
    private function evaluate(string $expression): bool
    {
        return (bool) $this->expressionLanguage->evaluate($expression, $this->getExpressionVars());
    }

    /**
     * @param $name
     * @return null|Route
     */
    private function getRoute($name): ?Route
    {
        if (!$this->routeCollection) {
            $this->routeCollection = $this->router->getRouteCollection();
        }

        return $this->routeCollection->get($name);
    }

    /**
     * @param object $entity
     * @param array $params
     * @param Route $route
     * @param string $link
     * @return array
     */
    private function generateLinkData($entity, array $params, Route $route, $link): array
    {
        return [
            'url' => $this->router->generate(
                $params['route'],
                $this->getRouteParams($route, $entity),
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'methods' => $route->getMethods(),
            'description' => isset($params['description']) ? $params['description'] : ucfirst($link),
        ];
    }

    /**
     * @param Route $route
     * @param $entity
     * @return array
     */
    private function getRouteParams(Route $route, DtoSerializableInterface $entity): array
    {
        $data = [];
        foreach ($route->getRequirements() as $param => $value) {
            $data[$param] = call_user_func([$entity, 'get' . ucfirst($param)]);
        }

        return $data;
    }
}
