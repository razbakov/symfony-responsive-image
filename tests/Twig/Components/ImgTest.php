<?php

namespace Ommax\ResponsiveImageBundle\Tests\Twig\Components;

use Ommax\ResponsiveImageBundle\Provider\ProviderInterface;
use Ommax\ResponsiveImageBundle\Provider\ProviderRegistry;
use Ommax\ResponsiveImageBundle\Service\PreloadManager;
use Ommax\ResponsiveImageBundle\Twig\Components\Img;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\TwigComponent\Test\InteractsWithTwigComponents;

class ImgTest extends KernelTestCase
{
    use InteractsWithTwigComponents;

    /** @var ProviderInterface&MockObject */
    private ProviderInterface $provider;

    /** @var ProviderInterface&MockObject */
    private ProviderInterface $customProvider;

    private PreloadManager $preloadManager;

    private ProviderRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();

        $container = static::getContainer();
        $this->registry = $container->get(ProviderRegistry::class);
        $this->preloadManager = $container->get(PreloadManager::class);
        $this->preloadManager->reset();

        // Setup default mock provider
        $this->provider = $this->createMock(ProviderInterface::class);
        $this->provider->method('getName')->willReturn('mock');
        $this->provider
            ->method('getImage')
            ->willReturnCallback(function ($src, $modifiers) {
                return $src.'?'.http_build_query($modifiers);
            });

        $this->registry->addProvider($this->provider);
        $this->registry->setDefaultProvider('mock');
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
                'format' => 'webp',
                'quality' => '80',
                'fit' => 'cover',
                'focal' => 'center',
            ]
        );

        $this->assertStringContainsString('alt="Test image"', $rendered);
        $this->assertStringContainsString('class="img-fluid rounded"', $rendered);
        $this->assertStringContainsString('referrerpolicy="origin"', $rendered);
        $this->assertStringContainsString('id="image"', $rendered);
        $this->assertStringContainsString('data-controller="responsive-image"', $rendered);
        $this->assertStringContainsString('width="100"', $rendered);
        $this->assertStringContainsString('height="100"', $rendered);
        $this->assertStringContainsString('loading="lazy"', $rendered);
        $this->assertStringContainsString('fetchpriority="auto"', $rendered);
    }

    public function testPresetConfiguration(): void
    {
        $component = $this->mountTwigComponent(
            name: 'img',
            data: [
                'src' => '/image.jpg',
                'preset' => 'hero',
            ]
        );

        $this->assertStringContainsString('high', $component->fetchpriority);
        $this->assertStringContainsString('16:9', $component->ratio);
        $this->assertStringContainsString('100vw sm:50vw md:400px', $component->width);
        $this->assertTrue($component->preload);
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
        $this->assertStringNotContainsString('sizes="', $rendered);
        $this->assertStringNotContainsString('srcset="', $rendered);
    }

    public function testFixedWidthLarge(): void
    {
        $rendered = $this->renderTwigComponent(
            name: 'img',
            data: [
                'src' => '/image.jpg',
                'width' => '1000',
            ]
        );

        $this->assertStringContainsString('src="/image.jpg?width=1000"', $rendered);
        $this->assertStringNotContainsString('sizes="', $rendered);
        $this->assertStringNotContainsString('srcset="', $rendered);
    }

    public function testFixedWidthBreakpoints(): void
    {
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
        $this->assertStringContainsString('(max-width: 640px) 50px', $rendered);
        $this->assertStringContainsString('(max-width: 768px) 100px', $rendered);
        $this->assertStringContainsString('(max-width: 1024px) 200px', $rendered);
        $this->assertStringContainsString('200px', $rendered);
    }

    public function testFullscreen(): void
    {
        $rendered = $this->renderTwigComponent(
            name: 'img',
            data: [
                'src' => '/image.jpg',
                'width' => '100vw',
            ]
        );

        $this->assertStringContainsString('src="/image.jpg?width=640"', $rendered);
        $this->assertStringContainsString('/image.jpg?width=640 640w', $rendered);
        $this->assertStringContainsString('/image.jpg?width=768 768w', $rendered);
        $this->assertStringContainsString('/image.jpg?width=1024 1024w', $rendered);
        $this->assertStringContainsString('/image.jpg?width=1280 1280w', $rendered);
        $this->assertStringContainsString('/image.jpg?width=1536 1536w', $rendered);
        $this->assertStringContainsString('sizes="100vw', $rendered);
    }

    public function testHalfscreenAndFixed(): void
    {
        $rendered = $this->renderTwigComponent(
            name: 'img',
            data: [
                'src' => '/image.jpg',
                'width' => '50vw lg:400px',
            ]
        );

        $this->assertStringContainsString('src="/image.jpg?width=320"', $rendered);
        $this->assertStringContainsString('/image.jpg?width=320 320w', $rendered);
        $this->assertStringContainsString('/image.jpg?width=400 400w', $rendered);
        $this->assertStringContainsString('(max-width: 1024px) 50vw', $rendered);
        $this->assertStringContainsString('400px', $rendered);
    }

    public function testMixedValues(): void
    {
        $rendered = $this->renderTwigComponent(
            name: 'img',
            data: [
                'src' => '/image.jpg',
                'width' => '400 sm:500 md:100vw',
            ]
        );

        $this->assertStringContainsString('src="/image.jpg?width=400"', $rendered);
        $this->assertStringContainsString('/image.jpg?width=400 400w', $rendered);
        $this->assertStringContainsString('/image.jpg?width=500 500w', $rendered);
        $this->assertStringContainsString('/image.jpg?width=768 768w', $rendered);
        $this->assertStringContainsString('/image.jpg?width=1024 1024w', $rendered);
        $this->assertStringContainsString('/image.jpg?width=1280 1280w', $rendered);
        $this->assertStringContainsString('/image.jpg?width=1536 1536w', $rendered);
        $this->assertStringContainsString('(max-width: 640px) 400px', $rendered);
        $this->assertStringContainsString('(max-width: 768px) 500px', $rendered);
        $this->assertStringContainsString('100vw', $rendered);
    }

    public function testDensities(): void
    {
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

    public function testPreloadSimpleImage(): void
    {
        // First verify the PreloadManager service exists
        $preloadManager = static::getContainer()->get(PreloadManager::class);
        $this->assertInstanceOf(PreloadManager::class, $preloadManager);

        // Reset the PreloadManager
        $preloadManager->reset();

        // Mount component with preload=true
        $component = $this->mountTwigComponent(
            name: 'img',
            data: [
                'src' => '/image.jpg',
                'width' => '400',
                'preload' => true,
            ]
        );

        $this->assertTrue($component->preload, 'Preload flag should be true');
        $this->assertEquals('/image.jpg?width=400', $component->srcComputed, 'Computed src should match expected');

        $preloadTags = $preloadManager->getPreloadTags();

        $this->assertStringContainsString(
            '<link rel="preload" as="image" href="/image.jpg?width=400">',
            $preloadTags,
            'Preload tags should contain the expected link tag'
        );
    }

    public function testPreloadResponsiveImage(): void
    {
        $this->mountTwigComponent(
            name: 'img',
            data: [
                'src' => '/image.jpg',
                'width' => '100vw',
                'preload' => true,
            ]
        );

        $preloadManager = static::getContainer()->get(PreloadManager::class);
        $preloadTags = $preloadManager->getPreloadTags();

        $this->assertStringContainsString('imagesrcset="', $preloadTags);
        $this->assertStringContainsString('sizes="100vw"', $preloadTags);
        $this->assertStringContainsString('/image.jpg?width=640 640w', $preloadTags);
        $this->assertStringContainsString('/image.jpg?width=1536 1536w', $preloadTags);
    }

    public function testPreloadDisabledByDefault(): void
    {
        $this->mountTwigComponent(
            name: 'img',
            data: [
                'src' => '/image.jpg',
                'width' => '400',
            ]
        );

        $preloadManager = static::getContainer()->get(PreloadManager::class);
        $preloadTags = $preloadManager->getPreloadTags();

        $this->assertEmpty($preloadTags);
    }

    public function testFormatParameter(): void
    {
        $rendered = $this->renderTwigComponent(
            name: 'img',
            data: [
                'src' => '/image.jpg',
                'width' => '400',
                'format' => 'avif',
            ]
        );

        $this->assertStringContainsString('src="/image.jpg?width=400&amp;format=avif"', $rendered);
    }

    public function testQualityParameter(): void
    {
        $rendered = $this->renderTwigComponent(
            name: 'img',
            data: [
                'src' => '/image.jpg',
                'width' => '400',
                'quality' => '90',
            ]
        );

        $this->assertStringContainsString('src="/image.jpg?width=400&amp;quality=90"', $rendered);
    }

    public function testFitParameter(): void
    {
        $rendered = $this->renderTwigComponent(
            name: 'img',
            data: [
                'src' => '/image.jpg',
                'width' => '400',
                'fit' => 'contain',
            ]
        );

        $this->assertStringContainsString('src="/image.jpg?width=400&amp;fit=contain"', $rendered);
    }

    public function testBackgroundParameter(): void
    {
        $rendered = $this->renderTwigComponent(
            name: 'img',
            data: [
                'src' => '/image.jpg',
                'width' => '400',
                'background' => '#ffffff',
            ]
        );

        $this->assertStringContainsString('src="/image.jpg?width=400&amp;background=%23ffffff"', $rendered);
    }

    public function testFocalParameter(): void
    {
        $rendered = $this->renderTwigComponent(
            name: 'img',
            data: [
                'src' => '/image.jpg',
                'width' => '400',
                'focal' => 'top',
            ]
        );

        $this->assertStringContainsString('src="/image.jpg?width=400&amp;focal=top"', $rendered);
    }

    public function testFallbackParameter(): void
    {
        $component = $this->mountTwigComponent(
            name: 'img',
            data: [
                'src' => '/image.jpg',
                'width' => '400',
                'format' => 'webp',
                'fallback' => 'jpg',
            ]
        );

        $this->assertEquals('/image.jpg?width=400&format=jpg', $component->srcComputed);
    }

    public function testDefaultFallbackParameter(): void
    {
        $component = $this->mountTwigComponent(
            name: 'img',
            data: [
                'src' => '/image.webp',
                'width' => '400',
                'format' => 'webp',
                'fallback' => 'auto',
            ]
        );

        $this->assertEquals('/image.webp?width=400&format=png', $component->srcComputed);
    }

    public function testEmptyFallbackParameter(): void
    {
        $component = $this->mountTwigComponent(
            name: 'img',
            data: [
                'src' => '/image.jpg',
                'width' => '400',
                'format' => 'webp',
                'fallback' => 'empty',
            ]
        );

        $this->assertEquals(Img::EMPTY_GIF, $component->srcComputed);
    }

    public function testRatioParameter(): void
    {
        $rendered = $this->renderTwigComponent(
            name: 'img',
            data: [
                'src' => '/image.jpg',
                'width' => '400',
                'ratio' => '16:9',
            ]
        );

        $this->assertStringContainsString('src="/image.jpg?width=400&amp;ratio=16%3A9"', $rendered);
    }

    public function testCustomProvider(): void
    {
        $this->customProvider = $this->createMock(ProviderInterface::class);
        $this->customProvider->method('getName')->willReturn('custom');
        $this->customProvider
            ->method('getImage')
            ->willReturnCallback(function ($src, $modifiers) {
                return 'custom://'.$src.'?'.http_build_query($modifiers);
            });

        $this->registry->addProvider($this->customProvider);

        $rendered = $this->renderTwigComponent(
            name: 'img',
            data: [
                'src' => '/image.jpg',
                'width' => '400',
                'provider' => 'custom',
            ]
        );

        $this->assertStringContainsString('src="custom:///image.jpg?width=400"', $rendered);
    }
}
