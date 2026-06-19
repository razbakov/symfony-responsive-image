<?php

namespace Razbakov\ResponsiveImageBundle\Provider;

class FastlyProvider implements ProviderInterface
{
    private string $baseUrl = '';
    private array $defaults = [];

    private const KEY_MAP = [
        'width' => 'width',
        'height' => 'height',
        'format' => 'format',
        'quality' => 'quality',
        'fit' => 'fit',
        'background' => 'bg-color',
        'ratio' => 'aspect-ratio',
    ];

    private const VALUE_MAP = [
        'fit' => [
            'fill' => 'crop',
            'inside' => 'crop',
            'outside' => 'crop',
            'cover' => 'bounds',
            'contain' => 'bounds',
        ],
    ];

    public function configure(array $config): void
    {
        if (!isset($config['base_url']) || '' === trim($config['base_url'])) {
            throw new \InvalidArgumentException('Fastly provider requires "base_url" configuration.');
        }

        if (!filter_var($config['base_url'], \FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Fastly provider "base_url" must be a valid URL.');
        }

        $this->baseUrl = rtrim($config['base_url'], '/');
        $this->defaults = $config['defaults'] ?? [];
    }

    public function getName(): string
    {
        return 'fastly';
    }

    public function getImage(string $src, array $modifiers): string
    {
        $src = $this->normalizeSource($src);

        $modifiers = array_merge($this->defaults, $modifiers);
        $this->assertValidModifiers($modifiers);

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

            $transformations[] = rawurlencode($fastlyKey).'='.rawurlencode((string) $value);
        }

        $queryString = implode('&', $transformations);

        return \sprintf(
            '%s/%s%s',
            rtrim($this->baseUrl, '/'),
            $src,
            $queryString ? '?'.$queryString : ''
        );
    }

    private function normalizeSource(string $src): string
    {
        $src = trim($src);

        if ('' === $src) {
            throw new \InvalidArgumentException('Image source cannot be empty.');
        }

        if (str_contains($src, '..')) {
            throw new \InvalidArgumentException('Image source cannot contain directory traversal segments.');
        }

        return ltrim($src, '/');
    }

    private function assertValidModifiers(array $modifiers): void
    {
        foreach ($modifiers as $key => $value) {
            if (!\is_scalar($value) && null !== $value) {
                throw new \InvalidArgumentException(\sprintf('Modifier "%s" must be a scalar value or null.', $key));
            }
        }
    }
}
