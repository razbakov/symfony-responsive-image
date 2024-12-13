<?php

namespace Ommax\ResponsiveImageBundle\Tests\Service;

use Ommax\ResponsiveImageBundle\Service\Transformer;
use PHPUnit\Framework\TestCase;

class TransformerTest extends TestCase
{
    private Transformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new Transformer([
            // 'xs' => 320, Mobile Portrait BrowserStack
            'sm' => 640,
            'md' => 768,
            'lg' => 1024,
            'xl' => 1280,
            // 1366 Top Common Screen Resolutions Worldwide in 2024
            // 1440 Mobile Portrait BrowserStack
            // 1512 MacBook Pro 14” 2021
            '2xl' => 1536, // Top Common Screen Resolutions Worldwide in 2024
            // '3xl' => 1920, // DELL U2515H, Top Common Screen Resolutions Worldwide in 2024
            // 1600 DELL U2515H
            // 1800 MacBook Pro 14” 2021
            // 2048 DELL U2515H
            // '4xl' => 2560 // DELL U2515H, Tablet Landscape
        ]);
    }

    /**
     * @dataProvider provideWidthStrings
     */
    public function testParseWidth(string $input, array $expected): void
    {
        $result = $this->transformer->parseWidth($input);
        $this->assertEquals($expected, $result);
    }

    public function provideWidthStrings(): array
    {
        return [
            'fixed width' => [
                '300',
                [
                    'default' => ['value' => 300, 'vw' => '0'],
                    'sm' => ['value' => 300, 'vw' => '0'],
                    'md' => ['value' => 300, 'vw' => '0'],
                    'lg' => ['value' => 300, 'vw' => '0'],
                    'xl' => ['value' => 300, 'vw' => '0'],
                    '2xl' => ['value' => 300, 'vw' => '0'],
                ],
            ],
            'fixed width large' => [
                '1000',
                [
                    'default' => ['value' => 1000, 'vw' => '0'],
                    'sm' => ['value' => 1000, 'vw' => '0'],
                    'md' => ['value' => 1000, 'vw' => '0'],
                    'lg' => ['value' => 1000, 'vw' => '0'],
                    'xl' => ['value' => 1000, 'vw' => '0'],
                    '2xl' => ['value' => 1000, 'vw' => '0'],
                ],
            ],
            'fixed breakpoints' => [
                'sm:50 md:100 lg:200',
                [
                    'default' => ['value' => 50, 'vw' => '0'],
                    'sm' => ['value' => 50, 'vw' => '0'],
                    'md' => ['value' => 100, 'vw' => '0'],
                    'lg' => ['value' => 200, 'vw' => '0'],
                    'xl' => ['value' => 200, 'vw' => '0'],
                    '2xl' => ['value' => 200, 'vw' => '0'],
                ],
            ],
            'fullscreen' => [
                '100vw',
                [
                    'default' => ['value' => 640, 'vw' => '100'],
                    'sm' => ['value' => 640, 'vw' => '100'],
                    'md' => ['value' => 768, 'vw' => '100'],
                    'lg' => ['value' => 1024, 'vw' => '100'],
                    'xl' => ['value' => 1280, 'vw' => '100'],
                    '2xl' => ['value' => 1536, 'vw' => '100'],
                ],
            ],
            'halfscreen and fixed' => [
                '50vw lg:400px',
                [
                    'default' => ['value' => 320, 'vw' => '50'],
                    'sm' => ['value' => 320, 'vw' => '50'],
                    'md' => ['value' => 384, 'vw' => '50'],
                    'lg' => ['value' => 400, 'vw' => '0'],
                    'xl' => ['value' => 400, 'vw' => '0'],
                    '2xl' => ['value' => 400, 'vw' => '0'],
                ],
            ],
            'mixed values' => [
                '400 sm:500 md:100vw',
                [
                    'default' => ['value' => 400, 'vw' => '0'],
                    'sm' => ['value' => 500, 'vw' => '0'],
                    'md' => ['value' => 768, 'vw' => '100'],
                    'lg' => ['value' => 1024, 'vw' => '100'],
                    'xl' => ['value' => 1280, 'vw' => '100'],
                    '2xl' => ['value' => 1536, 'vw' => '100'],
                ],
            ],
            'mixed values with gap' => [
                '100 lg:100vw',
                [
                    'default' => ['value' => 100, 'vw' => '0'],
                    'sm' => ['value' => 100, 'vw' => '0'],
                    'md' => ['value' => 100, 'vw' => '0'],
                    'lg' => ['value' => 1024, 'vw' => '100'],
                    'xl' => ['value' => 1280, 'vw' => '100'],
                    '2xl' => ['value' => 1536, 'vw' => '100'],
                ],
            ],
            'vw to fixed width' => [
                '100vw md:100',
                [
                    'default' => ['value' => 640, 'vw' => '100'],
                    'sm' => ['value' => 640, 'vw' => '100'],
                    'md' => ['value' => 100, 'vw' => '0'],
                    'lg' => ['value' => 100, 'vw' => '0'],
                    'xl' => ['value' => 100, 'vw' => '0'],
                    '2xl' => ['value' => 100, 'vw' => '0'],
                ],
            ],
            'large fixed to vw' => [
                '1000 lg:100vw',
                [
                    'default' => ['value' => 1000, 'vw' => '0'],
                    'sm' => ['value' => 1000, 'vw' => '0'],
                    'md' => ['value' => 1000, 'vw' => '0'],
                    'lg' => ['value' => 1024, 'vw' => '100'],
                    'xl' => ['value' => 1280, 'vw' => '100'],
                    '2xl' => ['value' => 1536, 'vw' => '100'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideSizesStrings
     */
    public function testGetSizes(array $widths, string $expected): void
    {
        $result = $this->transformer->getSizes($widths);
        $this->assertEquals($expected, $result);
    }

    public function provideSizesStrings(): array
    {
        return [
            'fullscreen' => [
                [
                    'default' => ['value' => 640, 'vw' => '100'],
                    'sm' => ['value' => 640, 'vw' => '100'],
                    'md' => ['value' => 768, 'vw' => '100'],
                    'lg' => ['value' => 1024, 'vw' => '100'],
                    'xl' => ['value' => 1280, 'vw' => '100'],
                    '2xl' => ['value' => 1536, 'vw' => '100'],
                ],
                '100vw',
            ],
            // Add more test cases for getSizes here
        ];
    }

    /**
     * @dataProvider provideSrcsetData
     */
    public function testGetSrcset(string $src, array $widths, string $expected): void
    {
        $result = $this->transformer->getSrcset(
            $src,
            $widths,
            fn ($modifiers) => $src.'?'.http_build_query($modifiers)
        );
        $this->assertEquals($expected, $result);
    }

    public function provideSrcsetData(): array
    {
        return [
            'basic widths' => [
                '/image.jpg',
                [
                    'default' => ['value' => 300, 'vw' => '0'],
                    'sm' => ['value' => 400, 'vw' => '0'],
                ],
                '/image.jpg?width=300 300w, /image.jpg?width=400 400w',
            ],
            // Add more test cases for srcset here
        ];
    }

    /**
     * @dataProvider provideInitialWidthData
     */
    public function testGetInitialWidth(array $widths, string $pattern, int $expected): void
    {
        $result = $this->transformer->getInitialWidth($widths, $pattern);
        $this->assertEquals($expected, $result);
    }

    public function provideInitialWidthData(): array
    {
        return [
            'viewport width pattern' => [
                [
                    'default' => ['value' => 640, 'vw' => '100'],
                    'sm' => ['value' => 640, 'vw' => '100'],
                    'md' => ['value' => 768, 'vw' => '100'],
                ],
                '100vw',
                640,
            ],
            'fixed width pattern' => [
                [
                    'default' => ['value' => 300, 'vw' => '0'],
                    'sm' => ['value' => 400, 'vw' => '0'],
                ],
                '300',
                300,
            ],
            'mixed pattern starting with fixed' => [
                [
                    'default' => ['value' => 400, 'vw' => '0'],
                    'md' => ['value' => 768, 'vw' => '100'],
                ],
                '400 md:100vw',
                400,
            ],
        ];
    }

    public function testGetDensityBasedWidths(): void
    {
        $transformer = new Transformer();

        $widths = $transformer->getDensityBasedWidths(100, 'x1 x2');
        $this->assertEquals([100, 200], $widths);

        $widths = $transformer->getDensityBasedWidths(100, '1x 2x 3x');
        $this->assertEquals([100, 200, 300], $widths);
    }
}
