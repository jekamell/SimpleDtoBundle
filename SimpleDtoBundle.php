<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle;

use Mell\Bundle\SimpleDtoBundle\DependencyInjection\ChainLoaderPass;
use Mell\Bundle\SimpleDtoBundle\DependencyInjection\OverrideSerializerClassMetadataPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class SimpleDtoBundle
 * @package Mell\Bundle\SimpleDtoBundle
 */
class SimpleDtoBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ChainLoaderPass());
    }
}
