<?php

namespace Ommax\ResponsiveImageBundle\Tests\Twig\Components;

use Ommax\ResponsiveImageBundle\Provider\ProviderInterface;
use Ommax\ResponsiveImageBundle\Provider\ProviderRegistry;
use Ommax\ResponsiveImageBundle\Twig\Components\Img;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\TwigComponent\Test\InteractsWithTwigComponents;

class ImgTest extends KernelTestCase
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
            name: 'img',
            data: [
                'src' => '/image.jpg',
            ]
        );

        $this->assertInstanceOf(Img::class, $component);
        $this->assertSame('/image.jpg', $component->src);
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

    public function testComponentRenders(): void
    {
        $rendered = $this->renderTwigComponent(
            name: 'img',
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

    public function testPresetConfiguration(): void
    {
        $rendered = $this->renderTwigComponent(
            name: 'img',
            data: [
                'src' => '/image.jpg',
                'preset' => 'hero',
            ]
        );

        // $this->assertStringContainsString('sizes="100vw sm:50vw md:400px"', $rendered);
        $this->assertStringContainsString('fetchpriority="high"', $rendered);
    }

    public function testPlaceholderRendering(): void
    {
        $rendered = $this->renderTwigComponent(
            name: 'img',
            data: [
                'src' => '/image.jpg',
                'placeholder' => 'blur',
                'placeholder-class' => 'custom-placeholder',
            ]
        );

        $this->assertStringContainsString('class="custom-placeholder"', $rendered);
    }

    public function testFixedWidth(): void
    {
        $rendered = $this->renderTwigComponent(
            name: 'img',
            data: [
                'src' => '/image.jpg',
                'width' => '100',
            ]
        );

        $this->assertStringContainsString('src="/image.jpg?width=100"', $rendered);
    }

    public function testFixedWidthPx(): void
    {
        $rendered = $this->renderTwigComponent(
            name: 'img',
            data: [
                'src' => '/image.jpg',
                'width' => '100px',
            ]
        );

        $this->assertStringContainsString('src="/image.jpg?width=100"', $rendered);
    }

    public function testFixedWidthSequence(): void
    {
        $this->markTestIncomplete("Not implemented");

        $rendered = $this->renderTwigComponent(
            name: 'img',
            data: [
                'src' => '/image.jpg',
                'width' => '50 100 200',
            ]
        );

        $this->assertStringContainsString('src="/image.jpg?width=50"', $rendered);
        $this->assertStringContainsString('/image.jpg?width=50 50w', $rendered);
        $this->assertStringContainsString('/image.jpg?width=100 100w', $rendered);
        $this->assertStringContainsString('/image.jpg?width=200 200w', $rendered);
    }

    public function testFixedWidthBreakpoints(): void
    {
        $this->markTestIncomplete("Not implemented");

        $rendered = $this->renderTwigComponent(
            name: 'img',
            data: [
                'src' => '/image.jpg',
                'width' => 'sm:50 md:100 lg:200',
            ]
        );

        $this->assertStringContainsString('src="/image.jpg?width=50"', $rendered);
        $this->assertStringContainsString('/image.jpg?width=50 50w', $rendered);
        $this->assertStringContainsString('/image.jpg?width=100 100w', $rendered);
        $this->assertStringContainsString('/image.jpg?width=200 200w', $rendered);
    }

    public function testFixedWidthLarge(): void
    {
        $this->markTestIncomplete("Not implemented");

        $rendered = $this->renderTwigComponent(
            name: 'img',
            data: [
                'src' => '/image.jpg',
                'width' => '1000',
            ]
        );

        $this->assertStringContainsString('src="/image.jpg?width=640"', $rendered);
        $this->assertStringContainsString('/image.jpg?width=640 640w', $rendered);
        $this->assertStringContainsString('/image.jpg?width=768 768w', $rendered);
        $this->assertStringContainsString('/image.jpg?width=1000 1000w', $rendered);
    }

    public function testDensities(): void
    {
        $this->markTestIncomplete("Not implemented");

        $rendered = $this->renderTwigComponent(
            name: 'img',
            data: [
                'src' => '/image.jpg',
                'width' => 100,
                'densities' => 'x1 x2',
            ]
        );

        $this->assertStringContainsString('src="/image.jpg?width=100"', $rendered);
        $this->assertStringContainsString('/image.jpg?width=100 100w', $rendered);
        $this->assertStringContainsString('/image.jpg?width=200 200w', $rendered);
    }
}
