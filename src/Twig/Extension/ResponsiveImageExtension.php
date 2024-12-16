<?php

namespace Ommax\ResponsiveImageBundle\Twig\Extension;

use Ommax\ResponsiveImageBundle\Service\PreloadManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ResponsiveImageExtension extends AbstractExtension
{
    public function __construct(
        private PreloadManager $preloadManager
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('responsive_image_preloads', [$this->preloadManager, 'getPreloadTags'], ['is_safe' => ['html']]),
        ];
    }
}
