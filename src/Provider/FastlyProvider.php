<?php

namespace Ommax\ResponsiveImageBundle\Provider;

class FastlyProvider implements ProviderInterface
{
    private string $baseUrl;
    private array $defaults;

    /**
     * Map of modifier keys to Fastly parameters.
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
     * Map of modifier values to Fastly values.
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
        $this->baseUrl = $config['base_url'] ?? '';
        $this->defaults = $config['defaults'] ?? [];
    }

    public function getName(): string
    {
        return 'fastly';
    }

    public function getImage(string $src, array $modifiers): string
    {
        $src = ltrim($src, '/');

        // Merge defaults with provided modifiers
        $modifiers = array_merge($this->defaults, $modifiers);

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
            if ('background' === $key) {
                $value = ltrim($value, '#');
            } elseif ('ratio' === $key && preg_match('/^(\d+):(\d+)$/', $value, $matches)) {
                $value = $matches[1].'/'.$matches[2];
            }

            $transformations[] = $fastlyKey.'='.$value;
        }

        // Build the final URL
        $queryString = implode('&', $transformations);

        return \sprintf(
            '%s/%s%s',
            rtrim($this->baseUrl, '/'),
            $src,
            $queryString ? '?'.$queryString : ''
        );
    }
}
