<?php

namespace Ommax\ResponsiveImageBundle;

use Ommax\ResponsiveImageBundle\DependencyInjection\Compiler\ProviderPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ResponsiveImageBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new ProviderPass());
    }
}
