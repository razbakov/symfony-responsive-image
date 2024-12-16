<?php

namespace Ommax\ResponsiveImageBundle\Twig\Components;

use Ommax\ResponsiveImageBundle\Provider\ProviderRegistry;
use Ommax\ResponsiveImageBundle\Service\Transformer;
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

    private array $widths = [];

    public function __construct(
        private ParameterBagInterface $params,
        private ProviderRegistry $providerRegistry,
        private Transformer $transformer,
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
        $resolver->setAllowedTypes('alt', ['string', 'null']);
        $resolver->setAllowedTypes('width', ['string', 'int', 'null']);
        $resolver->setAllowedTypes('height', ['int', 'null']);
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

        // Normalize width value but preserve original format
        if (isset($data['width'])) {
            if (is_numeric($data['width'])) {
                $data['width'] = (string) $data['width'];
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
        
        if ($this->width) {
            // Get sizes from transformer
            $this->widths = $this->transformer->getSizes($this->width);
            
            // Determine the initial width based on the pattern
            if (preg_match('/^\d+vw/', $this->width)) {
                // If pattern starts with viewport width
                $smallestWidth = PHP_INT_MAX;
                foreach ($this->widths as $width) {
                    if ($width['value'] < $smallestWidth && $width['vw'] !== '0') {
                        $smallestWidth = $width['value'];
                    }
                }
                $this->widthComputed = $smallestWidth;
            } else {
                // For fixed widths or patterns starting with fixed width
                $this->widthComputed = $this->widths['default']['value'];
            }

            $this->srcComputed = $this->getImage(['width' => $this->widthComputed]);
            
            // Generate srcset and sizes for responsive widths or breakpoint patterns
            if (str_contains($this->width, 'vw') || str_contains($this->width, ':')) {
                $this->srcset = $this->getSrcset();
                $this->sizes = $this->getSizes();
            }
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
            if ($width['value'] > 0) { // Only include positive widths
                $srcset[] = sprintf('%s %sw',
                    $this->getImage(['width' => $width['value']]),
                    $width['value']
                );
            }
        }

        return implode(', ', $srcset);
    }

    private function getSizes(): string
    {
        if (!$this->width) {
            return '';
        }

        // Special case: if it's just a viewport width with no breakpoints
        if ($this->width === '100vw') {
            return '100vw';
        }

        $breakpoints = [
            'sm' => 640,
            'md' => 768,
            'lg' => 1024,
            'xl' => 1280,
            '2xl' => 1536
        ];

        $sizes = [];
        $breakpointKeys = array_keys($breakpoints);
        
        // Find the largest explicit value for default size (no media query)
        $largestValue = null;
        foreach (array_reverse($breakpointKeys) as $key) {
            if (isset($this->widths[$key])) {
                $largestValue = $this->widths[$key];
                break;
            }
        }

        // If we found a largest value, use it as the default (no media query)
        if ($largestValue) {
            $sizes[] = $this->formatSizeValue($largestValue);
        }

        // Process breakpoints from largest to smallest
        $sizeVariants = [];
        $currentValue = $largestValue;
        
        foreach (array_reverse($breakpointKeys) as $i => $key) {
            if (isset($this->widths[$key])) {
                // Find the next breakpoint that has a value
                $nextValue = null;
                for ($j = $i + 1; $j < count($breakpointKeys); $j++) {
                    $nextKey = array_reverse($breakpointKeys)[$j];
                    if (isset($this->widths[$nextKey])) {
                        $nextValue = $this->widths[$nextKey];
                        break;
                    }
                }
                
                // If no next breakpoint value found and we have a default value
                if (!$nextValue && isset($this->widths['default'])) {
                    $nextValue = $this->widths['default'];
                }
                
                // Add current value to size variants
                $sizeVariants[] = [
                    'size' => $this->formatSizeValue($this->widths[$key]),
                    'screenMaxWidth' => $breakpoints[$key],
                    'media' => sprintf('(max-width: %dpx)', $breakpoints[$key])
                ];
                
                // If next value is different, add it at this breakpoint
                if ($nextValue && !$this->isSameValue($this->widths[$key], $nextValue)) {
                    $sizeVariants[] = [
                        'size' => $this->formatSizeValue($nextValue),
                        'screenMaxWidth' => $breakpoints[$key],
                        'media' => sprintf('(max-width: %dpx)', $breakpoints[$key])
                    ];
                }
                
                $currentValue = $this->widths[$key];
            }
        }

        // Sort variants by screen width (largest to smallest)
        usort($sizeVariants, fn($a, $b) => $b['screenMaxWidth'] - $a['screenMaxWidth']);

        // Add size variants to sizes array
        foreach ($sizeVariants as $variant) {
            $sizes[] = $variant['media'] . ' ' . $variant['size'];
        }

        // Add default value if it exists and differs from sm breakpoint
        if (isset($this->widths['default']) && 
            (!isset($this->widths['sm']) || !$this->isSameValue($this->widths['default'], $this->widths['sm']))) {
            $sizes[] = sprintf('(max-width: %dpx) %s',
                $breakpoints['sm'],
                $this->formatSizeValue($this->widths['default'])
            );
        }

        return implode(', ', array_unique($sizes));
    }

    private function isSameValue(array $value1, array $value2): bool
    {
        return $value1['value'] === $value2['value'] && $value1['vw'] === $value2['vw'];
    }

    private function formatSizeValue(array $width): string
    {
        return $width['vw'] !== '0'
            ? $width['vw'] . 'vw'
            : $width['value'] . 'px';
    }

    private function getImage(array $modifiers = []): string
    {
        return $this->providerRegistry->getProvider()->getImage($this->src, $modifiers);
    }
}
