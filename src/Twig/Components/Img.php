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
    public ?string $alt = null;
    public ?int $width = null;
    public ?int $height = null;
    public ?string $ratio = null;
    public ?string $fit = 'cover';
    public ?string $focal = 'center';
    public ?string $quality = '80';
    public ?string $loading = 'lazy';
    public ?string $fetchpriority = 'auto';
    public ?bool $preload = false;
    public ?string $background = null;
    public ?string $sizes = null;
    public ?string $fallback = 'auto';
    public ?string $class = null;
    public ?string $preset = null;
    public ?string $placeholder = null;
    public ?string $srcset = null;

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
                'sizes',
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
        $resolver->setAllowedTypes('width', 'int');
        $resolver->setAllowedTypes('height', 'int');
        $resolver->setAllowedTypes('ratio', 'string');
        $resolver->setAllowedTypes('fit', 'string');
        $resolver->setAllowedTypes('focal', 'string');
        $resolver->setAllowedTypes('quality', 'string');
        $resolver->setAllowedTypes('loading', 'string');
        $resolver->setAllowedTypes('fetchpriority', 'string');
        $resolver->setAllowedTypes('preload', 'bool');
        $resolver->setAllowedTypes('background', 'string');
        $resolver->setAllowedTypes('sizes', 'string');
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

    public function mount(string $src, ?string $densities = null, ?int $width = null): void
    {
        if (empty($src)) {
            throw new \InvalidArgumentException('Image src cannot be empty');
        }

        $this->src = $src;
        $this->width = $width;
        $this->srcset = $this->getSrcset($densities);
    }

    private function getSrcset(?string $densities = null): string
    {
        $srcset = [];

        if ($densities) {
            $densities = explode(' ', $densities);
            foreach ($densities as $density) {
                $scale = (int) substr($density, 1);
                $width = $this->width * $scale;
                $srcset[] = \sprintf('%s %s%s',
                    $this->getImage(['width' => $width]),
                    $scale,
                    'x'
                );
            }

            return implode(', ', $srcset);
        }

        return '';
    }

    private function getImage(array $modifiers = []): string
    {
        return $this->providerRegistry->getProvider()->getImage($this->src, $modifiers);
    }
}
