<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\Serializer\Normalizer;

use Mell\Bundle\SimpleDtoBundle\Model\DtoSerializable;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Class DtoNormalizer
 * @package Mell\SimpleDtoBundle\Serializer\Normalizer
 */
class DtoNormalizer extends ObjectNormalizer
{
    const FORMAT_DTO = 'dto';

    /**
     * {@inheritdoc}
     */
    protected function isAllowedAttribute($classOrObject, $attribute, $format = null, array $context = [])
    {
        if (in_array($attribute, $this->ignoredAttributes)) {
            return false;
        }

        return empty($context['fields']) || in_array($attribute, $context['fields']);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $format === self::FORMAT_DTO && $data instanceof DtoSerializable;
    }
}
