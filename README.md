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
    Ommax\SymfonyImgBundle\OmmaxSymfonyImgBundle::class => ['all' => true],
];
```

## Components

### Img Component

Use for simple responsive images with automatic WebP conversion:

```twig
<twig:img
    src="/images/hero.jpg"               # Required: Image source path
    alt="Hero image"                     # Required: Alt text for accessibility
    width="800"                          # Optional: Override width
    height="600"                         # Optional: Override height
    ratio="16:9"                         # Optional: Override aspect ratio
    fit="cover"                          # Optional: How image should fit dimensions
    focal="center"                       # Optional: Focus point for cropping
    quality="80"                         # Optional: Image quality 0-100 (default: 80)
    lazy="true"                          # Optional: Enable lazy loading (default: true)
    priority="true"                      # Optional: Set high priority for LCP
    preload="true"                       # Optional: Add preload link
    background="#ffffff"                 # Optional: Background color for 'contain' fit
    breakpoints="{{ [400,800,1200] }}"   # Optional: Custom responsive widths
    sizes="{{ ['100vw'] }}"              # Optional: Responsive size hints
    class="hero-image"                   # Any HTML attribute is supported
    data-controller="zoom"               # Custom data attributes
    aria-label="Hero section"            # ARIA attributes
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

Use for art direction with different crops per screen size or orientation:

```twig
<twig:picture
    src="/images/hero.jpg"                  # Required: Image source path
    alt="Hero image"                        # Required: Alt text for accessibility
    class="hero-picture"                    # Any HTML attribute is supported
    data-controller="lightbox"              # Custom data attributes
    format="webp"                           # Primary format (default: webp)
    fallback="jpg"                          # Fallback format (default: from source)
    sources="{{ {
        'sm': {                            # Mobile screens (<768px)
            width: 800,
            ratio: '1:1',                  # Square crop for mobile
            fit: 'cover',
            focal: 'center',               # Center-focused crop
            sizes: '100vw'                 # Image takes full viewport width
        },
        'md': {                            # Desktop screens (≥768px)
            width: 1600,
            ratio: '16:9',                 # Widescreen for desktop
            fit: 'cover',
            focal: '0.5,0.3',              # Custom focal point
            sizes: '80vw'                  # Image takes 80% of viewport width
        }
    } }}"
/>
```

The generated HTML will include format fallbacks:

```html
<picture>
  <source
    media="(max-width: 768px)"
    type="image/webp"
    srcset="hero-400x400.webp 400w, hero-800x800.webp 800w"
    sizes="100vw"
  />
  <source
    media="(max-width: 768px)"
    type="image/jpeg"
    srcset="hero-400x400.jpg 400w, hero-800x800.jpg 800w"
    sizes="100vw"
  />
  <source
    media="(min-width: 769px)"
    type="image/webp"
    srcset="hero-800x450.webp 800w, hero-1600x900.webp 1600w"
    sizes="80vw"
  />
  <source
    media="(min-width: 769px)"
    type="image/jpeg"
    srcset="hero-800x450.jpg 800w, hero-1600x900.jpg 1600w"
    sizes="80vw"
  />
  <img src="hero-1600x900.jpg" alt="Hero image" width="1600" height="900" />
</picture>
```

Format fallbacks:

- WebP sources are generated first
- Fallback sources use original format or specified fallback
- Fallback `<img>` uses most compatible format
- Browsers automatically choose the best supported format

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

### Simple Responsive Images

Use `<twig:img>` when you need different sizes of the same image:

```twig
<twig:img
    src="/images/hero.jpg"
    alt="Hero image"
    width="1600"                         # Maximum/original width
    ratio="16:9"                         # Maintain aspect ratio
    breakpoints="[400, 800, 1200]"       # Generate different sizes
    sizes="100vw"                        # Size hints for browser
/>
```

The above generates multiple sizes of the same image, maintaining aspect ratio:

```html
<img
  src="hero-1600.webp"
  srcset="hero-400.webp 400w, hero-800.webp 800w, hero-1200.webp 1200w"
  sizes="100vw"
  alt="Hero image"
  width="1600"
  height="900"
/>
```

### Art Direction with Breakpoints

Use `<twig:picture>` when you need different versions of the image:

```twig
<twig:picture
    src="/images/hero.jpg"
    alt="Hero image"
    sources="{{ {
        'sm': {                            # Mobile screens (<768px)
            width: 800,
            ratio: '1:1',                  # Square crop for mobile
            fit: 'cover',
            focal: 'center',               # Center-focused crop
            sizes: '100vw'                 # Image takes full viewport width
        },
        'md': {                            # Desktop screens (≥768px)
            width: 1600,
            ratio: '16:9',                 # Widescreen for desktop
            fit: 'cover',
            focal: '0.5,0.3',              # Custom focal point
            sizes: '80vw'                  # Image takes 80% of viewport width
        }
    } }}"
/>
```

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

Default breakpoints:

- xs: ≤ 320px
- sm: ≤ 640px
- md: ≤ 768px
- lg: ≤ 1024px
- xl: ≤ 1280px
- 2xl: ≤ 1536px

## Common Use Cases

### Simple Responsive Image

```twig
<twig:img
    src="/images/hero.jpg"
    alt="Hero image"
    width="1600"                         # Maximum width
    ratio="16:9"                         # Maintain aspect ratio
    breakpoints="[400, 800, 1200]"       # Generate different sizes
    sizes="100vw"                        # Size hints for browser
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
    breakpoints="[400, 800, 1200]"       # Generate different sizes
    sizes="100vw"                        # Size hints for browser
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
    breakpoints="[400, 800, 1200]"       # Generate different sizes
    sizes="100vw"                        # Size hints for browser
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

## License

This bundle is available under the MIT license.

## Credits

Inspired by [NuxtImg](https://image.nuxtjs.org/) and [Ibexa Platform](https://www.ibexa.co/).
