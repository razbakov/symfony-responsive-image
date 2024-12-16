<?php

namespace Ommax\ResponsiveImageBundle\Tests\Twig\Components;

use Ommax\ResponsiveImageBundle\Provider\ProviderInterface;
use Ommax\ResponsiveImageBundle\Provider\ProviderRegistry;
use Ommax\ResponsiveImageBundle\Twig\Components\Picture;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\TwigComponent\Test\InteractsWithTwigComponents;

class PictureTest extends KernelTestCase
{
    use InteractsWithTwigComponents;

    /** @var ProviderInterface&MockObject */
    private ProviderInterface $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $registry = static::getContainer()->get(ProviderRegistry::class);

        $this->provider = $this->createMock(ProviderInterface::class);
        $this->provider->method('getName')->willReturn('mock');
        $this->provider
            ->method('getImage')
            ->willReturnCallback(function ($src, $modifiers) {
                return $src.'?'.http_build_query($modifiers);
            });

        $registry->addProvider($this->provider);
        $registry->setDefaultProvider('mock');
    }

    public function testComponentMount(): void
    {
        $component = $this->mountTwigComponent(
            name: 'picture',
            data: [
                'src' => '/image.jpg',
            ]
        );

        $this->assertInstanceOf(Picture::class, $component);
        $this->assertSame('/image.jpg', $component->src);
    }

    public function testEmptySrcThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Image src cannot be empty');

        $this->mountTwigComponent(
            name: 'picture',
            data: [
                'src' => '',
            ]
        );
    }

    public function testComponentRenders(): void
    {
        $rendered = $this->renderTwigComponent(
            name: 'picture',
            data: [
                'src' => '/image.jpg',
                'alt' => 'Test image',
                'class' => 'img-fluid rounded',
                'referrerpolicy' => 'origin',
                'id' => 'image',
                'data-controller' => 'responsive-image',
                'width' => 100,
                'height' => 100,
                'loading' => 'lazy',
                'fetchpriority' => 'auto',
                'sizes' => '(max-width: 768px) 100vw, 50vw',
                'fallback' => 'auto',
                'class' => 'img-fluid rounded',
            ]
        );

        $this->assertStringContainsString('alt="Test image"', $rendered);
        $this->assertStringContainsString('class="img-fluid rounded"', $rendered);
        $this->assertStringContainsString('referrerpolicy="origin"', $rendered);
        $this->assertStringContainsString('id="image"', $rendered);
        $this->assertStringContainsString('data-controller="responsive-image"', $rendered);
        $this->assertStringContainsString('width="100"', $rendered);
        $this->assertStringContainsString('height="100"', $rendered);
    }

    public function testFixedWidth(): void
    {
        $rendered = $this->renderTwigComponent(
            name: 'picture',
            data: [
                'src' => '/image.jpg',
                'width' => '100',
            ]
        );

        $this->assertStringContainsString('width="100"', $rendered);
        $this->assertStringContainsString('src="/image.jpg?width=100"', $rendered);
    }

    public function testResponsiveWidth(): void
    {
        $rendered = $this->renderTwigComponent(
            name: 'picture',
            data: [
                'src' => '/image.jpg',
                'width' => 'sm:50 md:100 lg:200',
            ]
        );

        $this->assertStringContainsString('src="/image.jpg?width=50"', $rendered);
        $this->assertStringContainsString('media="(min-width: 768px)"', $rendered);
        $this->assertStringContainsString('media="(min-width: 1024px)"', $rendered);
        $this->assertStringContainsString('srcset="/image.jpg?width=100"', $rendered);
        $this->assertStringContainsString('srcset="/image.jpg?width=200"', $rendered);
    }
}
