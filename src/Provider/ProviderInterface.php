<?php

namespace Ommax\ResponsiveImageBundle\Provider;

interface ProviderInterface
{
    /**
     * Get the provider name.
     */
    public function getName(): string;

    /**
     * Generate the URL for the image with given options.
     *
     * @param string $src The source image path
     * @param array $options The transformation options
     * @return string The transformed image URL
     */
    public function generateUrl(string $src, array $options): string;

    /**
     * Check if the provider supports the given source.
     */
    public function supports(string $src): bool;

    /**
     * Get provider-specific configuration options.
     *
     * @return array<string, mixed>
     */
    public function getDefaultOptions(): array;
}
