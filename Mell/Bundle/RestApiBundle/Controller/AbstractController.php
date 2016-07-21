<?php

namespace Mell\Bundle\RestApiBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Mell\Bundle\RestApiBundle\Model\Dto;
use Mell\Bundle\RestApiBundle\Model\DtoInterface;
use Mell\Bundle\RestApiBundle\Event\ApiEvent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

abstract class AbstractController extends Controller
{
    const FORMAT_JSON = 'json';

    const DTO_GROUP_CREATE = 'create';
    const DTO_GROUP_READ = 'read';
    const DTO_GROUP_UPDATE = 'update';
    const DTO_GROUP_DELETE = 'delete';
    const DTO_GROUP_LIST = 'list';

    /** @return string */
    protected abstract function getDtoType();

    /** @return string */
    protected abstract function getEntityAlias();

    /** @return array */
    protected abstract function getAllowedExpands();

    /**
     * @param Request $request
     * @param $entity
     * @param string|null $dtoGroup
     * @return Response
     */
    protected function createResource(Request $request, $entity, $dtoGroup = null)
    {
        if (!$data = json_decode($request->getContent(), true)) {
            throw new BadRequestHttpException('Missing json data');
        }

        $entity = $this->getDtoManager()->createEntityFromDto($entity, new Dto($data), $this->getDtoType(), $dtoGroup);
        $event = new ApiEvent($entity, 'create');

        $this->getEventDispatcher()->dispatch('mell_rest_api.pre_validate', $event);
        $errors = $this->get('validator')->validate($entity);
        if ($errors->count()) {
            return $this->serializeResponse($errors);
        }

        $this->getEventDispatcher()->dispatch('mell_rest_api.pre_persist', $event);
        $this->getEntityManager()->persist($entity);

        $this->getEventDispatcher()->dispatch('mell_rest_api.pre_flush', $event);
        $this->getEntityManager()->flush();

        return $this->serializeResponse(
            $this->getDtoManager()->createDto($entity, $this->getDtoType(), $this->getAllowedExpands())
        );
    }

    /**
     * @param Request $request
     * @param $entity
     * @param string|null $dtoGroup
     * @return Response
     */
    protected function updateResource(Request $request, $entity, $dtoGroup = null)
    {
        if (!$data = json_decode($request->getContent(), true)) {
            throw new BadRequestHttpException('Missing json data');
        }

        $entity = $this->getDtoManager()->createEntityFromDto($entity, new Dto($data), $this->getDtoType(), $dtoGroup);
        $event = new ApiEvent($entity, 'update');

        $this->getEventDispatcher()->dispatch('mell_rest_api.pre_validate', $event);
        $errors = $this->get('validator')->validate($entity);
        if ($errors->count()) {
            return $this->serializeResponse($errors);
        }

        $this->getEventDispatcher()->dispatch('mell_rest_api.pre_flush', $event);
        $this->getEntityManager()->flush();

        return $this->serializeResponse(
            $this->getDtoManager()->createDto($entity, $this->getDtoType(), $this->getAllowedExpands())
        );
    }

    /**
     * @param $entity
     * @param string|null $dtoGroup
     * @return Response
     */
    protected function readResource($entity, $dtoGroup = null)
    {
        return $this->serializeResponse(
            $this->getDtoManager()->createDto($entity, $this->getDtoType(), $dtoGroup, $this->getAllowedExpands())
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
                self::DTO_GROUP_LIST,
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

    /**
     * @return object|\Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher|\Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher
     */
    protected function getEventDispatcher()
    {
        return $this->get('event_dispatcher');
    }
}
