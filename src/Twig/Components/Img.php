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
        
        if ($this->width) {
            $this->widths = $this->transformer->getSizes($this->width);
            $this->widthComputed = $this->widths['default']['value'];
            $this->srcComputed = $this->getImage(['width' => $this->widthComputed]);
        } else {
            $this->srcComputed = $this->getImage();
        }

        $this->srcset = $this->getSrcset();
    }

    private function getSrcset(): string
    {
        if (!$this->width) {
            return '';
        }

        $srcset = [];

        foreach ($this->widths as $width) {
            $srcset[] = sprintf('%s %sw',
                $this->getImage(['width' => $width['value']]),
                $width['value']
            );
        }

        return implode(', ', $srcset);
    }

    private function getImage(array $modifiers = []): string
    {
        return $this->providerRegistry->getProvider()->getImage($this->src, $modifiers);
    }
}
