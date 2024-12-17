<?php

namespace Ommax\ResponsiveImageBundle\Provider;

class CloudinaryProvider implements ProviderInterface
{
    private string $baseUrl;
    private array $defaults;

    /**
     * Map of modifier keys to Cloudinary parameters.
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

    /**
     * Map of modifier values to Cloudinary values.
     */
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

    public function __construct(array $config = [])
    {
        $this->baseUrl = $config['base_url'] ?? '';
        $this->defaults = $config['defaults'] ?? [];
    }

    public function getName(): string
    {
        return 'cloudinary';
    }

    public function getImage(string $src, array $modifiers): string
    {
        $src = ltrim($src, '/');
        $transformations = [];

        // Merge defaults with provided modifiers
        $modifiers = array_merge($this->defaults, $modifiers);

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

            // Handle special formatting for certain keys
            if (str_contains($cloudinaryKey, '_')) {
                $transformations[] = $cloudinaryKey.':'.$value;
            } else {
                $transformations[] = $cloudinaryKey.'_'.$value;
            }
        }

        // Build the final URL
        $transformationString = implode(',', $transformations);

        return \sprintf(
            '%s/%s/%s',
            $this->baseUrl,
            $transformationString ? $transformationString.'/' : '',
            $src
        );
    }

    private function convertHexToRgb(string $value): string
    {
        return str_starts_with($value, '#') ? 'rgb_'.ltrim($value, '#') : $value;
    }
}
