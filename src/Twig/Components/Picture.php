<?php

namespace Ommax\ResponsiveImageBundle\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('picture', template: '@ResponsiveImage/components/picture.html.twig')]
class Picture extends Img
{
}
