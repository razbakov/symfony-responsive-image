<?php

namespace Ommax\ResponsiveImageBundle\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('picture', template: '@ResponsiveImage/components/picture.html.twig')]
class Picture extends Img
{
    public function getBreakpoints(): array
    {
        if (!$this->width || !str_contains($this->width, ':')) {
            return [];
        }

        $breakpoints = [];
        $widths = $this->transformer->parseWidth($this->width);
        
        foreach ($widths as $breakpoint => $width) {
            if ($breakpoint === 'default') {
                continue;
            }

            $minWidth = match ($breakpoint) {
                'sm' => '640px',
                'md' => '768px',
                'lg' => '1024px',
                'xl' => '1280px',
                '2xl' => '1536px',
                default => null,
            };

            if ($minWidth) {
                $breakpoints[] = [
                    'media' => "(min-width: {$minWidth})",
                    'srcset' => $this->getImage(['width' => $width['value']]),
                ];
            }
        }

        return $breakpoints;
    }

    public function getSrcComputed(): string
    {
        $widths = $this->transformer->parseWidth($this->width);
        $width = $widths['default'] ?? array_shift($widths);
        return $this->getImage(['width' => $width['value']]);
    }
}
