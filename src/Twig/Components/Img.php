<?php

namespace Ommax\ResponsiveImageBundle\Twig\Components;

use Ommax\ResponsiveImageBundle\Provider\ProviderRegistry;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PreMount;

#[AsTwigComponent('img', template: '@ResponsiveImage/components/img.html.twig')]
class Img
{
    public string $src;
    public ?string $srcComputed = null;
    public ?string $alt = null;
    public ?string $width = null;
    public ?int $widthComputed = null;
    public ?int $height = null;
    public ?string $ratio = null;
    public ?string $fit = 'cover';
    public ?string $focal = 'center';
    public ?string $quality = '80';
    public ?string $loading = 'lazy';
    public ?string $fetchpriority = 'auto';
    public ?bool $preload = false;
    public ?string $background = null;
    public ?string $fallback = 'auto';
    public ?string $class = null;
    public ?string $preset = null;
    public ?string $placeholder = null;
    public ?string $sizes = null;
    public ?string $srcset = null;

    private array $parsedWidths = [];
    private array $widths = [];

    public function __construct(
        private ParameterBagInterface $params,
        private ProviderRegistry $providerRegistry,
    ) {
        $this->params = $params;
    }

    #[PreMount]
    public function preMount(array $data): array
    {
        $resolver = new OptionsResolver();

        $resolver
            ->setDefined([
                'alt',
                'width',
                'height',
                'ratio',
                'fit',
                'focal',
                'quality',
                'loading',
                'fetchpriority',
                'preload',
                'background',
                'fallback',
                'class',
                'preset',
                'placeholder',
                'srcset',
            ])
            ->setIgnoreUndefined(true);

        $resolver->setRequired('src');

        $resolver->setAllowedTypes('src', 'string');
        $resolver->setAllowedTypes('alt', 'string');
        $resolver->setAllowedTypes('width', ['string', 'int']);
        $resolver->setAllowedTypes('height', 'int');
        $resolver->setAllowedTypes('ratio', 'string');
        $resolver->setAllowedTypes('fit', 'string');
        $resolver->setAllowedTypes('focal', 'string');
        $resolver->setAllowedTypes('quality', 'string');
        $resolver->setAllowedTypes('loading', 'string');
        $resolver->setAllowedTypes('fetchpriority', 'string');
        $resolver->setAllowedTypes('preload', 'bool');
        $resolver->setAllowedTypes('background', 'string');
        $resolver->setAllowedTypes('fallback', 'string');
        $resolver->setAllowedTypes('class', 'string');
        $resolver->setAllowedTypes('preset', 'string');
        $resolver->setAllowedTypes('placeholder', 'string');

        if (isset($data['preset'])) {
            $presetName = $data['preset'];
            $presets = $this->params->get('responsive_image.presets');

            if (isset($presets[$presetName])) {
                $data = array_merge($presets[$presetName], $data);
            }
        }

        return $resolver->resolve($data) + $data;
    }

    public function mount(string $src, $width = null): void
    {
        if (empty($src)) {
            throw new \InvalidArgumentException('Image src cannot be empty');
        }

        $this->src = $src;
        $this->width = $width;
        $this->srcset = $this->getSrcset();
        
        if ($this->width) {
            $this->parsedWidths = $this->parseWidthString($this->width);
            $this->widths = $this->calculateSizes($this->parsedWidths, [
                'sm' => 640,
                'md' => 768,
                'lg' => 1024,
                'xl' => 1280,
                '2xl' => 1536
            ]);

            if (count($this->widths) > 0) {
                $this->widthComputed = min($this->widths);
            }
            $this->srcComputed = $this->getImage(['width' => $this->widthComputed]);
        } else {
            $this->srcComputed = $this->getImage();
        }
    }

    private function getSrcset(): string
    {
        if (!$this->width) {
            return '';
        }

        $srcset = [];

        foreach ($this->widths as $width) {
            $srcset[] = sprintf('%s %sw',
                $this->getImage(['width' => $width]),
                $width
            );
        }

        return implode(', ', $srcset);
    }

    private function parseWidthString(string $width): array
    {
        $parts = preg_split('/\s+/', trim($width));
        $widths = [];
        
        foreach ($parts as $part) {
            if (strpos($part, ':') !== false) {
                [$breakpoint, $value] = explode(':', $part);
                $widths[$breakpoint] = $this->normalizeWidthValue($value);
            } else {
                $widths['default'] = $this->normalizeWidthValue($part);
            }
        }
        
        return $widths;
    }

    private function normalizeWidthValue(string $value): array
    {
        $isVw = str_ends_with($value, 'vw');
        return [
            'value' => (int) $value,
            'isVw' => $isVw
        ];
    }

    private function calculateSizes(array $widths, array $breakpoints): array
    {
        $sizes = [];
        
        // If no breakpoints specified, treat each width as a breakpoint
        if (count($widths) === 1 && isset($widths['default'])) {
            $width = $widths['default']['value'];
            if ($widths['default']['isVw']) {
                return array_values($breakpoints);
            }
            if ($width > $breakpoints['sm']) {
                return array_filter($breakpoints, fn($bp) => $bp <= $width);
            }
            return [$width];
        }

        // Sort breakpoints and process them in order
        $breakpointOrder = ['default', 'sm', 'md', 'lg', 'xl', '2xl'];
        $currentWidth = null;
        
        foreach ($breakpointOrder as $bp) {
            if (!isset($widths[$bp])) continue;
            
            $width = $widths[$bp];
            if ($width['isVw']) {
                // Add all remaining breakpoint sizes
                $sizes = array_merge($sizes,
                    array_filter($breakpoints, fn($bpSize) => $bpSize >= ($currentWidth ?? 0))
                );
                break;
            } else {
                $sizes[] = $width['value'];
                $currentWidth = $width['value'];
            }
        }

        return array_unique($sizes);
    }

    private function getImage(array $modifiers = []): string
    {
        return $this->providerRegistry->getProvider()->getImage($this->src, $modifiers);
    }
}
