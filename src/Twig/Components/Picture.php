<?php

namespace Ommax\ResponsiveImageBundle\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('picture', template: '@ResponsiveImage/components/picture.html.twig')]
class Picture
{
    public function __construct(
        public string $src,
        public ?string $alt = null,
        public ?string $width = null,
        public ?string $height = null,
        public ?string $ratio = null,
        public ?string $fit = 'cover',
        public ?string $focal = 'center',
        public ?string $quality = '80',
        public ?string $loading = 'lazy',
        public ?string $fetchpriority = 'auto',
        public ?bool $preload = false,
        public ?string $background = null,
        public ?string $sizes = null,
        public ?string $fallback = 'auto',
        public ?string $class = null,
        public ?string $preset = null,
    ) {}

    public function mount(): void
    {
        // Handle preset if specified
        if ($this->preset) {
            // TODO: Load and apply preset configuration
        }
        
        // Validate required attributes
        if (!$this->src) {
            throw new \InvalidArgumentException('The "src" attribute is required for picture component.');
        }
        
        // TODO: Process breakpoints and generate art-directed variants
    }
} 