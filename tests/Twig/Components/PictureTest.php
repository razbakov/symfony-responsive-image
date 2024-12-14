<?php

namespace Ommax\ResponsiveImageBundle\Tests\Twig\Components;

use PHPUnit\Framework\TestCase;
use Ommax\ResponsiveImageBundle\Twig\Components\Picture;

class PictureTest extends TestCase
{
    private Picture $component;

    protected function setUp(): void
    {
        $this->component = new Picture(
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
    }

    public function testThrowsExceptionWithoutSrc(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $component = new Picture(src: '');
        $component->mount();
    }

    public function testBreakpointConfiguration(): void
    {
        $component = new Picture(
            src: '/images/test.jpg',
            ratio: 'sm:1:1 md:16:9',
            sizes: 'sm:100vw md:80vw'
        );

        $component->mount();

        $this->assertEquals('sm:1:1 md:16:9', $component->ratio);
        $this->assertEquals('sm:100vw md:80vw', $component->sizes);
    }

    public function testArtDirectionConfiguration(): void
    {
        $component = new Picture(
            src: '/images/test.jpg',
            fit: 'sm:cover md:contain',
            focal: 'sm:center md:0.5,0.3'
        );

        $component->mount();

        $this->assertEquals('sm:cover md:contain', $component->fit);
        $this->assertEquals('sm:center md:0.5,0.3', $component->focal);
    }

    public function testPresetConfiguration(): void
    {
        $component = new Picture(
            src: '/images/test.jpg',
            preset: 'hero'
        );

        $component->mount();

        $this->assertEquals('hero', $component->preset);
    }
}
