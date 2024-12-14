<?php

namespace Ommax\ResponsiveImageBundle\Tests\Twig\Components;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\TwigComponent\Test\InteractsWithTwigComponents;
use Ommax\ResponsiveImageBundle\Twig\Components\Img;

class ImgTest extends KernelTestCase
{
    use InteractsWithTwigComponents;

    public function testComponentMount(): void
    {
        $component = $this->mountTwigComponent(
            name: 'img',
            data: [
                'src' => '/image.jpg',
            ]
        );

        $this->assertInstanceOf(Img::class, $component);
        $this->assertSame('/image.jpg', $component->src);
    }

    public function testComponentRendersAlt(): void
    {
        $rendered = $this->renderTwigComponent(
            name: 'img',
            data: [
                'src' => '/image.jpg',
                'alt' => 'Test image',
            ]
        );

        $this->assertStringContainsString('alt="Test image"', $rendered);
    }

    public function testComponentRendersClass(): void
    {
        $rendered = $this->renderTwigComponent(
            name: 'img',
            data: [
                'src' => '/image.jpg',
                'class' => 'img-fluid rounded',
            ]
        );

        $this->assertStringContainsString('class="img-fluid rounded"', $rendered);
    }

    public function testComponentRendersCustomAttributes(): void
    {
        $rendered = $this->renderTwigComponent(
            name: 'img',
            data: [
                'src' => '/image.jpg',
                'referrerpolicy' => 'origin',
                'id' => 'image',
                'data-controller' => 'responsive-image',
            ]
        );

        $this->assertStringContainsString('referrerpolicy="origin"', $rendered);
        $this->assertStringContainsString('id="image"', $rendered);
        $this->assertStringContainsString('data-controller="responsive-image"', $rendered);
    }

    public function testEmptySrcThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Image src cannot be empty');

        $this->mountTwigComponent(
            name: 'img',
            data: [
                'src' => '',
            ]
        );
    }
}
