# Symfony Image Components Bundle

A Symfony bundle that provides two components for optimized images:

- `<twig:img>` - For simple responsive images with automatic WebP conversion
- `<twig:picture>` - For art direction with different crops per breakpoint

## Features

- 🖼️ Automatic responsive image generation
- 🎯 Smart cropping with focal points
- 🔄 WebP format conversion
- 🚀 Core Web Vitals optimization
- ⚡ Image preloading support
- 💾 Automatic caching

## Installation

```bash
composer require ommax/symfony-img
```

Register the bundle in `config/bundles.php`:

```php
return [
    // ...
    YourVendor\ImageComponentsBundle\ImageComponentsBundle::class => ['all' => true],
];
```

## Components

### Img Component

Use for simple responsive images with automatic WebP conversion:

```twig
<twig:img
    src="/images/hero.jpg"          # Required: Image source path
    alt="Hero image"                # Recommended: Alt text for accessibility
    width="800"                     # Optional: Override width
    height="600"                    # Optional: Override height
    ratio="16:9"                    # Optional: Override aspect ratio
    fit="cover"                     # Optional: How image should fit dimensions
    focal="center"                  # Optional: Focus point for cropping
    quality="80"                    # Optional: Image quality 0-100 (default: 80)
    lazy="true"                     # Optional: Enable lazy loading (default: true)
    priority="true"                 # Optional: Set high priority for LCP
    preload="true"                  # Optional: Add preload link
    background="#ffffff"            # Optional: Background color for 'contain' fit
    breakpoints="{{ [400,800,1200] }}"   # Optional: Custom responsive widths
    sizes="{{ ['100vw'] }}"             # Optional: Responsive size hints
    class="hero-image"             # Any HTML attribute is supported
    data-controller="zoom"         # Custom data attributes
    aria-label="Hero section"      # ARIA attributes
/>
```

Width and height are automatically calculated from:

- Original image dimensions when no ratio specified
- When ratio specified:
  - Original width and calculated height if no width/height set
  - Width and calculated height if width set (width="800" ratio="16:9")
  - Calculated width and height if height set (height="600" ratio="16:9")
  - Override both with width/height if needed (width="800" height="600")

### Picture Component

Use for art direction with different crops per breakpoint:

```twig
<twig:picture
    src="/images/hero.jpg"
    alt="Hero image"
    class="hero-picture"           # Any HTML attribute is supported
    data-controller="lightbox"     # Custom data attributes
    sources="{{ {
        '(max-width: 768px)': {     # Mobile: square crop
            width: 800,             # Width is required for art direction
            ratio: '1:1',           # Square crop
            fit: 'cover',
            focal: 'center'
        },
        '(min-width: 769px)': {     # Desktop: wide crop
            width: 1600,            # Width is required for art direction
            ratio: '16:9',
            fit: 'cover',
            focal: '0.5,0.3'
        }
    } }}"
/>
```

Width and height are automatically calculated from:

- Largest source dimensions
- Source ratio if specified
- Falls back to original image dimensions if no sources

The generated HTML will be:

```html
<!-- Img component output -->
<img
  src="hero.webp"
  srcset="..."
  alt="Hero image"
  width="1600"
  height="900"
  class="hero-image"
  data-controller="zoom"
  aria-label="Hero section"
/>

<!-- Picture component output -->
<picture class="hero-picture" data-controller="lightbox">
  <source media="(max-width: 768px)" srcset="hero-800x800.webp" />
  <source media="(min-width: 769px)" srcset="hero-1600x900.webp" />
  <img src="hero-1600x900.webp" alt="Hero image" width="1600" height="900" />
</picture>
```

## Preloading Images

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

## Component Properties

### Dimensions & Cropping

| Property     | Values                                                | Description                                  |
| ------------ | ----------------------------------------------------- | -------------------------------------------- |
| `width`      | number                                                | Target width in pixels (default: original)   |
| `height`     | number                                                | Target height in pixels                      |
| `ratio`      | "16:9", "4:3", "1:1", "3:2", "2:3"                    | Aspect ratio as width:height                 |
| `fit`        | "cover", "contain", "fill", "none"                    | How image fits target dimensions (see below) |
| `focal`      | "center", "top", "bottom", "left", "right", "0.5,0.3" | Focus point for cropping                     |
| `background` | CSS color                                             | Background color when using fit="contain"    |

### Fit Options

#### cover (default)

```twig
<twig:img
    src="/images/photo.jpg"
    width="400"
    height="300"
    fit="cover"
/>
```

- Scales image to fill width AND height
- Maintains aspect ratio
- Crops excess parts
- Good for: Hero images, thumbnails

#### contain

```twig
<twig:img
    src="/images/photo.jpg"
    width="400"
    height="300"
    fit="contain"
    background="#f0f0f0"
/>
```

- Scales image to fit within width AND height
- Maintains aspect ratio
- Shows entire image
- Adds padding if needed
- Good for: Product images, logos

#### fill

```twig
<twig:img
    src="/images/photo.jpg"
    width="400"
    height="300"
    fit="fill"
/>
```

- Stretches image to exactly fill width AND height
- Does NOT maintain aspect ratio
- No cropping
- Good for: Rare cases where distortion is acceptable

#### none

```twig
<twig:img
    src="/images/photo.jpg"
    width="400"
    fit="none"
/>
```

- No resizing or cropping
- Only converts format if specified
- Good for: Already optimized images

### Image Optimization

| Property  | Values               | Description                               |
| --------- | -------------------- | ----------------------------------------- |
| `format`  | "webp", "jpg", "png" | Target format (original used as fallback) |
| `quality` | 0-100                | Image quality                             |

### Performance

| Property            | Values                     | Description              |
| ------------------- | -------------------------- | ------------------------ |
| `lazy`              | boolean                    | Enable lazy loading      |
| `priority`          | boolean                    | Set fetchpriority="high" |
| `preload`           | boolean                    | Add preload link         |
| `placeholder`       | "blur", "dominant", "none" | Loading placeholder type |
| `placeholder-color` | CSS color                  | Custom placeholder color |

## Responsive Images

There are two ways to handle responsive images:

### 1. Simple Breakpoints & Sizes

```twig
<twig:img
    src="/images/hero.jpg"
    alt="Hero image"
    width="1200"
    height="800"
    breakpoints="[400, 800, 1200]"
    sizes="['100vw']"
/>
```

### 2. Breakpoint-Specific Configurations

```twig
<twig:picture
    src="/images/hero.jpg"
    alt="Hero image"
    sources="{{ {
        sm: {                # < 768px
            width: 400,
            height: 300,
            fit: 'cover'
        },
        md: {                # >= 768px
            width: 800,
            height: 400,
            fit: 'contain'
        },
        lg: {                # >= 1024px
            ratio: '16:9',
            fit: 'cover'
        }
    } }}"
    focal="center"
/>
```

Choose the approach that best fits your needs:

- **Simple breakpoints**: When you just need different widths with size hints
- **Breakpoint configurations**: When you need different dimensions, ratios, or fit modes per breakpoint

Default breakpoints:

- xs: ≤ 320px
- sm: ≤ 640px
- md: ≤ 768px
- lg: ≤ 1024px
- xl: ≤ 1280px
- 2xl: ≤ 1536px

## Common Use Cases

### Responsive Sizes

```twig
{# Different dimensions for different screen sizes #}
<twig:img
    src="/images/hero.jpg"
    alt="Hero image"
    :sizes="{{ {
        sm: {                # < 768px
            width: 400,
            height: 300,
            fit: 'cover'
        },
        md: {                # >= 768px
            width: 800,
            height: 400,
            fit: 'contain'
        },
        lg: {                # >= 1024px
            ratio: '16:9',
            fit: 'cover'
        }
    } }}"
    focal="center"
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
    priority="true"
    preload="true"
    :sizes="{{ {
        sm: { width: 400, ratio: '16:9' },
        md: { width: 800, ratio: '16:9' },
        lg: { width: 1200, ratio: '16:9' }
    } }}"
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
    lazy="true"
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

## Configuration

Default settings in `config/packages/image_components.yaml`:

```yaml
image_components:
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
    lazy: true
    priority: false
    preload: false
    fit: "cover"
    focal: "center"
    placeholder: "none"
    placeholder-color: null
  cache_dir: "%kernel.project_dir%/public/media/cache"
```

## Using Presets

Presets allow you to reuse common configurations:

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
    priority="false"    {# Override specific preset value #}
/>

{# Available presets #}

thumbnail:
  - Fixed 200x200 square
  - Cover fit
  - High quality (90)

hero:
  - 16:9 ratio
  - Responsive sizes
  - Priority loading
  - Preloading enabled

avatar:
  - 48x48 square
  - Blur placeholder
  - Cover fit

product:
  - Square ratio
  - Contain fit
  - White background
  - Dominant color placeholder
```

You can define your own presets in the configuration. Preset values can be overridden by directly setting properties on the component.

## License

This bundle is available under the MIT license.

## Credits

Inspired by [NuxtImg](https://image.nuxtjs.org/) and [Ibexa Platform](https://www.ibexa.co/).

## Placeholders

The component supports different types of placeholders to improve perceived loading performance:

### Blur Placeholder

```twig
{# Generate a blurred, low-res placeholder #}
<twig:img
    src="/images/hero.jpg"
    alt="Hero image"
    width="800"
    placeholder="blur"
/>
```

- Generates tiny (10px wide) blurred preview
- Scales up and animates to full image
- Best for: Photos and complex images

### Dominant Color

```twig
{# Use image's dominant color as placeholder #}
<twig:img
    src="/images/product.jpg"
    alt="Product"
    width="800"
    placeholder="dominant"
/>
```

- Extracts main color from image
- Shows solid color until image loads
- Best for: Product photos, simple images

### Custom Color

```twig
{# Use specific color as placeholder #}
<twig:img
    src="/images/photo.jpg"
    alt="Photo"
    width="800"
    placeholder="color"
    placeholder-color="#f0f0f0"
/>
```

- Uses provided color
- Useful when you know the desired background
- Best for: Brand-specific colors

The generated HTML includes inline styles for smooth transitions:

```html
<div class="responsive-image-wrapper" style="background-color: #f0f0f0;">
  <img
    src="..."
    alt="Photo"
    width="800"
    class="responsive-image loading"
    style="transition: opacity 0.2s;"
  />
</div>
```

## Legacy Format Selection

When using `legacy="true"`, the fallback format is determined as follows:

```twig
{# Automatic fallback format selection #}
<twig:img
    src="/images/logo.png"     # Original has transparency
    format="webp"
    legacy="true"              # Will use PNG as fallback
/>

<twig:img
    src="/images/photo.jpg"    # Original without transparency
    format="webp"
    legacy="true"              # Will use JPG as fallback
/>

{# Override fallback format #}
<twig:img
    src="/images/photo.jpg"
    format="webp"
    legacy="true"
    legacyFormat="png"        # Force PNG as fallback
/>
```

Default fallback selection:

- If original format supports transparency (PNG, WebP, GIF) → PNG fallback
- Otherwise → JPEG fallback
- Can be overridden with `legacyFormat` property
