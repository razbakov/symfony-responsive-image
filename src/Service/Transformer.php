<?php

namespace Ommax\ResponsiveImageBundle\Service;

class Transformer
{
    private const BREAKPOINT_ORDER = ['default', 'sm', 'md', 'lg', 'xl', '2xl'];
    private array $breakpoints;

    public function __construct(array $breakpoints = [
        'sm' => 640,
        'md' => 768,
        'lg' => 1024,
        'xl' => 1280,
        '2xl' => 1536
    ]) {
        $this->breakpoints = $breakpoints;
    }

    public function getSizes(string $width): array
    {
        $parts = preg_split('/\s+/', trim($width));
        $widths = [];
        $smallestBreakpoint = null;
        $firstVwAfterFixed = null;
        $firstFixedAfterVw = null;
        
        // First pass: collect explicit values and find transitions
        foreach ($parts as $part) {
            if (strpos($part, ':') !== false) {
                [$breakpoint, $value] = explode(':', $part);
                $normalized = $this->normalizeWidthValue($value, $breakpoint);
                $widths[$breakpoint] = $normalized;
                
                // Track transitions
                if ($normalized['vw'] !== '0' && isset($widths['default']) && $widths['default']['vw'] === '0') {
                    $firstVwAfterFixed = $breakpoint;
                }
                if ($normalized['vw'] === '0' && isset($widths['default']) && $widths['default']['vw'] !== '0') {
                    $firstFixedAfterVw = $breakpoint;
                }
                
                // Track the smallest breakpoint
                if (!$smallestBreakpoint ||
                    array_search($breakpoint, self::BREAKPOINT_ORDER) < array_search($smallestBreakpoint, self::BREAKPOINT_ORDER)) {
                    $smallestBreakpoint = $breakpoint;
                }
            } else {
                $widths['default'] = $this->normalizeWidthValue($part, 'default');
            }
        }
        
        // If no default width is set but we have breakpoints, use the smallest breakpoint as default
        if (!isset($widths['default']) && $smallestBreakpoint) {
            $widths['default'] = $widths[$smallestBreakpoint];
        }
        
        // Handle viewport width calculations and transitions
        if (isset($widths['default']) && $widths['default']['vw'] !== '0') {
            $vwPercentage = (int)$widths['default']['vw'];
            
            // Pre-calculate all viewport widths up to fixed width transition
            foreach (self::BREAKPOINT_ORDER as $breakpoint) {
                if ($firstFixedAfterVw && $breakpoint === $firstFixedAfterVw) {
                    // Found fixed width transition point, propagate fixed width to remaining breakpoints
                    $fixedValue = $widths[$firstFixedAfterVw];
                    foreach (self::BREAKPOINT_ORDER as $nextBreakpoint) {
                        if (array_search($nextBreakpoint, self::BREAKPOINT_ORDER) >= array_search($breakpoint, self::BREAKPOINT_ORDER)
                            && !isset($widths[$nextBreakpoint])) {
                            $widths[$nextBreakpoint] = $fixedValue;
                        }
                    }
                    break;
                }
                
                if (!isset($widths[$breakpoint])) {
                    $breakpointWidth = $breakpoint === 'default' ? 
                        $this->breakpoints['sm'] : 
                        $this->breakpoints[$breakpoint];
                        
                    $pixelWidth = (int) ($breakpointWidth * ($vwPercentage / 100));
                    
                    $widths[$breakpoint] = [
                        'value' => $pixelWidth,
                        'vw' => (string)$vwPercentage
                    ];
                }
            }
        }
        // Handle fixed width cases
        else if (isset($widths['default']) && $widths['default']['vw'] === '0') {
            $lastValue = $widths['default'];
            
            // Propagate fixed width to all breakpoints
            foreach (self::BREAKPOINT_ORDER as $breakpoint) {
                if ($firstVwAfterFixed && $breakpoint === $firstVwAfterFixed) {
                    // Found viewport width transition point
                    $vwPercentage = (int)$widths[$breakpoint]['vw'];
                    
                    // Calculate viewport widths for remaining breakpoints
                    foreach (self::BREAKPOINT_ORDER as $vwBreakpoint) {
                        if (array_search($vwBreakpoint, self::BREAKPOINT_ORDER) >= array_search($breakpoint, self::BREAKPOINT_ORDER)
                            && !isset($widths[$vwBreakpoint])) {
                            $breakpointWidth = $this->breakpoints[$vwBreakpoint];
                            $pixelWidth = (int) ($breakpointWidth * ($vwPercentage / 100));
                            
                            $widths[$vwBreakpoint] = [
                                'value' => $pixelWidth,
                                'vw' => (string)$vwPercentage
                            ];
                        }
                    }
                    break;
                }
                
                if (!isset($widths[$breakpoint])) {
                    $widths[$breakpoint] = $lastValue;
                } else {
                    $lastValue = $widths[$breakpoint];
                }
            }
        }
        
        return $widths;
    }

    private function normalizeWidthValue(string $value, string $breakpoint = 'default'): array
    {
        $isVw = str_ends_with($value, 'vw');
        $numericValue = (int) preg_replace('/[^0-9]/', '', $value);
        
        if ($isVw) {
            $breakpointWidth = $breakpoint === 'default' ? 
                $this->breakpoints['sm'] : 
                $this->breakpoints[$breakpoint];
            
            $pixelWidth = (int) ($breakpointWidth * ($numericValue / 100));
            return [
                'value' => $pixelWidth,
                'vw' => (string)$numericValue
            ];
        }
        
        return [
            'value' => $numericValue,
            'vw' => '0'
        ];
    }
}
