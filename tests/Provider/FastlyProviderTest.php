<?php

namespace Razbakov\ResponsiveImageBundle\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Razbakov\ResponsiveImageBundle\Provider\FastlyProvider;

class FastlyProviderTest extends TestCase
{
    private FastlyProvider $provider;

    protected function setUp(): void
    {
        $this->provider = new FastlyProvider();
        $this->provider->configure([
            'base_url' => 'https://example.global.ssl.fastly.net',
            'defaults' => [
                'quality' => '80',
                'format' => 'webp',
            ],
        ]);
    }

    public function testGetName(): void
    {
        $this->assertEquals('fastly', $this->provider->getName());
    }

    public function testBuildsTransformedImageUrl(): void
    {
        $result = $this->provider->getImage('/sample.jpg', [
            'width' => '300',
            'height' => '200',
            'fit' => 'cover',
        ]);

        $this->assertEquals(
            'https://example.global.ssl.fastly.net/sample.jpg?quality=80&format=webp&width=300&height=200&fit=bounds',
            $result
        );
    }

    public function testConvertsBackgroundAndRatioValues(): void
    {
        $result = $this->provider->getImage('sample.jpg', [
            'background' => '#ffffff',
            'ratio' => '16:9',
        ]);

        $this->assertEquals(
            'https://example.global.ssl.fastly.net/sample.jpg?quality=80&format=webp&bg-color=ffffff&aspect-ratio=16%2F9',
            $result
        );
    }

    public function testThrowsExceptionWhenBaseUrlIsMissing(): void
    {
        $provider = new FastlyProvider();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('base_url');

        $provider->configure([]);
    }

    public function testThrowsExceptionForInvalidBaseUrl(): void
    {
        $provider = new FastlyProvider();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('valid URL');

        $provider->configure(['base_url' => 'not-a-url']);
    }

    public function testThrowsExceptionForEmptySource(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('empty');

        $this->provider->getImage('', []);
    }

    public function testThrowsExceptionForDirectoryTraversalSource(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('directory traversal');

        $this->provider->getImage('../secret.jpg', []);
    }

    public function testThrowsExceptionForNonScalarModifier(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Modifier "width"');

        $this->provider->getImage('sample.jpg', [
            'width' => ['300'],
        ]);
    }
}
