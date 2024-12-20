<?php

namespace Ommax\ResponsiveImageBundle\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('picture', template: '@ResponsiveImage/components/picture.html.twig')]
class Picture extends Img
{
    public function getBreakpoints(): array
    {
        if (!$this->width) {
            return [];
        }

        // Handle viewport width units
        if (str_contains($this->width, 'vw')) {
            $breakpoints = [];
            
            foreach ($this->transformer->getBreakpoints() as $breakpoint => $size) {
                $breakpoints[] = [
                    'media' => "(min-width: {$size}px)",
                    'srcset' => $this->getImage(['width' => $size]) . " {$size}w",
                    'sizes' => $this->width,
                ];
            }
            
            return $breakpoints;
        }

        // Handle regular breakpoints
        if (!str_contains($this->width, ':')) {
            return [];
        }

        $breakpoints = [];
        $widths = $this->transformer->parseWidth($this->width);
        $configuredBreakpoints = $this->transformer->getBreakpoints();

        foreach ($widths as $breakpoint => $width) {
            if ('default' === $breakpoint) {
                continue;
            }

            if (isset($configuredBreakpoints[$breakpoint])) {
                $breakpoints[] = [
                    'media' => "(max-width: {$configuredBreakpoints[$breakpoint]}px)",
                    'srcset' => $this->getImage(['width' => $width['value']]),
                ];
            }
        }

        return $breakpoints;
    }

    public function getSizes(): ?string
    {
        if (str_contains($this->width, 'vw')) {
            return $this->width;
        }

        return null;
    }
}
