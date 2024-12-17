<?php

namespace Ommax\ResponsiveImageBundle\Provider;

class FastlyProvider implements ProviderInterface
{
    private string $baseUrl;
    private array $defaultTransformations;

    /**
     * Map of modifier keys to Fastly parameters
     */
    private const KEY_MAP = [
        'width' => 'width',
        'height' => 'height',
        'format' => 'format',
        'quality' => 'quality',
        'fit' => 'fit',
        'background' => 'bg-color',
        'ratio' => 'aspect-ratio',
    ];

    /**
     * Map of modifier values to Fastly values
     */
    private const VALUE_MAP = [
        'fit' => [
            'fill' => 'crop',
            'inside' => 'crop',
            'outside' => 'crop',
            'cover' => 'bounds',
            'contain' => 'bounds',
        ],
    ];

    public function __construct(array $config = [])
    {
        $this->baseUrl = $config['base_url'];
        $this->defaultTransformations = $config['default_transformations'] ?? [];
    }

    public function getName(): string
    {
        return 'fastly';
    }

    public function getImage(string $src, array $modifiers): string
    {
        // Remove leading slash if present
        $src = ltrim($src, '/');

        // Process modifiers
        $transformations = [];
        foreach ($modifiers as $key => $value) {
            if (!isset(self::KEY_MAP[$key])) {
                continue;
            }

            $fastlyKey = self::KEY_MAP[$key];

            // Handle special value mappings
            if (isset(self::VALUE_MAP[$key]) && isset(self::VALUE_MAP[$key][$value])) {
                $value = self::VALUE_MAP[$key][$value];
            }

            // Handle special cases
            if ($key === 'background') {
                $value = ltrim($value, '#');
            } elseif ($key === 'ratio' && preg_match('/^(\d+):(\d+)$/', $value, $matches)) {
                $value = $matches[1] . '/' . $matches[2];
            }

            $transformations[] = $fastlyKey . '=' . $value;
        }

        // Merge with default transformations
        $transformations = array_merge(
            array_map(fn($t) => "$t[0]=$t[1]", $this->defaultTransformations),
            $transformations
        );

        // Build the final URL
        $queryString = implode('&', $transformations);
        
        return sprintf(
            '%s/%s%s',
            rtrim($this->baseUrl, '/'),
            $src,
            $queryString ? '?' . $queryString : ''
        );
    }
} 