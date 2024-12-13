<?php

namespace Ommax\ResponsiveImageBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Ommax\ResponsiveImageBundle\Provider\ProviderInterface;

class ResponsiveImageExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        // Register the provider interface for autoconfiguration
        $container->registerForAutoconfiguration(ProviderInterface::class)
            ->addTag('responsive_image.provider');

        // Set parameters
        $container->setParameter('responsive_image.default_provider', $config['default_provider']);
        $container->setParameter('responsive_image.missing_image_placeholder', $config['missing_image_placeholder']);
        $container->setParameter('responsive_image.defaults', $config['defaults']);
        $container->setParameter('responsive_image.providers', $config['providers']);
        $container->setParameter('responsive_image.presets', $config['presets'] ?? []);

        // Configure providers
        foreach ($config['providers'] as $name => $providerConfig) {
            $providerId = sprintf('responsive_image.provider.%s', $name);
            if ($container->hasDefinition($providerId)) {
                $providerDef = $container->getDefinition($providerId);
                $providerDef->addMethodCall('configure', [$providerConfig]);
            }
        }

        // Configure default options for the registry
        $registryDef = $container->getDefinition('responsive_image.provider_registry');
        $registryDef->setArgument(0, $config['default_provider']);
    }

    public function getAlias(): string
    {
        return 'responsive_image';
    }
} 