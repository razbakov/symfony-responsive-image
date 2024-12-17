<?php

namespace Ommax\ResponsiveImageBundle\Provider;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;

class LiipImagineProvider implements ProviderInterface
{
    private CacheManager $cacheManager;
    private string $defaultFilter = 'default';
    private array $defaults = [];

    /**
     * Map of modifier keys to Liip filter settings.
     */
    private const KEY_MAP = [
        'width' => 'size',
        'height' => 'size',
        'quality' => 'quality',
        'fit' => 'mode',
        'background' => 'background',
        'format' => 'format',
        'ratio' => 'ratio',
    ];

    /**
     * Map of modifier values to Liip values.
     */
    private const VALUE_MAP = [
        'fit' => [
            'fill' => 'outbound',
            'inside' => 'inset',
            'outside' => 'outbound',
            'cover' => 'outbound',
            'contain' => 'inset',
        ],
        'format' => [
            'jpeg' => 'jpg',
            'auto' => 'jpg',
        ],
    ];

    public function __construct(
        CacheManager $cacheManager,
    ) {
        $this->cacheManager = $cacheManager;
    }

    public function configure(array $config): void
    {
        $this->defaultFilter = $config['default_filter'] ?? 'default';
        $this->defaults = $config['defaults'] ?? [];
    }

    public function getName(): string
    {
        return 'liip_imagine';
    }

    public function getImage(string $src, array $modifiers): string
    {
        $src = ltrim($src, '/');
        $filterName = $this->defaultFilter;

        // Create runtime config based on modifiers
        $runtimeConfig = [];

        // Merge defaults with provided modifiers
        $modifiers = array_merge($this->defaults, $modifiers);

        // Convert modifiers to Liip filter configuration
        foreach ($modifiers as $key => $value) {
            if (!isset(self::KEY_MAP[$key])) {
                continue;
            }

            $liipKey = self::KEY_MAP[$key];

            // Handle special cases
            if ('width' === $key || 'height' === $key) {
                $runtimeConfig['size'] = $runtimeConfig['size'] ?? [
                    'width' => null,
                    'height' => null,
                ];
                $runtimeConfig['size'][$key] = $value;
                continue;
            }

            // Handle value mappings
            if (isset(self::VALUE_MAP[$key][$value])) {
                $value = self::VALUE_MAP[$key][$value];
            }

            // Handle ratio parsing
            if ('ratio' === $key && preg_match('/^(\d+):(\d+)$/', $value, $matches)) {
                $value = [
                    'width' => (int) $matches[1],
                    'height' => (int) $matches[2],
                ];
            }

            $runtimeConfig[$liipKey] = $value;
        }

        try {
            // Generate the filtered image URL using runtime config
            return $this->cacheManager->getBrowserPath(
                $src,
                $filterName,
                $runtimeConfig
            );
        } catch (\Exception $e) {
            // Log error or handle appropriately
            return $src;
        }
    }
}
