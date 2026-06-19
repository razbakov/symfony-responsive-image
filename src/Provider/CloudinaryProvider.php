<?php

namespace Razbakov\ResponsiveImageBundle\Provider;

class CloudinaryProvider implements ProviderInterface
{
    private string $baseUrl = '';
    private array $defaults = [];
    private const UPLOAD_SEGMENT = '/upload/';
    private const DEFAULT_TRANSFORMATION_PRIORITY = 999;
    private const ORDER_PRIORITY = [
        'width' => 1,
        'height' => 2,
        'quality' => 3,
        'format' => 4,
    ];

    private const KEY_MAP = [
        'width' => 'w',
        'height' => 'h',
        'format' => 'f',
        'quality' => 'q',
        'fit' => 'c',
        'focal' => 'g',
        'background' => 'b',
        'ratio' => 'ar',
        'roundCorner' => 'r',
        'gravity' => 'g',
        'rotate' => 'a',
        'effect' => 'e',
        'color' => 'co',
        'flags' => 'fl',
        'dpr' => 'dpr',
        'opacity' => 'o',
        'overlay' => 'l',
        'underlay' => 'u',
        'transformation' => 't',
        'zoom' => 'z',
        'colorSpace' => 'cs',
        'customFunc' => 'fn',
        'density' => 'dn',
        'aspectRatio' => 'ar',
        'blur' => 'e_blur',
    ];

    private const VALUE_MAP = [
        'fit' => [
            'fill' => 'fill',
            'inside' => 'pad',
            'outside' => 'lpad',
            'cover' => 'lfill',
            'contain' => 'scale',
            'minCover' => 'mfit',
            'minInside' => 'mpad',
            'thumbnail' => 'thumb',
            'cropping' => 'crop',
            'coverLimit' => 'limit',
        ],
        'format' => [
            'jpeg' => 'jpg',
        ],
        'gravity' => [
            'auto' => 'auto',
            'subject' => 'auto:subject',
            'face' => 'face',
            'sink' => 'sink',
            'faceCenter' => 'face:center',
            'multipleFaces' => 'faces',
            'multipleFacesCenter' => 'faces:center',
            'north' => 'north',
            'northEast' => 'north_east',
            'northWest' => 'north_west',
            'west' => 'west',
            'southWest' => 'south_west',
            'south' => 'south',
            'southEast' => 'south_east',
            'east' => 'east',
            'center' => 'center',
        ],
    ];

    public function configure(array $config): void
    {
        if (isset($config['base_url'])) {
            $this->assertValidBaseUrl($config['base_url']);
            $this->baseUrl = rtrim($config['base_url'], '/');
        }

        $this->defaults = $config['defaults'] ?? [];
    }

    public function getName(): string
    {
        return 'cloudinary';
    }

    public function getImage(string $src, array $modifiers): string
    {
        $src = $this->normalizeSource($src);
        $baseUrl = $this->baseUrl;

        if ($this->isExternalUrl($src)) {
            [$baseUrl, $src] = $this->splitCloudinaryUrl($src);
        }

        if ('' === $baseUrl) {
            throw new \InvalidArgumentException('Cloudinary provider requires "base_url" configuration for relative image paths.');
        }

        $modifiers = array_merge($this->defaults, $modifiers);
        $this->assertValidModifiers($modifiers);

        $prioritizedTransformations = [];
        foreach ($modifiers as $key => $value) {
            if (!isset(self::KEY_MAP[$key])) {
                continue;
            }

            $cloudinaryKey = self::KEY_MAP[$key];

            // Handle special value mappings
            if (isset(self::VALUE_MAP[$key])) {
                if (isset(self::VALUE_MAP[$key][$value])) {
                    $value = self::VALUE_MAP[$key][$value];
                }
            }

            // Rest of the switch case for special handling...
            switch ($key) {
                case 'background':
                case 'color':
                    $value = $this->convertHexToRgb($value);
                    break;
                case 'roundCorner':
                    if ('max' === $value) {
                        $value = 'max';
                    } elseif (str_contains($value, ':')) {
                        $value = str_replace(':', '_', $value);
                    }
                    break;
                case 'blur':
                    $cloudinaryKey = 'e';
                    $value = 'blur:'.$value;
                    break;
            }

            // Format the transformation
            $transformation = str_contains($cloudinaryKey, '_')
                ? $cloudinaryKey.':'.$value
                : $cloudinaryKey.'_'.$value;

            $priority = self::ORDER_PRIORITY[$key] ?? self::DEFAULT_TRANSFORMATION_PRIORITY;
            $prioritizedTransformations[$priority][] = $transformation;
        }

        ksort($prioritizedTransformations);
        $transformations = array_merge(...array_values($prioritizedTransformations));

        $transformationString = implode(',', $transformations);

        return \sprintf(
            '%s/%s%s',
            rtrim($baseUrl, '/'),
            $transformationString ? $transformationString.'/' : '',
            $src
        );
    }

    private function convertHexToRgb(string $value): string
    {
        return str_starts_with($value, '#') ? 'rgb_'.ltrim($value, '#') : $value;
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

        return $this->isExternalUrl($src) ? $src : ltrim($src, '/');
    }

    private function isExternalUrl(string $src): bool
    {
        return str_starts_with($src, 'http://') || str_starts_with($src, 'https://');
    }

    private function splitCloudinaryUrl(string $src): array
    {
        if (!str_contains($src, self::UPLOAD_SEGMENT)) {
            throw new \InvalidArgumentException('Cloudinary image URL must contain the "/upload/" segment.');
        }

        $uploadPosition = strpos($src, self::UPLOAD_SEGMENT);

        return [
            substr($src, 0, $uploadPosition + \strlen(self::UPLOAD_SEGMENT)),
            substr($src, $uploadPosition + \strlen(self::UPLOAD_SEGMENT)),
        ];
    }

    private function assertValidBaseUrl(string $baseUrl): void
    {
        if ('' === trim($baseUrl) || !filter_var($baseUrl, \FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Cloudinary provider "base_url" must be a valid URL.');
        }
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
