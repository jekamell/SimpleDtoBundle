<?php

namespace Mell\Bundle\SimpleDtoBundle\Services\Dto\Builder;

use Mell\Bundle\SimpleDtoBundle\Helpers\DtoHelper;
use Mell\Bundle\SimpleDtoBundle\Model\DtoInterface;
use Mell\Bundle\SimpleDtoBundle\Services\RequestManager\RequestManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\ExpressionLanguage;

/**
 * Append HATEOAS information to DTO
 *
 * Class LinksBuilder
 * @package Mell\Bundle\SimpleDtoBundle\Services\Dto\Builder
 */
class LinksBuilder implements DtoBuilderInterface
{
    /** @var RequestManager */
    protected $requestManager;
    /** @var Router */
    protected $router;
    /** @var ExpressionLanguage */
    protected $expressionLanguage;
    /** @var DtoHelper */
    protected $dtoHelper;
    /** @var TokenStorage */
    protected $tokenStorage;
    /** @var  AuthenticationTrustResolver */
    protected $trustResolver;
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;
    /** @var Request */
    protected $request;
    /** @var bool */
    protected $enabled;
    /** @var RouteCollection */
    private $routeCollection;

    /**
     * LinksBuilder constructor.
     * @param RequestManager $requestManager
     * @param Router $router
     * @param ExpressionLanguage $expressionLanguage
     * @param DtoHelper $dtoHelper
     * @param TokenStorage $tokenStorage
     * @param AuthenticationTrustResolver $trustResolver
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param RequestStack $requestStack
     * @param $enabled
     */
    public function __construct(
        RequestManager $requestManager,
        Router $router,
        ExpressionLanguage $expressionLanguage,
        DtoHelper $dtoHelper,
        TokenStorage $tokenStorage,
        AuthenticationTrustResolver $trustResolver,
        AuthorizationCheckerInterface $authorizationChecker,
        RequestStack $requestStack,
        $enabled
    ) {
        $this->requestManager = $requestManager;
        $this->router = $router;
        $this->expressionLanguage = $expressionLanguage;
        $this->dtoHelper = $dtoHelper;
        $this->tokenStorage = $tokenStorage;
        $this->trustResolver = $trustResolver;
        $this->authorizationChecker = $authorizationChecker;
        $this->request = $requestStack->getCurrentRequest();
        $this->enabled = $enabled;
    }

    /**
     * @param object $entity Data source for dto
     * @param DtoInterface $dto
     * @param array $config Whole dto configuration
     * @param string $type
     * @param string|null $group Dto group to process
     * @return null
     */
    public function build($entity, DtoInterface $dto, array $config, $type, $group = null)
    {

        if (!$this->enabled || !$this->requestManager->isLinksRequired() || empty($config[$type]['links'])) {
            return;
        }

        $linksConfig = $config[$type]['links'];
        $data = [];
        foreach ($linksConfig as $link => $params) {
            if ($this->evaluate($dto, $params)) {
                continue;
            }
            $route = $this->getRoute($params['route']);
            $data['_links'][$link] = $this->generateLinkData($entity, $params, $route, $link);
        }

        $dto->append($data);
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
     * @param Route $route
     * @param $entity
     * @return array
     */
    private function getRouteParams(Route $route, $entity)
    {
        $data = [];
        foreach ($route->getRequirements() as $param => $value) {
            $data[$param] = call_user_func([$entity, $this->dtoHelper->getFieldGetter($param)]);
        }

        return $data;
    }

    /**
     * @param DtoInterface $dto
     * @param $params
     * @return bool
     */
    private function evaluate(DtoInterface $dto, $params)
    {
        return isset($params['expression']) && !$this->expressionLanguage->evaluate(
            $params['expression'],
            $this->getExpressionVars($dto)
        );
    }

    /**
     * @param DtoInterface $dto
     * @return array
     */
    private function getExpressionVars(DtoInterface $dto)
    {
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;
        return [
            'user' => $user,
            'roles' => $user ? $user->getRoles() : [],
            'request' => $this->request,
            'trust_resolver' => $this->trustResolver,
            'auth_checker' => $this->authorizationChecker,
            'dto' => $dto,
            'entity' => is_object($dto->getOriginalData()) ? $dto->getOriginalData() : null
        ];
    }

    /**
     * @param object $entity
     * @param array $params
     * @param Route $route
     * @param string $link
     * @return array
     */
    private function generateLinkData($entity, $params, $route, $link)
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
}
