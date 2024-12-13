# Symfony Responsive Image Bundle

A Symfony bundle that provides responsive image components similar to NuxtImg and NuxtPicture. Optimize your images for Core Web Vitals with automatic resizing, format conversion, and preloading capabilities.

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
    YourVendor\ResponsiveImageBundle\ResponsiveImageBundle::class => ['all' => true],
];
```

## Basic Usage

```twig
<twig:responsive_image
    src="/images/hero.jpg"          # Required: Image source path
    alt="Hero image"                # Recommended: Alt text for accessibility
    width="800"                     # Optional: Target width in pixels
    height="600"                    # Optional: Target height in pixels
    ratio="16:9"                    # Optional: Aspect ratio (alternative to width/height)
    fit="cover"                     # Optional: How image should fit dimensions
    focal="center"                  # Optional: Focus point for cropping
    format="webp"                   # Optional: Output format (default: webp)
    quality="80"                    # Optional: Image quality 0-100 (default: 80)
    lazy="true"                     # Optional: Enable lazy loading (default: true)
    priority="true"                 # Optional: Set high priority for LCP
    preload="true"                  # Optional: Add preload link
    background="#ffffff"            # Optional: Background color for 'contain' fit
    breakpoints="{{ [400,800,1200] }}"   # Optional: Custom responsive widths
    sizes="{{ ['100vw'] }}"             # Optional: Responsive size hints
/>
```

The component automatically chooses between `<img>` and `<picture>` tags based on your configuration:

```twig
{# Outputs <img> when using single format #}
<twig:responsive_image
    src="/images/hero.jpg"
    alt="Hero image"
    width="800"
/>

{# Outputs <picture> when format differs from source #}
<twig:responsive_image
    src="/images/hero.jpg"    # Source is JPG
    alt="Hero image"
    width="800"
    format="webp"            # Will generate <picture> with WebP and JPG fallback
/>
```

The generated HTML will be:

```html
<!-- Single format -->
<img src="..." alt="Hero image" width="800" />

<!-- With format conversion -->
<picture>
  <source type="image/webp" srcset="..." />
  <source type="image/jpeg" srcset="..." />
  <img src="..." alt="Hero image" width="800" />
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
| `width`      | number                                                | Target width in pixels                       |
| `height`     | number                                                | Target height in pixels                      |
| `ratio`      | "16:9", "4:3", "1:1", "3:2", "2:3"                    | Aspect ratio as width:height                 |
| `fit`        | "cover", "contain", "fill", "none"                    | How image fits target dimensions (see below) |
| `focal`      | "center", "top", "bottom", "left", "right", "0.5,0.3" | Focus point for cropping                     |
| `background` | CSS color                                             | Background color when using fit="contain"    |

### Fit Options

#### cover (default)

```twig
<twig:responsive_image
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
<twig:responsive_image
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
<twig:responsive_image
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
<twig:responsive_image
    src="/images/photo.jpg"
    width="400"
    fit="none"
/>
```

- No resizing or cropping
- Only converts format if specified
- Good for: Already optimized images

### Image Optimization

| Property      | Values               | Description                      |
| ------------- | -------------------- | -------------------------------- |
| `format`      | "webp", "jpg", "png" | Output format                    |
| `quality`     | 0-100                | Image quality                    |
| `breakpoints` | number[]             | Custom responsive widths         |
| `sizes`       | string[] or object   | Size hints or breakpoint configs |

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
{# Generate images for specific widths with size hints #}
<twig:responsive_image
    src="/images/hero.jpg"
    alt="Hero image"
    width="1200"
    height="800"
    :breakpoints="[400, 800, 1200]"
    :sizes="['100vw']"
/>
```

### 2. Breakpoint-Specific Configurations

```twig
{# Different configurations per breakpoint #}
<twig:responsive_image
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
<twig:responsive_image
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
<twig:responsive_image
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
<twig:responsive_image
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
<twig:responsive_image
    src="/images/portrait.jpg"
    alt="Portrait"
    ratio="4:3"
    fit="cover"
    focal="0.5,0.3"
/>
```

### Integration with Ibexa

```twig
<twig:responsive_image
    src="{{ content.image.uri }}"
    alt="{{ content.image.alt }}"
    width="{{ content.image.width }}"
    height="{{ content.image.height }}"
    fit="cover"
    focal="{{ content.image.focal }}"
/>
```

## Configuration

Default settings in `config/packages/responsive_image.yaml`:

```yaml
responsive_image:
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
  cache_dir: "%kernel.project_dir%/public/media/cache"
```

## License

This bundle is available under the MIT license.

## Credits

Inspired by [NuxtImg](https://image.nuxtjs.org/) and [Ibexa Platform](https://www.ibexa.co/).

## Placeholders

The component supports different types of placeholders to improve perceived loading performance:

### Blur Placeholder

```twig
{# Generate a blurred, low-res placeholder #}
<twig:responsive_image
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
<twig:responsive_image
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
<twig:responsive_image
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
