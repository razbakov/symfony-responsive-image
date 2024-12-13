<?php

namespace YourVendor\ResponsiveImageBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use YourVendor\ResponsiveImageBundle\Service\PreloadManager;

class ResponsiveImageExtension extends AbstractExtension
{
    public function __construct(
        private PreloadManager $preloadManager
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('responsive_image_preloads', [$this, 'renderPreloads'], ['is_safe' => ['html']]),
        ];
    }

    public function renderPreloads(): string
    {
        $html = '';
        foreach ($this->preloadManager->getPreloads() as $preload) {
            $html .= sprintf(
                '<link rel="preload" as="image" href="%s" imagesrcset="%s"%s>',
                $preload['src'],
                $preload['srcset'],
                $preload['sizes'] ? sprintf(' imagesizes="%s"', $preload['sizes']) : ''
            );
        }
        return $html;
    }
} 