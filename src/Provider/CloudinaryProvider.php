<?php

namespace Ommax\ResponsiveImageBundle\Provider;

class CloudinaryProvider implements ProviderInterface
{
    private string $baseUrl;
    private array $defaultTransformations;

    /**
     * Map of modifier keys to Cloudinary parameters
     */
    private const KEY_MAP = [
        'width' => 'w',
        'height' => 'h',
        'format' => 'f',
        'quality' => 'q',
        'fit' => 'c',
        'focal' => 'g',
        'background' => 'b',
        'ratio' => 'ar',
    ];

    /**
     * Map of modifier values to Cloudinary values
     */
    private const VALUE_MAP = [
        'fit' => [
            'cover' => 'fill',
            'contain' => 'fit',
            'fill' => 'scale',
            'inside' => 'limit',
            'outside' => 'mfit',
        ],
        'focal' => [
            'center' => 'center',
            'top' => 'north',
            'bottom' => 'south',
            'left' => 'west',
            'right' => 'east',
        ],
    ];

    public function __construct(array $config = [])
    {
        $this->baseUrl = $config['base_url'];
        $this->defaultTransformations = $config['default_transformations'];
    }

    public function getName(): string
    {
        return 'cloudinary';
    }

    public function getImage(string $src, array $modifiers): string
    {
        // Remove leading slash if present
        $src = ltrim($src, '/');

        // Start building the transformation string
        $transformations = [];

        // Process each modifier
        foreach ($modifiers as $key => $value) {
            if (!isset(self::KEY_MAP[$key])) {
                continue;
            }

            $cloudinaryKey = self::KEY_MAP[$key];

            // Handle special value mappings
            if (isset(self::VALUE_MAP[$key])) {
                if (isset(self::VALUE_MAP[$key][$value])) {
                    $value = self::VALUE_MAP[$key][$value];
                } elseif ($key === 'focal' && preg_match('/^(\d*\.?\d+),(\d*\.?\d+)$/', $value, $matches)) {
                    // Handle custom focal points like "0.5,0.3"
                    $x = round($matches[1] * 100);
                    $y = round($matches[2] * 100);
                    $value = $x.'_'.$y;
                }
            }

            // Handle special cases
            if ($key === 'background') {
                $value = 'rgb:'.ltrim($value, '#');
            }

            $transformations[] = $cloudinaryKey.'_'.$value;
        }

        // Merge with default transformations
        $transformations = array_merge($this->defaultTransformations, $transformations);

        // Build the final URL
        $transformationString = implode(',', $transformations);
        
        return sprintf(
            '%s/%s/%s',
            $this->baseUrl,
            $transformationString ? $transformationString.'/' : '',
            $src
        );
    }
}
