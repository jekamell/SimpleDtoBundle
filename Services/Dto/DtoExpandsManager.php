<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\Services\Dto;

use Mell\Bundle\SimpleDtoBundle\Model\Dto;
use Mell\Bundle\SimpleDtoBundle\Model\DtoInterface;
use Mell\Bundle\SimpleDtoBundle\Serializer\Normalizer\DtoNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Class DtoExpandsManager
 * @package Mell\Bundle\SimpleDtoBundle\Services\Dto
 */
class DtoExpandsManager
{
    /** @var Serializer */
    protected $serializer;

    /**
     * DtoExpandsManager constructor.
     * @param Serializer $serializer
     */
    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param Dto $dto
     * @param array $expands
     * @return Dto
     */
    public function processExpands(Dto $dto, array $expands): Dto
    {
        // TODO: check if expand allowed
        $entity = $dto->getOriginalData();
        $data = [];
        foreach ($expands as $expand => $fields) {
            $getter = 'get' . ucfirst($expand);
            if (!is_callable([$entity, $getter])) {
                continue;
            }
            $object = call_user_func([$entity, $getter]);
            $data[$expand] = $this->serializer->normalize(
                $object,
                DtoNormalizer::FORMAT_DTO,
                ['groups' => [DtoInterface::DTO_GROUP_READ], 'fields' => $fields]
            );
        }

        return $dto->setRawData(array_merge($dto->getRawData(), ['_expands' => $data]));
    }
}
