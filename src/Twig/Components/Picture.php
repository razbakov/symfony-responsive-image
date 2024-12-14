<?php

namespace Ommax\ResponsiveImageBundle\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\TwigComponent\Attribute\PreMount;

#[AsTwigComponent('picture', template: 'picture.html.twig')]
class Picture
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

    public function __construct(
        private ParameterBagInterface $params
    ) {
        $this->params = $params;
    }

    #[PreMount]
    public function preMount(array $data): array
    {
        $resolver = new OptionsResolver();

        $resolver->setDefaults([
            'alt' => null,
            'width' => null,
            'height' => null,
            'ratio' => null,
            'fit' => 'cover',
            'focal' => 'center',
            'quality' => '80',
            'loading' => 'lazy',
            'fetchpriority' => 'auto',
            'preload' => false,
            'background' => null,
            'sizes' => null,
            'fallback' => 'auto',
            'class' => null,
            'preset' => null,
            'placeholder' => null,
        ]);

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
}
