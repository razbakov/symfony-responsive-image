<?php

namespace YourVendor\ResponsiveImageBundle\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Intervention\Image\ImageManager;
use YourVendor\ResponsiveImageBundle\Service\PreloadManager;

#[AsTwigComponent('responsive_image', exposePublicProps: false)]
class ResponsiveImage
{
    private ImageManager $imageManager;
    
    public string $src;
    public ?string $alt = null;
    public ?array $sizes = null;
    public ?array $breakpoints = null;
    public ?string $format = 'webp';
    public ?bool $lazy = true;
    public ?int $quality = 80;
    public bool $priority = false;
    public bool $preload = false;
    public bool $async = false;
    
    /**
     * Additional HTML attributes that aren't explicit properties
     */
    private array $attributes = [];

    public function __construct(
        private ImageManager $imageManager,
        private PreloadManager $preloadManager
    ) {}
    
    public function mount(array $data): void
    {
        // Handle preloading
        if ($this->preload) {
            $this->preloadManager->addPreload(
                $this->src,
                $this->getSrcset(),
                $this->sizes ? implode(', ', $this->sizes) : null
            );
        }

        // Store any additional attributes that aren't explicit properties
        foreach ($data as $key => $value) {
            if (!property_exists($this, $key)) {
                $this->attributes[$key] = $value;
            }
        }
    }
    
    public function getSrcset(): string
    {
        $breakpoints = $this->breakpoints ?? [640, 768, 1024, 1280, 1536];
        $srcset = [];
        
        foreach ($breakpoints as $width) {
            $resizedImage = $this->getResizedImage($width);
            $srcset[] = sprintf('%s %dw', $resizedImage, $width);
        }
        
        return implode(', ', $srcset);
    }
    
    private function getResizedImage(int $width): string
    {
        // Implementation for image resizing and caching
        $image = $this->imageManager->make($this->src);
        $image->resize($width, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        
        // Generate unique cache path
        $cachePath = sprintf('media/cache/%s_%d.%s', 
            md5($this->src), 
            $width,
            $this->format
        );
        
        // Save resized image
        $image->save(public_path($cachePath), $this->quality, $this->format);
        
        return $cachePath;
    }

    /**
     * Get all HTML attributes for the img tag
     */
    public function getAttributes(): array
    {
        $defaultAttributes = [
            'src' => $this->src,
            'srcset' => $this->getSrcset(),
        ];

        if ($this->sizes) {
            $defaultAttributes['sizes'] = implode(', ', $this->sizes);
        }

        if ($this->alt) {
            $defaultAttributes['alt'] = $this->alt;
        }

        // Handle priority loading
        if ($this->priority) {
            $defaultAttributes['fetchpriority'] = 'high';
            // Disable lazy loading when priority is true
            $this->lazy = false;
        }

        // Add lazy loading if enabled
        if ($this->lazy) {
            $defaultAttributes['loading'] = 'lazy';
        }

        // Add async decoding if enabled
        if ($this->async) {
            $defaultAttributes['decoding'] = 'async';
        }

        // Merge with custom attributes, allowing custom attributes to override defaults
        return array_merge($defaultAttributes, $this->attributes);
    }
} 