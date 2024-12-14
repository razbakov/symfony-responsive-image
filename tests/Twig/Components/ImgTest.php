<?php

namespace Ommax\ResponsiveImageBundle\Tests\Twig\Components;

use PHPUnit\Framework\TestCase;
use Ommax\ResponsiveImageBundle\Twig\Components\Img;

class ImgTest extends TestCase
{
    private Img $component;

    protected function setUp(): void
    {
        $this->component = new Img(
            src: '/images/test.jpg',
            alt: 'Test image'
        );
    }

    public function testBasicRender(): void
    {
        $this->component->mount();

        $this->assertEquals('/images/test.jpg', $this->component->src);
        $this->assertEquals('Test image', $this->component->alt);
        $this->assertEquals('cover', $this->component->fit);
        $this->assertEquals('lazy', $this->component->loading);
        $this->assertEquals('auto', $this->component->fetchpriority);
    }

    public function testThrowsExceptionWithoutSrc(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $component = new Img(src: '');
        $component->mount();
    }

    public function testPresetConfiguration(): void
    {
        $component = new Img(
            src: '/images/test.jpg',
            preset: 'thumbnail'
        );

        $component->mount();

        $this->assertEquals('thumbnail', $component->preset);
    }

    public function testResponsiveAttributes(): void
    {
        $component = new Img(
            src: '/images/test.jpg',
            width: '800',
            height: '600',
            sizes: '100vw'
        );

        $component->mount();

        $this->assertEquals('800', $component->width);
        $this->assertEquals('600', $component->height);
        $this->assertEquals('100vw', $component->sizes);
    }

    public function testPlaceholderConfiguration(): void
    {
        $component = new Img(
            src: '/images/test.jpg',
            placeholder: 'blur'
        );

        $component->mount();

        $this->assertEquals('blur', $component->placeholder);
    }
}
