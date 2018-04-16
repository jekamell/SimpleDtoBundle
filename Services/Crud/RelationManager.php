<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\Services\Crud;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Mell\Bundle\SimpleDtoBundle\Model\DtoSerializableInterface;
use Mell\Bundle\SimpleDtoBundle\Model\Relation;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Class RelationManager
 */
class RelationManager
{
    /** @var PropertyAccessorInterface */
    protected $propertyAccessor;
    /** @var EntityManagerInterface */
    protected $entityManager;

    /**
     * RelationManager constructor.
     * @param PropertyAccessorInterface $propertyAccessor
     * @param EntityManager $entityManager
     */
    public function __construct(PropertyAccessorInterface $propertyAccessor, EntityManagerInterface $entityManager)
    {
        $this->propertyAccessor = $propertyAccessor;
        $this->entityManager = $entityManager;
    }

    /**
     * @param DtoSerializableInterface $entity
     * @param Relation $relation
     * @throws \Exception
     */
    public function handleRelation(DtoSerializableInterface $entity, Relation $relation): void
    {
        $attribute = $relation->getAttribute();
        $attributeValue = $this->propertyAccessor->getValue($entity, $attribute);

        if (!$this->propertyAccessor->isWritable($entity, $relation->getRelation())) {
            return;
        }

        if ($attributeValue === null) {
            $this->propertyAccessor->setValue($entity, $attribute, null);
            return;
        }

        $repository = $this->entityManager->getRepository($relation->getTargetEntityClass());
        if (!is_callable([$repository, $relation->getRepositoryMethod()])) {
            throw new \Exception(
                sprintf('%s: Method is not callable: %s', get_class($repository), $relation->getRepositoryMethod())
            );
        }
        $relationObject = call_user_func(
            [$repository, $relation->getRepositoryMethod()],
            [$relation->getTargetEntityAttribute() => $attributeValue]
        );
        if (!$relationObject) {
            throw new NotFoundHttpException(sprintf('%s object not found.', $relation->getTargetEntityClass()));
        }
        
        if ($relation->getSetter()) {
            call_user_func([$entity, $relation->getSetter()], $relationObject);
        } else {
            $this->propertyAccessor->setValue($entity, $relation->getRelation(), $relationObject);
        }
    }
}
