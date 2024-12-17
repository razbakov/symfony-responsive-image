<?php

namespace Ommax\ResponsiveImageBundle\Twig\Components;

use Ommax\ResponsiveImageBundle\Provider\ProviderRegistry;
use Ommax\ResponsiveImageBundle\Service\PreloadManager;
use Ommax\ResponsiveImageBundle\Service\Transformer;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PreMount;

#[AsTwigComponent('img', template: '@ResponsiveImage/components/img.html.twig')]
class Img
{
    public const EMPTY_GIF = 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';

    public string $src;
    public ?string $srcComputed = null;
    public ?string $alt = null;
    public ?string $width = null;
    public ?int $widthComputed = null;
    public ?int $height = null;
    public ?string $ratio = null;
    public ?string $fit = null;
    public ?string $focal = null;
    public ?string $quality = null;
    public ?string $format = null;
    public ?string $loading = null;
    public ?string $fetchpriority = null;
    public ?bool $preload = null;
    public ?string $background = null;
    public ?string $fallback = null;
    public ?string $class = null;
    public ?string $preset = null;
    public ?string $placeholder = null;
    public ?string $placeholderClass = null;
    public ?string $sizes = null;
    public ?string $srcset = null;
    public ?string $densities = null;

    protected array $widths = [];

    public function __construct(
        protected ParameterBagInterface $params,
        protected ProviderRegistry $providerRegistry,
        protected Transformer $transformer,
        protected PreloadManager $preloadManager,
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
                'placeholder-class',
                'srcset',
                'id',
                'referrerpolicy',
                'sizes',
                'style',
                'title',
                'crossorigin',
                'decoding',
                'format',
                'densities',
            ]);

        // Allow any data-* and aria-* attributes
        $resolver->setDefaults([]);
        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'data-') || str_starts_with($key, 'aria-')) {
                $resolver->setDefined($key);
                $resolver->setAllowedTypes($key, ['string', 'null']);
            }
        }

        $resolver->setRequired('src');

        $resolver->setAllowedTypes('src', 'string');
        $resolver->setAllowedTypes('alt', ['string', 'null']);
        $resolver->setAllowedTypes('width', ['string', 'int', 'null']);
        $resolver->setAllowedTypes('height', ['int', 'null']);
        $resolver->setAllowedTypes('ratio', ['string', 'null']);
        $resolver->setAllowedTypes('fit', ['string', 'null']);
        $resolver->setAllowedTypes('focal', ['string', 'null']);
        $resolver->setAllowedTypes('quality', ['string', 'null']);
        $resolver->setAllowedTypes('loading', ['string', 'null']);
        $resolver->setAllowedTypes('fetchpriority', ['string', 'null']);
        $resolver->setAllowedTypes('preload', ['bool', 'null']);
        $resolver->setAllowedTypes('background', ['string', 'null']);
        $resolver->setAllowedTypes('fallback', ['string', 'null']);
        $resolver->setAllowedTypes('class', ['string', 'null']);
        $resolver->setAllowedTypes('preset', ['string', 'null']);
        $resolver->setAllowedTypes('placeholder', ['string', 'null']);
        $resolver->setAllowedTypes('placeholder-class', ['string', 'null']);
        $resolver->setAllowedTypes('sizes', ['string', 'null']);
        $resolver->setAllowedTypes('id', ['string', 'null']);
        $resolver->setAllowedTypes('referrerpolicy', ['string', 'null']);
        $resolver->setAllowedTypes('style', ['string', 'null']);
        $resolver->setAllowedTypes('title', ['string', 'null']);
        $resolver->setAllowedTypes('crossorigin', ['string', 'null']);
        $resolver->setAllowedTypes('decoding', ['string', 'null']);
        $resolver->setAllowedTypes('densities', ['string', 'null']);

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

    public function mount(
        string $src,
        $width = null,
        ?bool $preload = null,
        ?string $format = null,
        ?string $quality = null,
        ?string $fit = null,
        ?string $focal = null,
        ?string $fallback = null,
        ?string $background = null,
        ?string $ratio = null,
        ?string $densities = null,
    ): void {
        if (empty($src)) {
            throw new \InvalidArgumentException('Image src cannot be empty');
        }

        $this->src = $src;
        $this->width = $width;
        $this->format = $format;
        $this->quality = $quality;
        $this->fit = $fit;
        $this->focal = $focal;
        $this->fallback = $fallback;
        $this->background = $background;
        $this->ratio = $ratio;
        $this->densities = $densities;

        if (null !== $preload) {
            $this->preload = $preload;
        }

        if ($this->width) {
            // Get sizes from transformer
            $this->widths = $this->transformer->parseWidth($this->width);

            // Use new transformer method to determine initial width
            $this->widthComputed = $this->transformer->getInitialWidth($this->widths, $this->width);

            // For the main src, apply fallback format
            $this->srcComputed = $this->getImage(['width' => $this->widthComputed], true);

            // Handle additional sizes from densities
            if ($this->densities) {
                if (!str_contains($this->width, 'vw') && !str_contains($this->width, ':')) {
                    // For fixed widths, get density-based widths
                    $widthsForSrcset = $this->transformer->getDensityBasedWidths($this->widthComputed, $this->densities);
                    
                    // Build srcset manually for fixed widths
                    $srcsetParts = [];
                    foreach ($widthsForSrcset as $w) {
                        $srcsetParts[] = $this->getImage(['width' => $w], false) . ' ' . $w . 'w';
                    }
                    $this->srcset = implode(', ', $srcsetParts);
                } else {
                    // For responsive widths, merge with density-based widths
                    $densityWidths = $this->transformer->getDensityBasedWidths($this->widthComputed, $this->densities);
                    $this->widths = array_unique(array_merge($this->widths, $densityWidths));
                    sort($this->widths);
                    
                    // Generate srcset with all widths
                    $this->srcset = $this->transformer->getSrcset(
                        $this->src,
                        $this->widths,
                        fn ($modifiers) => $this->getImage($modifiers, false)
                    );
                }
            }
            // Generate srcset and sizes for responsive widths or breakpoint patterns
            elseif (str_contains($this->width, 'vw') || str_contains($this->width, ':')) {
                $this->srcset = $this->transformer->getSrcset(
                    $this->src,
                    $this->widths,
                    fn ($modifiers) => $this->getImage($modifiers, false)
                );
                $this->sizes = $this->transformer->getSizes($this->widths);
            }
        } else {
            $this->srcComputed = $this->getImage([], true); // Apply fallback for main src
        }

        if ($this->preload) {
            $this->preloadManager->addPreloadImage($this->srcComputed, [
                'srcset' => $this->srcset,
                'sizes' => $this->sizes,
            ]);
        }
    }

    protected function getImage(array $modifiers = [], bool $applyFallback = false): string
    {
        // Handle format
        if ($applyFallback && $this->fallback) {
            if ($this->fallback === 'auto') {
                // Auto fallback logic based on original image format
                $extension = $this->getImageExtension();
                
                // PNG fallback for formats that might have transparency
                if (in_array($extension, ['png', 'webp', 'gif'])) {
                    $modifiers['format'] = 'png';
                } else {
                    // JPEG fallback for all other formats
                    $modifiers['format'] = 'jpg';
                }
            } elseif ($this->fallback === 'empty') {
                return self::EMPTY_GIF;
            } else {
                $modifiers['format'] = $this->fallback;
            }
        } elseif ($this->format && $this->format !== 'auto') {
            // If not applying fallback and format is set (and not auto), use it
            $modifiers['format'] = $this->format;
        }

        // Add other modifiers
        if ($this->quality) {
            $modifiers['quality'] = $this->quality;
        }

        if ($this->fit) {
            $modifiers['fit'] = $this->fit;
        }

        if ($this->focal) {
            $modifiers['focal'] = $this->focal;
        }

        if ($this->background) {
            $modifiers['background'] = $this->background;
        }

        if ($this->ratio) {
            $modifiers['ratio'] = $this->ratio;
        }

        if (isset($modifiers['width'])) {
            $modifiers['width'] = (int) $modifiers['width'];
        }

        return $this->providerRegistry->getProvider()->getImage($this->src, $modifiers);
    }

    protected function getImageExtension(): string
    {
        return strtolower(pathinfo($this->src, PATHINFO_EXTENSION));
    }
}
