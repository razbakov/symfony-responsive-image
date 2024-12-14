<?php

namespace Ommax\ResponsiveImageBundle\Tests\Twig\Components;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\TwigComponent\Test\InteractsWithTwigComponents;
use Ommax\ResponsiveImageBundle\Twig\Components\Picture;

class PictureTest extends KernelTestCase
{
    use InteractsWithTwigComponents;

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
}
