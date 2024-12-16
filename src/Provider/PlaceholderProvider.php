<?php

namespace Ommax\ResponsiveImageBundle\Provider;

class PlaceholderProvider implements ProviderInterface
{
    private array $config;

    public function configure(array $config): void
    {
        $this->config = $config;
    }

    public function getName(): string
    {
        return 'placeholder';
    }

    public function getImage(string $src, array $modifiers): string
    {
        $width = $modifiers['width'] ?? 600;
        $height = $modifiers['height'] ?? $width;
        $background = $modifiers['background'] ?? '868e96';
        $text = $modifiers['text'] ?? "{$width}x{$height}";
        $textColor = $modifiers['text_color'] ?? 'FFFFFF';

        // Remove '#' from hex colors if present
        $background = ltrim($background, '#');
        $textColor = ltrim($textColor, '#');

        return sprintf(
            'https://placehold.co/%dx%d/%s/%s?text=%s',
            $width,
            $height,
            $background,
            $textColor,
            urlencode($text)
        );
    }
}
