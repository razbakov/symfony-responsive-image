<?php

namespace Ommax\ResponsiveImageBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ProviderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('responsive_image.provider_registry')) {
            return;
        }

        $registryDefinition = $container->getDefinition('responsive_image.provider_registry');
        $taggedServices = $container->findTaggedServiceIds('responsive_image.provider');

        foreach ($taggedServices as $id => $tags) {
            $registryDefinition->addMethodCall('addProvider', [new Reference($id)]);
        }
    }
} 