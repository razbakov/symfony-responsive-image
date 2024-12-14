<?php

namespace Ommax\ResponsiveImageBundle\Provider;

interface ProviderInterface
{
    /**
     * Get the provider name.
     */
    public function getName(): string;

    /**
     * Generates the URL for the image with the specified options.
     *
     * This method takes the source image path and an array of transformation options,
     * and returns the URL of the transformed image. The transformation options can include
     * parameters such as image size, format, quality, and other modifications defined in the
     * image component or as a preset.
     *
     * @param string $src       The path to the source image
     * @param array  $modifiers List of image modifiers that are defined in the image component
     *                          or as a preset.
     *
     * @return string Absolute or relative url of optimized image
     */
    public function getImage(string $src, array $modifiers): string;
}
