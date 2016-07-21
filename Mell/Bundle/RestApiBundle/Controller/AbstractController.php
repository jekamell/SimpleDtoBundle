<?php

namespace Mell\Bundle\RestApiBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Mell\Bundle\RestApiBundle\Model\Dto;
use Mell\Bundle\RestApiBundle\Model\DtoInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

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
     * @param Request $request
     * @param $entity
     * @return Response
     */
    protected function createResource(Request $request, $entity)
    {
        if (!$data = json_decode($request->getContent(), true)) {
            throw new BadRequestHttpException('Missing json data');
        }

        $entity = $this->getDtoManager()->createEntityFromDto($entity, new Dto($data), $this->getDtoType());

        $errors = $this->get('validator')->validate($entity);
        if ($errors->count()) {
            return $this->serializeResponse($errors);
        }

        // TODO: throw events
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        return $this->serializeResponse(
            $this->getDtoManager()->createDto($entity, $this->getDtoType(), $this->getAllowedExpands())
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

        if ($data instanceof ConstraintViolationListInterface) {
            // TODO: handle validation error
            return new Response('', Response::HTTP_UNPROCESSABLE_ENTITY, ['Content-Type' => 'application/json']);
        }

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
