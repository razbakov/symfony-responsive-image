<?php

namespace YourVendor\ResponsiveImageBundle\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('picture')]
class Picture extends AbstractComponent
{
    public string $src;
    public string $alt = '';
    public array $sources = [];
    public string $format = 'webp';
    public int $quality = 80;
    public bool $lazy = true;
    public bool $priority = false;
    public bool $preload = false;
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

        // Use dimensions from the largest source
        $this->dimensions = $this->calculateDimensions();
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    private function calculateDimensions(): array
    {
        $largestSource = $this->getLargestSource();
        
        if (!$largestSource) {
            // Fallback to original image dimensions
            $image = $this->imageManager->make($this->src);
            return [
                'width' => $image->width(),
                'height' => $image->height()
            ];
        }

        if ($largestSource['ratio']) {
            return [
                'width' => $largestSource['width'],
                'height' => $this->calculateHeightFromRatio(
                    $largestSource['width'], 
                    $largestSource['ratio']
                )
            ];
        }

        return [
            'width' => $largestSource['width'],
            'height' => $largestSource['height']
        ];
    }

    private function getLargestSource(): ?array
    {
        $largest = null;
        $maxWidth = 0;

        foreach ($this->sources as $source) {
            if ($source['width'] > $maxWidth) {
                $largest = $source;
                $maxWidth = $source['width'];
            }
        }

        return $largest;
    }

    public function getWidth(): int
    {
        return $this->dimensions['width'];
    }

    public function getHeight(): int
    {
        return $this->dimensions['height'];
    }
} 