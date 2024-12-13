<?php

namespace YourVendor\ResponsiveImageBundle\Service;

class PreloadManager
{
    private array $preloads = [];

    public function addPreload(string $src, string $srcset, ?string $sizes = null): void
    {
        $this->preloads[] = [
            'src' => $src,
            'srcset' => $srcset,
            'sizes' => $sizes,
        ];
    }

    public function getPreloads(): array
    {
        return $this->preloads;
    }

    public function clearPreloads(): void
    {
        $this->preloads = [];
    }
} 