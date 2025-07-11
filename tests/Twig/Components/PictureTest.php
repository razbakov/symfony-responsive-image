<?php

namespace Razbakov\ResponsiveImageBundle\Tests\Twig\Components;

use Razbakov\ResponsiveImageBundle\Provider\ProviderInterface;
use Razbakov\ResponsiveImageBundle\Provider\ProviderRegistry;
use Razbakov\ResponsiveImageBundle\Twig\Components\Picture;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\TwigComponent\Test\InteractsWithTwigComponents;
use Razbakov\ResponsiveImageBundle\Tests\TestHelper\HtmlTestHelper;

class PictureTest extends KernelTestCase
{
    use InteractsWithTwigComponents;
    use HtmlTestHelper;

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

        $this->assertImageSrcParam($rendered, 'width', '100');
        $this->assertStringContainsString('width="100"', $rendered);
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

        $this->assertImageSrcParam($rendered, 'width', '50');
        $this->assertSourceAttribute($rendered, 'media', '(max-width: 640px)', 0);
        $this->assertSourceAttribute($rendered, 'media', '(max-width: 768px)', 1);
        $this->assertSourceAttribute($rendered, 'media', '(max-width: 1024px)', 2);
        $this->assertSourceAttribute($rendered, 'srcset', '/image.jpg?width=50', 0);
        $this->assertSourceAttribute($rendered, 'srcset', '/image.jpg?width=100', 1);
        $this->assertSourceAttribute($rendered, 'srcset', '/image.jpg?width=200', 2);
    }

    public function testViewportWidthUnit(): void
    {
        $rendered = $this->renderTwigComponent(
            name: 'picture',
            data: [
                'src' => '/image.jpg',
                'width' => '100vw',
            ]
        );

        $this->assertImageSrcParam($rendered, 'width', '640');
        $this->assertStringContainsString('srcset="/image.jpg?width=640 640w"', $rendered);
        $this->assertStringContainsString('srcset="/image.jpg?width=768 768w"', $rendered);
        $this->assertStringContainsString('srcset="/image.jpg?width=1024 1024w"', $rendered);
        $this->assertStringContainsString('srcset="/image.jpg?width=1280 1280w"', $rendered);
        $this->assertStringContainsString('srcset="/image.jpg?width=1536 1536w"', $rendered);
        $this->assertStringContainsString('sizes="100vw"', $rendered);
    }
}
