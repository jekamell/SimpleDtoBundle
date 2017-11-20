<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\EventListener;

use Mell\Bundle\SimpleDtoBundle\Event\ApiEvent;
use Mell\Bundle\SimpleDtoBundle\Model\DtoSerializableInterface;
use Mell\Bundle\SimpleDtoBundle\Serializer\Mapping\ClassMetadataDecorator;
use Mell\Bundle\SimpleDtoBundle\Services\Crud\RelationManager;
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
    /** @var bool */
    protected $relationHandlingEnabled;

    /**
     * RelationListener constructor.
     * @param MetadataFactoryInterface $metadataFactory
     * @param RelationManager $relationManager
     * @param bool $relationHandlingEnabled
     */
    public function __construct(
        ClassMetadataFactoryInterface $metadataFactory,
        RelationManager $relationManager,
        $relationHandlingEnabled
    ) {
        $this->metadataFactory = $metadataFactory;
        $this->relationManager = $relationManager;
        $this->relationHandlingEnabled = $relationHandlingEnabled;
    }

    /**
     * @param ApiEvent $apiEvent
     */
    public function updateRelations(ApiEvent $apiEvent): void
    {
        $entity = $apiEvent->getData();
        if (!$this->relationHandlingEnabled
            || !in_array($apiEvent->getAction(), [ApiEvent::ACTION_CREATE, ApiEvent::ACTION_UPDATE])
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
