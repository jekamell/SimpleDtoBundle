<?php

namespace Mell\Bundle\RestApiBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Mell\Bundle\RestApiBundle\Model\DtoInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractController extends Controller
{
    const FORMAT_JSON = 'json';

    /** @return string */
    protected abstract function getDtoType();

    /** @return string */
    protected abstract function getEntityAlias();

    /** @return array */
    protected abstract function getAllowedExpands();

    /**
     * @param QueryBuilder $queryBuilder
     * @return Response
     */
    protected function listResources(QueryBuilder $queryBuilder)
    {
        return $this->serializeResponse(
            $this->getDtoManager()->createDtoCollection(
                $queryBuilder->getQuery()->getResult(),
                $this->getDtoType(),
                $this->getAllowedExpands()
            )
        );
    }

    /**
     * @param $entity
     * @return Response
     */
    protected function readResource($entity)
    {
        return $this->serializeResponse(
            $this->getDtoManager()->createDto($entity, $this->getDtoType(), $this->getAllowedExpands())
        );
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return $this->get('doctrine.orm.entity_manager');
    }

    /**
     * @param string $alias
     * @return QueryBuilder
     */
    protected function getQueryBuilder($alias = 't')
    {
        return $this->getEntityManager()->getRepository($this->getEntityAlias())->createQueryBuilder($alias);
    }

    /**
     * @param DtoInterface $data
     * @param int $statusCode
     * @param string $format
     * @return Response
     */
    protected function serializeResponse($data, $statusCode = Response::HTTP_OK, $format = self::FORMAT_JSON)
    {
        return new Response(
            $this->get('serializer')->serialize($data, $format, []),
            $statusCode,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * @return \Mell\Bundle\RestApiBundle\Services\Dto\DtoManager|object
     */
    protected function getDtoManager()
    {
        return $this->get('mell_rest_api.dto_manager');
    }
}
