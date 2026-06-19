<?php

namespace Razbakov\ResponsiveImageBundle\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Razbakov\ResponsiveImageBundle\Provider\CloudinaryProvider;

class CloudinaryProviderTest extends TestCase
{
    private CloudinaryProvider $provider;

    protected function setUp(): void
    {
        $this->provider = new CloudinaryProvider();
        $this->provider->configure([
            'base_url' => 'https://res.cloudinary.com/demo',
            'defaults' => [
                'quality' => '80',
                'format' => 'jpeg',
            ],
        ]);
    }

    public function testGetName(): void
    {
        $this->assertEquals('cloudinary', $this->provider->getName());
    }

    public function testBasicImageTransformation(): void
    {
        $result = $this->provider->getImage('sample.jpg', [
            'width' => '300',
            'height' => '200',
        ]);

        $this->assertEquals(
            'https://res.cloudinary.com/demo/w_300,h_200,q_80,f_jpg/sample.jpg',
            $result
        );
    }

    public function testFitModifierMapping(): void
    {
        $result = $this->provider->getImage('sample.jpg', [
            'width' => '300',
            'fit' => 'cover',
        ]);

        $this->assertEquals(
            'https://res.cloudinary.com/demo/w_300,q_80,f_jpg,c_lfill/sample.jpg',
            $result
        );
    }

    public function testGravityModifierMapping(): void
    {
        $result = $this->provider->getImage('sample.jpg', [
            'gravity' => 'face',
        ]);

        $this->assertEquals(
            'https://res.cloudinary.com/demo/q_80,f_jpg,g_face/sample.jpg',
            $result
        );
    }

    public function testColorConversion(): void
    {
        $result = $this->provider->getImage('sample.jpg', [
            'background' => '#ff0000',
        ]);

        $this->assertEquals(
            'https://res.cloudinary.com/demo/q_80,f_jpg,b_rgb_ff0000/sample.jpg',
            $result
        );
    }

    public function testRoundCornerModifier(): void
    {
        $result = $this->provider->getImage('sample.jpg', [
            'roundCorner' => '20:40',
        ]);

        $this->assertEquals(
            'https://res.cloudinary.com/demo/q_80,f_jpg,r_20_40/sample.jpg',
            $result
        );
    }

    public function testBlurEffect(): void
    {
        $result = $this->provider->getImage('sample.jpg', [
            'blur' => '500',
        ]);

        $this->assertEquals(
            'https://res.cloudinary.com/demo/q_80,f_jpg,e_blur:500/sample.jpg',
            $result
        );
    }

    public function testIgnoresUnsupportedModifiers(): void
    {
        $result = $this->provider->getImage('sample.jpg', [
            'width' => '300',
            'unsupported' => 'value',
        ]);

        $this->assertEquals(
            'https://res.cloudinary.com/demo/w_300,q_80,f_jpg/sample.jpg',
            $result
        );
    }

    public function testHandlesLeadingSlashInSource(): void
    {
        $result = $this->provider->getImage('/sample.jpg', [
            'width' => '300',
        ]);

        $this->assertEquals(
            'https://res.cloudinary.com/demo/w_300,q_80,f_jpg/sample.jpg',
            $result
        );
    }

    public function testExternalCloudinaryUrlDoesNotMutateConfiguredBaseUrl(): void
    {
        $externalResult = $this->provider->getImage('https://res.cloudinary.com/other/upload/sample.jpg', [
            'width' => '300',
        ]);

        $localResult = $this->provider->getImage('sample.jpg', [
            'width' => '300',
        ]);

        $this->assertEquals(
            'https://res.cloudinary.com/other/upload/w_300,q_80,f_jpg/sample.jpg',
            $externalResult
        );
        $this->assertEquals(
            'https://res.cloudinary.com/demo/w_300,q_80,f_jpg/sample.jpg',
            $localResult
        );
    }

    public function testThrowsExceptionWhenBaseUrlIsMissingForRelativeSource(): void
    {
        $provider = new CloudinaryProvider();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('base_url');

        $provider->getImage('sample.jpg', []);
    }

    public function testThrowsExceptionForMalformedCloudinaryUrl(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('/upload/');

        $this->provider->getImage('https://res.cloudinary.com/demo/sample.jpg', []);
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
