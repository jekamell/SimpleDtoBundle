<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\EventListener;

use Doctrine\ORM\QueryBuilder;
use Mell\Bundle\SimpleDtoBundle\Event\ApiEvent;
use Mell\Bundle\SimpleDtoBundle\Model\ApiFilter;
use Mell\Bundle\SimpleDtoBundle\Model\ApiFilterCollectionInterface;
use Mell\Bundle\SimpleDtoBundle\Services\ApiFiltersManager\ApiFiltersManager;
use Mell\Bundle\SimpleDtoBundle\Services\RequestManager\RequestManager;

/**
 * Class ListQueryBuilderListener
 */
class ListQueryBuilderListener
{
    const LIST_LIMIT_DEFAULT = 100;
    const LIST_LIMIT_MAX = 1000;

    /** @var ApiFiltersManager */
    protected $apiFiltersManager;
    /** @var RequestManager */
    protected $requestManager;

    /**
     * ListQueryBuilderListener constructor.
     * @param ApiFiltersManager $apiFiltersManager
     * @param RequestManager $requestManager
     */
    public function __construct(ApiFiltersManager $apiFiltersManager, RequestManager $requestManager)
    {
        $this->apiFiltersManager = $apiFiltersManager;
        $this->requestManager = $requestManager;
    }

    /**
     * @param ApiEvent $apiEvent
     */
    public function onPreCollectionLoad(ApiEvent $apiEvent): void
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $apiEvent->getData();
        if ($apiEvent->getAction() !== ApiEvent::ACTION_LIST || !$queryBuilder instanceof QueryBuilder) {
            return;
        }

        $this->processFilters($queryBuilder, $apiEvent->getContext()['filters']);
        if (empty($apiEvent->getContext()['skipLimit'])) {
            $this->processLimit($queryBuilder);
        }
        $this->processOffset($queryBuilder);
        $this->processSort($queryBuilder);
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param ApiFilterCollectionInterface|null $apiFilters
     */
    protected function processFilters(QueryBuilder $queryBuilder, ?ApiFilterCollectionInterface $apiFilters): void
    {
        if (!$apiFilters) {
            return;
        }

        /** @var ApiFilter $filter */
        foreach ($apiFilters as $i => $filter) {
            $queryBuilder->andWhere(
                sprintf(
                    current($queryBuilder->getRootAliases()) . '.%s %s %s',
                    $filter->getParam(),
                    $this->apiFiltersManager->getSqlOperationByOperation($filter->getOperation()),
                    $this->apiFiltersManager->isArrayOperation($filter->getOperation())
                        ? '(:' . $filter->getParam() . $i . ')'
                        : ':' . $filter->getParam() . $i
                )
            );
            $queryBuilder->setParameter($filter->getParam() . $i, $filter->getValue());
        }
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    protected function processLimit(QueryBuilder $queryBuilder): void
    {
        $limit = $this->requestManager->getLimit() ?: static::LIST_LIMIT_DEFAULT;
        $queryBuilder->setMaxResults(min($limit, static::LIST_LIMIT_MAX));
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    protected function processOffset(QueryBuilder $queryBuilder): void
    {
        $offset = $this->requestManager->getOffset();
        if (!empty($offset)) {
            $queryBuilder->setFirstResult($offset);
        }
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    protected function processSort(QueryBuilder $queryBuilder): void
    {
        $sort = $this->requestManager->getSort();
        if (!empty($sort)) {
            $rootAliases = $queryBuilder->getRootAliases();
            foreach ($sort as $param => $direction) {
                $queryBuilder->addOrderBy(current($rootAliases) . '.' . $param, $direction);
            }
        }
    }
}
