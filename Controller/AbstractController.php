<?php

namespace Mell\Bundle\SimpleDtoBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Mell\Bundle\SimpleDtoBundle\Model\Dto;
use Mell\Bundle\SimpleDtoBundle\Model\DtoInterface;
use Mell\Bundle\SimpleDtoBundle\Event\ApiEvent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

abstract class AbstractController extends Controller
{
    const FORMAT_JSON = 'json';

    /** @return string */
    abstract protected function getDtoType();
    /** @return string */
    abstract protected function getEntityAlias();
    /** @return array */
    abstract protected function getAllowedExpands();

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

        $entity = $this->getDtoManager()->createEntityFromDto(
            $entity,
            new Dto($data),
            $this->getDtoType(),
            $dtoGroup ?: DtoInterface::DTO_GROUP_CREATE
        );

        $event = new ApiEvent($entity, ApiEvent::ACTION_CREATE);
        $this->getEventDispatcher()->dispatch('simple_dto.pre_validate', $event);

        $errors = $this->get('validator')->validate($entity);
        if ($errors->count()) {
            return $this->serializeResponse($errors);
        }

        $this->getEventDispatcher()->dispatch('simple_dto.pre_persist', $event);
        $this->getEntityManager()->persist($entity);

        $this->getEventDispatcher()->dispatch('simple_dto.pre_flush', $event);
        $this->getEntityManager()->flush();

        return $this->serializeResponse(
            $this->getDtoManager()->createDto(
                $entity,
                $this->getDtoType(),
                $dtoGroup ?: DtoInterface::DTO_GROUP_READ,
                $this->get('simple_dto.request_manager')->getFields(),
                array_intersect_key(
                    $this->get('simple_dto.request_manager')->getExpands(),
                    array_flip($this->getAllowedExpands())
                )
            )
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

        $entity = $this->getDtoManager()->createEntityFromDto(
            $entity,
            new Dto($data),
            $this->getDtoType(),
            $dtoGroup ?: DtoInterface::DTO_GROUP_UPDATE
        );

        $event = new ApiEvent($entity, ApiEvent::ACTION_UPDATE);
        $this->getEventDispatcher()->dispatch('simple_dto.pre_validate', $event);

        $errors = $this->get('validator')->validate($entity);
        if ($errors->count()) {
            return $this->serializeResponse($errors);
        }

        $this->getEventDispatcher()->dispatch('simple_dto.pre_flush', $event);
        $this->getEntityManager()->flush();

        return $this->readResource($entity);
    }

    /**
     * @param $entity
     * @param string|null $dtoGroup
     * @return Response
     */
    protected function readResource($entity, $dtoGroup = null)
    {
        return $this->serializeResponse(
            $this->getDtoManager()->createDto(
                $entity,
                $this->getDtoType(),
                $dtoGroup ?: DtoInterface::DTO_GROUP_READ,
                $this->get('simple_dto.request_manager')->getFields(),
                array_intersect_key(
                    $this->get('simple_dto.request_manager')->getExpands(),
                    array_flip($this->getAllowedExpands())
                )
            )
        );
    }

    /**
     * @param $entity
     * @return Response
     */
    protected function deleteResource($entity)
    {
        $this->getEntityManager()->remove($entity);

        $event = new ApiEvent($entity, ApiEvent::ACTION_DELETE);
        $this->getEventDispatcher()->dispatch('simple_dto.pre_flush', $event);

        $this->getEntityManager()->flush();

        return new Response('', Response::HTTP_NO_CONTENT, ['Content-Type' => 'application/json']);
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $dtoGroup
     * @return Response
     */
    protected function listResources(QueryBuilder $queryBuilder, $dtoGroup = null)
    {
        return $this->serializeResponse(
            $this->getDtoManager()->createDtoCollection(
                $queryBuilder->getQuery()->getResult(),
                $this->getDtoType(),
                $dtoGroup ?: DtoInterface::DTO_GROUP_LIST,
                $this->get('simple_dto.request_manager')->getFields(),
                array_intersect_key(
                    $this->get('simple_dto.request_manager')->getExpands(),
                    array_flip($this->getAllowedExpands())
                )
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
            return new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY, ['Content-Type' => 'application/json']);
        }

        return new Response(
            $this->get('serializer')->serialize($data, $format, []),
            $statusCode,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * @return \Mell\Bundle\SimpleDtoBundle\Services\Dto\DtoManager
     */
    protected function getDtoManager()
    {
        return $this->get('simple_dto.dto_manager');
    }

    /**
     * @return object|\Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher|\Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher
     */
    protected function getEventDispatcher()
    {
        return $this->get('event_dispatcher');
    }
}
