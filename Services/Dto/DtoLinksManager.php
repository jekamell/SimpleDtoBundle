<?php

namespace Mell\Bundle\SimpleDtoBundle\Services\Dto;

use Mell\Bundle\SimpleDtoBundle\Model\DtoInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Router;

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
    /** @var array */
    protected $expressionVars = [];
    /** @var RouteCollection */
    protected $routeCollection;

    /**
     * DtoLinksManager constructor.
     * @param ExpressionLanguage $expressionLanguage
     * @param Router $router
     */
    public function __construct(ExpressionLanguage $expressionLanguage, Router $router)
    {
        $this->expressionLanguage = $expressionLanguage;
        $this->router = $router;
    }

    /**
     * @param DtoInterface $dto
     * @param array $config Dto configuration
     */
    public function processLinks(DtoInterface $dto, array $config)
    {

        if (!isset($config[$dto->getType()]['links'])) {
            return;
        }
        $this->expressionVars['dto'] = $dto;

        $linksConfig = $config[$dto->getType()]['links'];
        $data = [];
        foreach ($linksConfig as $link => $params) {
            if ($this->evaluate($params)) {
                continue;
            }
            $route = $this->getRoute($params['route']);
            $data['_links'][$link] = $this->generateLinkData($dto->getOriginalData(), $params, $route, $link);
        }

        $dto->append($data);
    }

    /**
     * @return array
     */
    public function getExpressionVars()
    {
        return $this->expressionVars;
    }

    /**
     * @param array $expressionVars
     */
    public function setExpressionVars(array $expressionVars)
    {
        $this->expressionVars = $expressionVars;
    }

    /**
     * @param $params
     * @return bool
     */
    private function evaluate($params)
    {
        return isset($params['expression']) && !$this->expressionLanguage->evaluate(
            $params['expression'],
            $this->getExpressionVars()
        );
    }

    /**
     * @param $name
     * @return null|\Symfony\Component\Routing\Route
     */
    private function getRoute($name)
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
    private function generateLinkData($entity, array $params, Route $route, $link)
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
    private function getRouteParams(Route $route, $entity)
    {
        $data = [];
        foreach ($route->getRequirements() as $param => $value) {
            $data[$param] = call_user_func([$entity, 'get' . ucfirst($param)]);
        }

        return $data;
    }
}
