# Symfony Responsive Image Bundle

A Symfony bundle that provides two components for optimized images:

- `<twig:img>` - For simple responsive images with automatic WebP conversion
- `<twig:picture>` - For art direction with different crops per breakpoint
- `responsive_image_preloads()` - For preloading images

**Key Features:**

- 🖼️ Automatic responsive image generation
- 🎯 Smart cropping with focal points
- 🔄 WebP format conversion
- 🚀 Performance optimization
- ⚡ Image preloading support

**Benefits:**

- 📱 Better user experience across all devices and screen sizes
- ⚡ Faster page loads with optimized image delivery
- 🎨 Maintain image quality while reducing file sizes
- 📊 Improved Core Web Vitals scores
- 💻 Less developer time spent on image optimization

## Table of Contents

1. [Requirements](#requirements)
2. [Installation](#installation)
3. [Components](#components)
   - [Img Component](#img-component)
   - [Picture Component](#picture-component)
4. [Configuration](#configuration)
   - [Preloading Images](#preloading-images)
   - [Responsive Images](#responsive-images)
   - [Density Support](#density-support)
   - [Fit Options](#fit-options-cropping-and-resizing)
   - [Fallback Options](#fallback-options)
   - [Placeholder Options](#placeholder-options)
   - [Art Direction](#art-direction)
5. [Common Use Cases](#common-use-cases)
6. [Using Presets](#using-presets)
7. [Settings](#settings)
8. [Providers](#providers)
9. [Error Handling](#error-handling)
10. [Security](#security)
11. [Development](#development)
12. [License](#license)
13. [Credits](#credits)

## Requirements

- PHP 8.1 or higher
- Symfony 6.0 or higher
- GD extension or Imagick extension

## Installation

```bash
composer require ommax/symfony-responsive-image
```

Register the bundle in `config/bundles.php`:

```php
return [
    // ...
    Ommax\ResponsiveImageBundle\ResponsiveImageBundle::class => ['all' => true],
];
```

## Components

Choose the approach that best fits your needs:

- Use `<twig:img>` when you need:

  - Different sizes of the same image
  - Same aspect ratio across all sizes
  - Same crop/focal point across all sizes

- Use `<twig:picture>` when you need:
  - Different aspect ratios per breakpoint
  - Different crops per breakpoint
  - Different focal points per breakpoint
  - Different sizes within each breakpoint

### Img Component

Use for simple responsive images with automatic WebP conversion:

```twig
<twig:img
    src="/images/hero.jpg"               # Required: Image source path
    alt="Hero image"                     # Recommended: Alt text for accessibility
    width="100vw sm:50vw md:400px        # Full width on mobile, half width on tablet, 400px on desktop
    preset="hero"                        # Use preset
    class="hero-image"                   # Any HTML attribute is supported
    data-controller="zoom"               # Custom data attributes
    aria-label="Hero section"            # ARIA attributes
    preload="true"                       # Optional: Add preload link
    fetchpriority="high"                 # Optional: Set high priority for LCP
    loading="lazy"                       # Optional: Enable lazy loading
    format="webp"                        # Output format (default: webp)

    quality="80"                         # Optional: Image quality 0-100 (default: 80)
    fallback="auto"                      # Fallback format (default: auto)

    ratio="16:9"                         # Optional: Override aspect ratio
    height="600"                         # Optional: Override height
    densities="x1 x2"                    # Optional: Generate different densities
    focal="center"                       # Optional: Focus point for cropping
    fit="cover"                          # Optional: How image should fit dimensions
    background="#ffffff"                 # Optional: Background color for 'contain' fit
    placeholder="blur"                   # Enable blurred placeholder
/>
```

### Picture Component

Use for art direction with different crops per screen size or orientation:

```twig
<twig:picture
    src="/images/hero.jpg"                 # Required: Image source path
    alt="Hero image"                       # Recommended: Alt text for accessibility
    class="hero-picture"                   # Any HTML attribute is supported
    width="100vw md:80vw"                  # Responsive sizes per breakpoint
    format="webp"                          # Output format (default: webp)

    quality="80"                           # Optional: Image quality 0-100 (default: 80)
    fallback="auto"                        # Fallback format (default: auto)

    ratio="sm:1:1 md:16:9"                 # Different aspect ratios per breakpoint
    focal="sm:center md:0.5,0.3"           # Focus points per breakpoint
    fit="sm:contain md:cover"              # Fit behavior per breakpoint
/>
```

## Configuration

### Preloading Images

Add this to your base template to enable preloading of critical images:

```twig
<!DOCTYPE html>
<html>
    <head>
        {{ responsive_image_preloads() }}
        {# Will output preload links for images marked with preload="true" #}
    </head>
    <body>
        {# Your content #}
    </body>
</html>
```

The `responsive_image_preloads()` function generates appropriate `<link rel="preload">` tags for any images that have `preload="true"` set. This is especially useful for LCP optimization.

### Responsive Images

```twig
<twig:img
    src="/images/hero.jpg"
    alt="Hero image"
    width="100vw sm:50vw md:400px lg:800" # Default size for mobile, then breakpoint:size pairs
/>
```

This will automatically:

- Generate appropriate image widths based on your design system's breakpoints
- Create the correct srcset and sizes attributes
- Optimize image delivery for each viewport size

The sizes syntax follows this pattern:

- Start with default size (applies to smallest screens)
- Add breakpoint:size pairs for larger screens
- Each size applies from that breakpoint up
- Example: `"100vw sm:50vw md:400px lg:800"`
  - `100vw` - full width (640px) on mobile (<640px)
  - `sm:50vw` - half width (384px) from sm breakpoint (≥640px)
  - `md:400px` - fixed 400px from md breakpoint (≥768px)
  - `lg:800` - fixed 800px from lg breakpoint (≥1024px)

Default breakpoints:

- default: <640px - Mobile portrait
- sm: >= 640px - Mobile landscape
- md: >= 768px - Tablet portrait
- lg: >= 1024px - Tablet landscape
- xl: >= 1280px - Desktop
- 2xl: >= 1536px - Large desktop

Transformation rules:
- If default width is not set, it will be taken from the smallest breakpoint, i.e `sm:50vw md:400px` is translated to `50vw md:400px`.
- Dynamic width `vw` will generate all sizes from smallest breakpoint to image size but not larger than largest breakpoint.
- Fixed width wll be used until there is a breakpoint with `vw` width set, from which point it will use dynamic rule.

| Width string        | Image versions |
| --------------------| -------------- |
| 100                 | 100px |
| 1000                | 1000px |
| sm:50 md:100 lg:200 | 50px, 100px, 200px |
| 100vw               | 640px, 768px, 1024px, 1280px, 1536px |
| 50vw lg:400px       | 320px, 384px, 400px |
| 100 lg:100vw        | 100px, 1024px, 1280px, 1536px |
| 100vw md:100        | 640px, 768px, 100px |
| 1000 lg:100vw       | 1000px, 1024px, 1280px, 1536px |

Width and height are automatically calculated from:

- Original image dimensions when no ratio specified
- When ratio specified:
  - Original width and calculated height if no width/height set
  - Width and calculated height if width set (width="800" ratio="16:9")
  - Calculated width and height if height set (height="600" ratio="16:9")
  - Override both with width/height if needed (width="800" height="600")

The bundle uses your design system's breakpoints (configurable in `responsive_image.yaml`).

### Density Support

To generate special versions of images for high-DPI displays (like Retina), use the `densities` attribute:

```twig
<twig:img
    src="/images/logo.png"
    width="100"
    densities="x1 x2" # Generate 1x and 2x versions
    alt="Logo"
/>
```

This will generate:

```html
<img
  src="/images/logo-100.jpg"
  srcset="/images/logo-100.jpg 100w, /images/logo-200.jpg 200w"
  width="100"
  alt="Logo"
/>
```

You can combine densities with responsive sizes:

```twig
<twig:img
    src="/images/hero.jpg"
    width="100vw sm:50vw md:400px"
    densities="x1 x2"
    alt="Hero image"
/>
```

The component will:

- Generate 1x and 2x versions for each size
- Include both width (w) and density (x) descriptors in srcset
- Automatically calculate the correct dimensions for each density

### Fit Options (Cropping and Resizing)

The `fit` property specifies how the image should be resized to fit the target dimensions. There are five standard values:

- `cover` (default) - Preserving aspect ratio, ensures the image covers both provided dimensions by cropping/clipping to fit
- `contain` - Preserving aspect ratio, contains image within both provided dimensions using "letterboxing" where necessary
- `fill` - Ignores the aspect ratio of the input and stretches to both provided dimensions
- `inside` - Preserving aspect ratio, resizes the image to be as large as possible while ensuring its dimensions are less than or equal to both those specified
- `outside` - Preserving aspect ratio, resizes the image to be as small as possible while ensuring its dimensions are greater than or equal to both those specified
- `none` - Uses original image dimensions

### Fallback Options

The `fallback` property controls format selection for older browsers:

- `auto` (default): Chooses based on original image
  - PNG fallback if original has transparency (PNG, WebP, GIF)
  - JPEG fallback for all other formats
- `jpg`: Force JPEG as fallback format
- `png`: Force PNG as fallback format

### Placeholder Options

The `placeholder` property controls image loading placeholders:

```twig
<twig:img
    src="/images/hero.jpg"
    alt="Hero image"
    placeholder="blur"            # Enable blurred placeholder
    placeholder="[200]"           # Square placeholder of 200px
    placeholder="[200,150]"       # Placeholder with specific dimensions
    placeholder="[200,150,70,3]"  # With quality=70 and blur=3
/>
```

Placeholder options:

- `none` (default) - No placeholder
- `blur` - Blurred version of the image
- `dominant` - Dominant color of the image
- Array syntax for custom dimensions:
  - `[size]` - Square placeholder (e.g. `[200]`)
  - `[width,height]` - Custom dimensions (e.g. `[200,150]`)
  - `[width,height,quality,blur]` - Full control (e.g. `[200,150,70,3]`)

The placeholder image is automatically:

- Converted to a lightweight Base64 data URI
- Shown while the main image loads
- Faded out when the main image loads
- Optimized for performance

Example with blur placeholder:

```twig
<twig:img
    src="/images/hero.jpg"
    alt="Hero image"
    placeholder="blur"
    placeholder-class="my-placeholder"   # Optional custom class
/>
```

### Art Direction

Use `<twig:picture>` when you need different versions of the image:

```twig
<twig:picture
    src="/images/hero.jpg"
    alt="Hero image"
    width="100vw md:80vw"                 # Full width on mobile, 80% on desktop
    ratio="sm:1:1 md:16:9"                # Square for mobile, widescreen for desktop
    fit="sm:cover md:cover"               # Cover fitting for both breakpoints
    focal="sm:center md:0.5,0.3"          # Center on mobile, custom focus on desktop
/>
```

## Common Use Cases

### Simple Responsive Image

```twig
<twig:img
    src="/images/hero.jpg"
    alt="Hero image"
    width="100vw"
    ratio="16:9"
/>
```

### Hero Image (LCP Optimization)

```twig
<twig:img
    src="/images/hero.jpg"
    alt="Hero image"
    ratio="16:9"
    fit="cover"
    focal="center"
    fetchpriority="high"
    preload="true"
    width="100vw"
/>
```

### Product Image (Contained)

```twig
<twig:img
    src="/images/product.jpg"
    alt="Product"
    width="800"
    height="800"
    fit="contain"
    background="#ffffff"
    loading="lazy"
/>
```

### Portrait with Smart Cropping

```twig
<twig:img
    src="/images/portrait.jpg"
    alt="Portrait"
    ratio="4:3"
    fit="cover"
    focal="0.5,0.3"
    width="100vw"                     
/>
```

### Integration with Ibexa

```twig
<twig:img
    src="{{ content.image.uri }}"
    alt="{{ content.image.alt }}"
    width="{{ content.image.width }}"
    height="{{ content.image.height }}"
    fit="cover"
    focal="{{ content.image.focal }}"
/>
```

## Using Presets

Presets allow you to reuse common configurations:

```yaml
# config/packages/responsive_image.yaml
responsive_image:
  presets:
    thumbnail:
      width: 200
      height: 200
      fit: cover
      quality: 90

    hero:
      ratio: "16:9"
      sizes: "100vw sm:50vw md:400px"
      fetchpriority: high
      preload: true

    avatar:
      width: 48
      height: 48
      fit: cover
      placeholder: blur

    product:
      ratio: "1:1"
      fit: contain
      background: "#ffffff"
      placeholder: dominant
```

Using presets in templates:

```twig
{# Using a preset #}
<twig:img
    src="/images/photo.jpg"
    alt="Photo"
    preset="hero"
/>

{# Override preset values #}
<twig:img
    src="/images/photo.jpg"
    alt="Photo"
    preset="hero"
    fetchpriority="low"    {# Override specific preset value #}
/>
```

You can define your own presets in the configuration. Preset values can be overridden by directly setting properties on the component.

## Settings

Default settings in `config/packages/responsive_image.yaml`:

```yaml
responsive_image:
  missing_image_placeholder: "/path/to/404-placeholder.jpg"
  defaults:
    breakpoints:
      xs: 320
      sm: 640
      md: 768
      lg: 1024
      xl: 1280
      2xl: 1536
    format: "webp"
    quality: 80
    loading: lazy
    fetchpriority: low
    fit: "cover"
    focal: "center"
    placeholder: "none"
    placeholder-class: "lazy-placeholder"
```

## Providers

The bundle supports multiple providers for image transformation and optimization. Each provider is responsible for generating optimized image URLs and handling transformations. See [Providers](providers.md) for more information.

## Error Handling

The bundle provides several error handling mechanisms:

- Missing images return a 404 placeholder
- Invalid configurations throw `InvalidConfigurationException`
- Processing errors are logged to Symfony's error log

## Security

- Allowed image types: jpg, jpeg, png, gif, webp
- Maximum upload size: Configured through PHP's upload_max_filesize
- Path validation prevents directory traversal attacks
- Image validation ensures file integrity

## Development

### Setup

```bash
# Clone repository
git clone https://github.com/ommax/symfony-responsive-image
cd symfony-responsive-image

# Install dependencies
composer install

# Run tests
composer test
```

## License

This bundle is available under the MIT license.

## Credits

Inspired by [NuxtImg](https://image.nuxtjs.org/) and [Ibexa Platform](https://www.ibexa.co/).
