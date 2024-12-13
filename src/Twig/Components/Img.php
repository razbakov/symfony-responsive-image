<?php

namespace YourVendor\ResponsiveImageBundle\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('img')]
class Img extends AbstractComponent
{
    public string $src;
    public string $alt = '';
    public ?int $width = null;
    public ?int $height = null;
    public ?string $ratio = null;
    public string $fit = 'cover';
    public string $focal = 'center';
    public ?string $format = 'webp';
    public int $quality = 80;
    public bool $lazy = true;
    public bool $priority = false;
    public bool $preload = false;
    public ?string $background = null;
    public ?array $breakpoints = null;
    public ?array $sizes = null;
    public ?string $placeholder = null;
    public ?string $placeholderColor = null;

    /**
     * Additional HTML attributes that aren't explicit properties
     */
    private array $attributes = [];

    private ?array $dimensions = null;

    public function mount(array $data): void
    {
        // Store any additional attributes that aren't explicit properties
        foreach ($data as $key => $value) {
            if (!property_exists($this, $key)) {
                $this->attributes[$key] = $value;
            }
        }

        // Calculate dimensions from original image if not specified
        if (!$this->width || !$this->height) {
            $this->dimensions = $this->calculateDimensions();
        }
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    private function calculateDimensions(): array
    {
        $image = $this->imageManager->make($this->src);
        $originalWidth = $image->width();
        $originalHeight = $image->height();

        if ($this->ratio) {
            // Calculate height from ratio if width is set
            if ($this->width) {
                $height = $this->calculateHeightFromRatio($this->width, $this->ratio);
                return ['width' => $this->width, 'height' => $height];
            }
            // Calculate width from ratio if height is set
            if ($this->height) {
                $width = $this->calculateWidthFromRatio($this->height, $this->ratio);
                return ['width' => $width, 'height' => $this->height];
            }
            // Use original width and calculate height if neither is set
            return [
                'width' => $originalWidth,
                'height' => $this->calculateHeightFromRatio($originalWidth, $this->ratio)
            ];
        }

        // Use original dimensions if no width/height/ratio specified
        return [
            'width' => $this->width ?? $originalWidth,
            'height' => $this->height ?? $originalHeight
        ];
    }

    public function getWidth(): int
    {
        return $this->dimensions['width'] ?? $this->width;
    }

    public function getHeight(): int
    {
        return $this->dimensions['height'] ?? $this->height;
    }
} 