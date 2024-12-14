<?php

namespace Ommax\ResponsiveImageBundle\Tests;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TestKernel extends Kernel
{
    public function registerBundles(): array
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Symfony\UX\TwigComponent\TwigComponentBundle(),
            new \Ommax\ResponsiveImageBundle\ResponsiveImageBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/config/config.yaml');
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $container->loadFromExtension('framework', [
            'test' => true,
            'secret' => 'test',
        ]);
        
        $container->loadFromExtension('twig', [
            'default_path' => __DIR__.'/templates',
        ]);
        
        $loader->load(__DIR__.'/../src/Resources/config/services.yaml');
    }
}
