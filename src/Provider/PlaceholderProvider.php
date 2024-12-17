<?php

namespace Ommax\ResponsiveImageBundle\Provider;

class PlaceholderProvider implements ProviderInterface
{
    private const BASE_URL = 'https://placehold.co';

    /**
     * Map of modifier keys to Placeholder parameters
     */
    private const KEY_MAP = [
        'width' => 'width',
        'height' => 'height',
        'background' => 'background',
        'text' => 'text',
        'text_color' => 'textColor',
        'ratio' => 'ratio',
    ];

    private array $defaults;

    public function __construct(array $config = [])
    {
        $this->defaults = $config['defaults'] ?? [];
    }

    public function configure(array $config): void
    {
        $this->defaults = $config['defaults'];
    }

    public function getName(): string
    {
        return 'placeholder';
    }

    public function getImage(string $src, array $modifiers): string
    {
        $params = $this->processModifiers($modifiers);
        
        return $this->buildUrl($params);
    }

    private function processModifiers(array $modifiers): array
    {
        $params = [];
        
        // Process each modifier using KEY_MAP
        foreach ($modifiers as $key => $value) {
            if (!isset(self::KEY_MAP[$key])) {
                continue;
            }

            $placeholderKey = self::KEY_MAP[$key];
            $params[$placeholderKey] = $value;
        }

        // Apply defaults for missing parameters
        foreach ($this->defaults as $key => $defaultValue) {
            if (!isset($params[self::KEY_MAP[$key]])) {
                $params[self::KEY_MAP[$key]] = $defaultValue;
            }
        }

        // Handle ratio if specified
        if ($params['ratio'] !== null) {
            if (preg_match('/^(\d+):(\d+)$/', $params['ratio'], $matches)) {
                $ratioWidth = (int)$matches[1];
                $ratioHeight = (int)$matches[2];
                if ($params['height'] === null) {
                    $params['height'] = (int)($params['width'] * $ratioHeight / $ratioWidth);
                }
            }
        }

        // Set height to width if still not specified
        if ($params['height'] === null) {
            $params['height'] = $params['width'];
        }

        // Set default text if not specified
        if ($params['text'] === null) {
            $params['text'] = sprintf('%dx%d', $params['width'], $params['height']);
        }

        // Clean up color values
        $params['background'] = ltrim($params['background'], '#');
        $params['textColor'] = ltrim($params['textColor'], '#');

        return $params;
    }

    private function buildUrl(array $params): string
    {
        return sprintf(
            '%s/%dx%d/%s/%s?text=%s',
            self::BASE_URL,
            $params['width'],
            $params['height'],
            $params['background'],
            $params['textColor'],
            urlencode($params['text'])
        );
    }
}
