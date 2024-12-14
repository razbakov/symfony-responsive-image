<?php

namespace Ommax\ResponsiveImageBundle\Twig\Components;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PreMount;

#[AsTwigComponent('img', template: '@ResponsiveImage/components/img.html.twig')]
class Img
{
    public string $src;
    public ?string $alt = null;
    public ?string $width = null;
    public ?string $height = null;
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

    public function __construct(
        private ParameterBagInterface $params,
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
            ])
            ->setIgnoreUndefined(true);

        $resolver->setRequired('src');

        $resolver->setAllowedTypes('src', 'string');
        $resolver->setAllowedTypes('alt', ['string', 'null']);
        $resolver->setAllowedTypes('width', ['string', 'null']);
        $resolver->setAllowedTypes('height', ['string', 'null']);
        $resolver->setAllowedTypes('ratio', ['string', 'null']);
        $resolver->setAllowedTypes('fit', ['string', 'null']);
        $resolver->setAllowedTypes('focal', ['string', 'null']);
        $resolver->setAllowedTypes('quality', ['string', 'null']);
        $resolver->setAllowedTypes('loading', ['string', 'null']);
        $resolver->setAllowedTypes('fetchpriority', ['string', 'null']);
        $resolver->setAllowedTypes('preload', ['bool', 'null']);
        $resolver->setAllowedTypes('background', ['string', 'null']);
        $resolver->setAllowedTypes('sizes', ['string', 'null']);
        $resolver->setAllowedTypes('fallback', ['string', 'null']);
        $resolver->setAllowedTypes('class', ['string', 'null']);
        $resolver->setAllowedTypes('preset', ['string', 'null']);
        $resolver->setAllowedTypes('placeholder', ['string', 'null']);

        return $resolver->resolve($data) + $data;
    }

    public function mount(string $src): void
    {
        if (empty($src)) {
            throw new \InvalidArgumentException('Image src cannot be empty');
        }

        $this->src = $src;
    }
}
