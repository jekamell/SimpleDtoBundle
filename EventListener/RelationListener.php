<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\EventListener;

use Mell\Bundle\SimpleDtoBundle\Event\ApiEvent;
use Mell\Bundle\SimpleDtoBundle\Model\DtoSerializableInterface;
use Mell\Bundle\SimpleDtoBundle\Serializer\Mapping\ClassMetadataDecorator;
use Mell\Bundle\SimpleDtoBundle\Services\Crud\RelationManager;
use Mell\Bundle\SimpleDtoBundle\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;

/**
 * Class RelationListener
 */
class RelationListener
{
    /** @var ClassMetadataFactoryInterface */
    protected $metadataFactory;
    /** @var RelationManager */
    protected $relationManager;

    /**
     * RelationListener constructor.
     * @param ClassMetadataFactoryInterface $metadataFactory
     * @param RelationManager $relationManager
     */
    public function __construct(ClassMetadataFactoryInterface $metadataFactory, RelationManager $relationManager)
    {
        $this->metadataFactory = $metadataFactory;
        $this->relationManager = $relationManager;
    }

    /**
     * @param ApiEvent $apiEvent
     * @throws \Exception
     */
    public function updateRelations(ApiEvent $apiEvent): void
    {
        $entity = $apiEvent->getData();
        if (!in_array($apiEvent->getAction(), [ApiEvent::ACTION_CREATE, ApiEvent::ACTION_UPDATE])
            || !$entity instanceof DtoSerializableInterface
        ) {
            return;
        }


        $group = $apiEvent->getContext()['group'] ?? null;
        /** @var ClassMetadataDecorator $metadata */
        $metadata = $this->metadataFactory->getMetadataFor(get_class($entity));
        foreach ($metadata->getRelations() as $relation) {
            if ($group && $relation->getGroups() && !in_array($group, $relation->getGroups())) {
                continue;
            }
            $this->relationManager->handleRelation($entity, $relation);
        }
    }
}
