# WordPress Vite.js Developer Theme - Technical Documentation

**Theme Name:** AntsNet
**Author:** Alen Melkić
**Version:** 1.0.0
**Description:** Modern WordPress theme with Vite.js, AI content generation, and custom media blocks

---

## Table of Contents

1. [Core Technologies](#core-technologies)
2. [Project Structure](#project-structure)
3. [Build System](#build-system)
4. [WordPress Integration](#wordpress-integration)
5. [AI Content Generation](#ai-content-generation)
6. [Custom Gutenberg Blocks](#custom-gutenberg-blocks)
7. [Component System](#component-system)
8. [Image Optimization](#image-optimization)
9. [Swup.js Integration](#swupjs-integration)
10. [Media Players](#media-players)
11. [Custom Post Types](#custom-post-types)
12. [Styling Architecture](#styling-architecture)
13. [Development Workflow](#development-workflow)
14. [Configuration & Setup](#configuration--setup)
15. [Features Summary](#features-summary)

---

## Core Technologies

### Frontend Build Tools
- **[Vite.js 7.1.2](https://vitejs.dev/)** - Ultra-fast build tool with HMR
- **[React 18.3.1](https://react.dev/)** - For Gutenberg block editor components
- **[Vitest 3.2.4](https://vitest.dev/)** - Fast unit testing framework
- **[Sass 1.90.0](https://sass-lang.com/)** - CSS preprocessor

### CSS Framework
- **[Bootstrap 5.3.7](https://getbootstrap.com/)** - Fully customizable via SASS

### WordPress Integration
- **[@wordpress/scripts 30.24.0](https://www.npmjs.com/package/@wordpress/scripts)** - Block development tools
- **WordPress REST API** - Custom endpoints for AI and media

### Client-Side Routing
- **[Swup 4.8.2](https://swup.js.org/)** - PJAX navigation with SEO optimizations
  - Head Plugin - Meta tags, title, Open Graph updates
  - Scripts Plugin - Re-run scripts on page change
  - Body Class Plugin - Update body classes
  - Progress Plugin - Loading indicator
  - A11y Plugin - Screen reader announcements

### AI Integration
- **Google Gemini 2.0 Flash** - AI content generation and SEO optimization

### Development Tools
- **[Biome 2.2.0](https://biomejs.dev/)** - Fast linter and formatter
- **[Playwright](https://playwright.dev/)** - End-to-end testing
- **[JSDOM](https://github.com/jsdom/jsdom)** - DOM testing environment
- **[Rimraf](https://www.npmjs.com/package/rimraf)** - Cross-platform file cleanup

---

## Project Structure

```
dev-theme/
├── assets/
│   └── src/
│       ├── js/
│       │   ├── main.js                          # Main entry point, Swup init
│       │   ├── navigation.js                    # Mobile menu, dropdowns
│       │   ├── bootstrap-components.js          # Bootstrap JS modules
│       │   ├── swup-init.js                     # Swup configuration
│       │   ├── audio-manager.js                 # Radio player logic
│       │   ├── soundcloud-custom-player.js      # SoundCloud player
│       │   ├── youtube-video-player.js          # YouTube player
│       │   ├── facebook-video-player.js         # Facebook video player
│       │   └── ai-content-helper.js             # AI sidebar panel for editor
│       └── scss/
│           ├── main.scss                        # Main SCSS entry
│           ├── abstracts/                       # Variables, mixins
│           ├── base/                            # Reset, typography, fonts
│           │   └── _swup-transitions.scss       # Page transition styles
│           ├── components/                      # UI components
│           │   ├── _buttons.scss
│           │   ├── _modal.scss
│           │   ├── _header-play-btn.scss
│           │   └── ai-content-helper.scss
│           ├── layout/                          # Layout components
│           │   ├── _header.scss
│           │   ├── _footer.scss
│           │   ├── _navigation.scss
│           │   └── _grid.scss
│           ├── pages/                           # Page-specific styles
│           ├── settings/                        # Functions, mixins, variables
│           └── vendors/                         # Bootstrap overrides
│
├── blocks/                                      # Custom Gutenberg Blocks
│   ├── soundcloud/
│   │   ├── index.jsx                            # Block editor component
│   │   ├── block.json                           # Block metadata
│   │   └── render.php                           # Server-side rendering
│   ├── facebook-video/
│   │   ├── index.jsx
│   │   ├── block.json
│   │   └── render.php
│   └── youtube-video/
│       ├── index.jsx
│       ├── block.json
│       └── render.php
│
├── components/                                  # Modular PHP components
│   ├── radio-player/
│   │   ├── radio-player.php                     # Persistent bottom player
│   │   └── radio-player.scss
│   ├── soundcloud-custom-player/
│   │   ├── soundcloud-custom-player.php
│   │   └── soundcloud-custom-player.scss
│   ├── youtube-video-player/
│   │   ├── youtube-video-player.php
│   │   └── youtube-video-player.scss
│   ├── facebook-video-player/
│   │   ├── facebook-video-player.php
│   │   └── facebook-video-player.scss
│   ├── featured-image/
│   ├── post-title/
│   ├── post-date/
│   ├── post-terms/
│   └── author/
│
├── configure/                                   # Theme configuration modules
│   ├── configure.php                            # Main theme setup
│   ├── js-css.php                               # Vite asset enqueuing
│   ├── blocks.php                               # Block registration
│   ├── components.php                           # Component system
│   ├── images.php                               # Image size registration
│   ├── ai-content-generator.php                 # AI REST API endpoints
│   ├── ai-settings.php                          # AI settings admin page
│   ├── ai-enqueue.php                           # AI script enqueuing
│   ├── cpt-obavijesti-o-smrti.php              # Death notices CPT
│   ├── cpt-taxonomy.php                         # Custom taxonomies
│   ├── utilities.php                            # Helper functions
│   ├── user-profile.php                         # User profile extensions
│   ├── admin.php                                # Admin customizations
│   ├── acf.php                                  # ACF configuration
│   ├── shortcodes.php                           # Custom shortcodes
│   └── class-bootstrap-nav-walker.php           # Bootstrap menu walker
│
├── inc/                                         # Image processing utilities
│   ├── image-processor.php                      # WebP conversion, optimization
│   ├── image-helper.php                         # Helper functions
│   └── image-fallbacks.php                      # Fallback mechanisms
│
├── dist/                                        # Built assets (auto-generated)
│   ├── js/
│   ├── css/
│   ├── blocks/
│   └── .vite/
│       └── manifest.json                        # Asset manifest
│
├── tests/                                       # Test files
│   ├── setup.js                                 # Test configuration
│   └── example.test.js
│
├── Template Files
│   ├── header.php                               # Theme header with Swup
│   ├── footer.php                               # Theme footer
│   ├── index.php                                # Main template
│   ├── single.php                               # Single post
│   ├── category.php                             # Category archive
│   ├── tag.php                                  # Tag archive
│   ├── 404.php                                  # 404 page
│   ├── archive-obavijesti-o-smrti.php          # Death notices archive
│   ├── single-obavijesti-o-smrti.php           # Death notice single
│   ├── archive-servicne-informacije.php        # Service info archive
│   └── single-servicne-informacije.php         # Service info single
│
├── Configuration Files
│   ├── functions.php                            # Theme entry point
│   ├── style.css                                # Theme metadata
│   ├── vite.config.js                           # Vite configuration
│   ├── package.json                             # Dependencies & scripts
│   ├── biome.json                               # Biome linter config
│   └── playwright.config.ts                     # E2E testing config
│
└── README.md                                    # Theme documentation
```

---

## Build System

### Vite Configuration ([vite.config.js](vite.config.js))

**Key Features:**
- Hot Module Replacement (HMR) for PHP, JS, and SCSS
- PurgeCSS for production CSS optimization
- React plugin for JSX support
- Multi-entry point compilation
- Asset organization (fonts, images, CSS in separate folders)

**Entry Points:**
```javascript
// JavaScript
'js/main' → main.js (Swup, Bootstrap)
'js/navigation' → navigation.js
'js/bootstrap-components' → Bootstrap modules
'js/soundcloud-custom-player'
'js/facebook-video-player'
'js/youtube-video-player'
'js/ai-content-helper' → AI editor panel

// CSS
'main' → main.scss (Global styles)
'components/radio-player'
'components/soundcloud-custom-player'
'components/facebook-video-player'
'components/youtube-video-player'
'components/ai-content-helper'
'components/featured-image'
'components/post-title'
'components/post-date'
'components/author'
'components/post-terms'
```

**PurgeCSS Safelist:**
- Bootstrap: `/^btn/`, `/^nav/`, `/^dropdown/`, `/^modal/`, etc.
- WordPress: `/^wp-/`, `/^post-/`, `/^page-/`, `/^comment-/`, etc.
- Utilities: `/^d-/`, `/^text-/`, `/^bg-/`, `/^border-/`, `/^p-/`, `/^m-/`

**Development Server:**
- Port: 5173
- CORS: Enabled for WordPress integration
- PHP Hot Reload: Full page reload on .php changes

---

## WordPress Integration

### Theme Setup ([configure/configure.php](configure/configure.php))

**Registered Features:**
- Navigation menus (desktop, mobile)
- Post thumbnails
- Title tag support
- HTML5 markup
- Custom image sizes

**Performance Optimizations:**
- ✅ Removed WP emoji scripts
- ✅ Removed global styles
- ✅ Removed wp-embed.js
- ✅ Disabled jQuery Migrate (uses jQuery 3.6.1 from CDN)
- ✅ SVG upload support
- ✅ Disabled update emails
- ✅ Removed unnecessary image sizes (1536x1536, 2048x2048)

**Block Control:**
Extensive block whitelist - only enables essential blocks:
- Text: Paragraph, Heading, Quote, Pullquote, Table, Details
- Media: Image only (core video/audio disabled)
- Layout: Columns, Spacer, Separator
- Custom: SoundCloud, Facebook Video, YouTube Video

File: [configure/configure.php:116-260](configure/configure.php#L116-L260)

### Asset Loading ([configure/js-css.php](configure/js-css.php))

**Vite Integration:**
```php
define('VITE_SERVER', 'http://localhost:5173');
define('VITE_BUILD', file_exists(DIST_PATH . '/.vite/manifest.json'));
```

**Development vs Production:**
- Development: Loads from Vite dev server (HMR enabled)
- Production: Loads from manifest.json (optimized, hashed filenames)

**Module Scripts:**
All scripts use `type="module"` with Swup integration:
```php
<script type="module" data-swup-ignore-script src="...">
```

---

## AI Content Generation

### Features
Google Gemini 2.0 Flash integration for SEO-optimized content generation:

1. **Generate Title** - Creates SEO-optimized title from content (50-60 chars)
2. **Generate Excerpt** - Creates meta description (150-160 chars)
3. **Optimize Title** - Improves existing title for SEO
4. **Optimize Excerpt** - Enhances existing excerpt

### Implementation Files

**Backend:**
- [configure/ai-content-generator.php](configure/ai-content-generator.php) - REST API endpoints
- [configure/ai-settings.php](configure/ai-settings.php) - Admin settings page
- [configure/ai-enqueue.php](configure/ai-enqueue.php) - Script enqueuing

**Frontend:**
- [assets/src/js/ai-content-helper.js](assets/src/js/ai-content-helper.js) - WordPress editor sidebar panel

### REST API Endpoints

```
POST /wp-json/dev-theme/v1/ai/generate-title
POST /wp-json/dev-theme/v1/ai/generate-excerpt
POST /wp-json/dev-theme/v1/ai/optimize-title
POST /wp-json/dev-theme/v1/ai/optimize-excerpt
```

**Request Format:**
```json
{
  "content": "Blog post content...",
  "title": "Current title (for optimization)",
  "excerpt": "Current excerpt (for optimization)"
}
```

**Response Format:**
```json
{
  "success": true,
  "title": "Generated title",
  "excerpt": "Generated excerpt",
  "length": 55
}
```

### AI Configuration

**Settings Page:** WordPress Admin → Settings → AI Sadržaj

**Required:**
- Google Gemini API Key (free tier: 60 requests/minute)
- Get key at: [Google AI Studio](https://makersuite.google.com/app/apikey)

**API Settings:**
```php
$api_endpoint = 'https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash:generateContent';
'temperature' => 0.7
'maxOutputTokens' => 100
```

**Editor Integration:**
- Sidebar panel in WordPress block editor
- 4 buttons: Generate Title, Optimize Title, Generate Excerpt, Optimize Excerpt
- Toast notifications for success/error
- Character count display

File: [assets/src/js/ai-content-helper.js:181-246](assets/src/js/ai-content-helper.js#L181-L246)

---

## Custom Gutenberg Blocks

### 1. SoundCloud Block (`dev-theme/soundcloud`)

**Features:**
- Fetches latest tracks from SoundCloud RSS feed
- Interactive track selection in editor
- Visual/standard player modes
- Customizable height

**Files:**
- [blocks/soundcloud/index.jsx](blocks/soundcloud/index.jsx) - React editor component
- [blocks/soundcloud/block.json](blocks/soundcloud/block.json) - Block metadata
- [blocks/soundcloud/render.php](blocks/soundcloud/render.php) - Frontend rendering
- [components/soundcloud-custom-player/](components/soundcloud-custom-player/) - Custom player component

**REST API:**
```
GET /wp-json/dev-theme/v1/soundcloud-tracks
```
Returns last 20 tracks from user ID: 61105252 (Radio Velika Kladuša)

**Attributes:**
```json
{
  "url": "https://soundcloud.com/...",
  "height": "166",
  "visual": false
}
```

### 2. YouTube Video Block (`dev-theme/youtube-video`)

**Features:**
- YouTube video embedding
- Responsive iframe
- Custom player wrapper

**Files:**
- [blocks/youtube-video/index.jsx](blocks/youtube-video/index.jsx)
- [blocks/youtube-video/block.json](blocks/youtube-video/block.json)
- [blocks/youtube-video/render.php](blocks/youtube-video/render.php)
- [components/youtube-video-player/](components/youtube-video-player/)

**Attributes:**
```json
{
  "videoUrl": "https://www.youtube.com/watch?v=..."
}
```

### 3. Facebook Video Block (`dev-theme/facebook-video`)

**Features:**
- Facebook video embedding
- Facebook SDK integration
- Responsive video player

**Files:**
- [blocks/facebook-video/index.jsx](blocks/facebook-video/index.jsx)
- [blocks/facebook-video/block.json](blocks/facebook-video/block.json)
- [blocks/facebook-video/render.php](blocks/facebook-video/render.php)
- [components/facebook-video-player/](components/facebook-video-player/)

**Attributes:**
```json
{
  "videoUrl": "https://www.facebook.com/watch/..."
}
```

### Block Registration

All blocks are registered in [configure/blocks.php](configure/blocks.php):

```php
register_block_type(get_template_directory() . '/blocks/soundcloud/block.json');
register_block_type(get_template_directory() . '/blocks/facebook-video/block.json');
register_block_type(get_template_directory() . '/blocks/youtube-video/block.json');
```

**Build Process:**
```bash
wp-scripts build blocks/soundcloud/index.jsx --output-path=dist/blocks/soundcloud
wp-scripts build blocks/facebook-video/index.jsx --output-path=dist/blocks/facebook-video
wp-scripts build blocks/youtube-video/index.jsx --output-path=dist/blocks/youtube-video
```

---

## Component System

### Architecture ([configure/components.php](configure/components.php))

Modular, performance-optimized component system with conditional asset loading.

**Key Functions:**

```php
// Register component assets (CSS/JS)
register_component_assets('component-name', $has_css, $has_js);

// Mark component as used and enqueue assets
use_component('component-name');

// Get and render component
get_component('component-name', ['arg1' => 'value']);
```

### Usage Example

```php
// In template file
get_component('featured-image', [
    'post_id' => get_the_ID(),
    'size' => 'large'
]);
```

**Automatic Asset Loading:**
- Component SCSS/JS only loaded when component is used
- Prevents unnecessary HTTP requests
- Better performance

### Available Components

| Component | CSS | JS | Purpose |
|-----------|-----|-----|---------|
| featured-image | ✅ | ❌ | Post featured image |
| post-title | ✅ | ❌ | Post title with styling |
| post-date | ✅ | ❌ | Formatted post date |
| post-terms | ✅ | ❌ | Post categories/tags |
| author | ✅ | ❌ | Author info box |
| radio-player | ✅ | ❌ | Persistent radio player |
| soundcloud-custom-player | ✅ | ❌ | SoundCloud embed |
| youtube-video-player | ✅ | ❌ | YouTube embed |
| facebook-video-player | ✅ | ❌ | Facebook video embed |

---

## Image Optimization

### Custom Image Sizes ([configure/images.php](configure/images.php))

```php
add_image_size('desktop', 1440, 9999, false);        // Large desktop
add_image_size('tablet', 1024, 9999, false);         // Tablets
add_image_size('mobile', 640, 9999, false);          // Smartphones
add_image_size('mobile-small', 480, 9999, false);    // Older phones
add_image_size('thumb', 100, 100, true);             // Cropped thumbnails
```

### Configuration

```php
define('DEV_THEME_WEBP_QUALITY', 85);
define('DEV_THEME_JPEG_QUALITY', 80);
define('DEV_THEME_PNG_QUALITY', 80);
define('DEV_THEME_ENABLE_WEBP', true);
define('DEV_THEME_ENABLE_OPTIMIZATION', true);
define('DEV_THEME_ENABLE_LAZY_LOAD', true);
```

### Supported Formats

**Processed:**
- JPEG (`image/jpeg`)
- PNG (`image/png`)

**Excluded from processing:**
- SVG, GIF, WebP, AVIF, ICO (served as-is)

### Image Processing ([inc/image-processor.php](inc/image-processor.php))

**Features:**
- WebP conversion with fallback
- Quality optimization
- Automatic cleanup of old images
- GD library forced (fixes XAMPP Imagick issues)

**XAMPP Fix:**
```php
function dev_theme_force_gd_editor($editors) {
    return array('WP_Image_Editor_GD'); // Remove Imagick
}
add_filter('wp_image_editors', 'dev_theme_force_gd_editor');
```

---

## Swup.js Integration

### Features ([assets/src/js/swup-init.js](assets/src/js/swup-init.js))

**PJAX Navigation with SEO:**
- No full page reloads
- Updates meta tags, title, Open Graph
- Updates canonical URLs
- Screen reader announcements
- Progress indicator
- Body class updates

**Plugins:**
```javascript
new SwupHeadPlugin()           // Meta tags, title updates
new SwupScriptsPlugin()        // Re-run scripts
new SwupBodyClassPlugin()      // Body class updates
new SwupProgressPlugin()       // Loading indicator
new SwupA11yPlugin()           // Accessibility
```

### Analytics Integration

**Google Analytics 4:**
```javascript
gtag('event', 'page_view', {
    page_path: window.location.pathname,
    page_title: document.title
});
```

**Google Tag Manager:**
```javascript
dataLayer.push({
    event: 'pageview',
    page: { path, title, url }
});
```

### Template Integration

**header.php:**
```html
<div class="swup-overlay"></div>
<div id="swup" class="transition-fade">
```

**Main script:**
```javascript
<script type="module" data-swup-ignore-script src="main.js">
```

### CSS Transitions ([assets/src/scss/base/_swup-transitions.scss](assets/src/scss/base/_swup-transitions.scss))

Smooth fade transitions between pages with overlay effect.

---

## Media Players

### 1. Radio Player (Persistent)

**Location:** [components/radio-player/radio-player.php](components/radio-player/radio-player.php)

**Features:**
- Fixed bottom bar player
- Live streaming from: `https://rvk2021.radioca.st/stream`
- Play/Pause controls
- Volume control
- Live indicator with animated dot
- Current track display
- Persistent across page navigation (Swup compatible)

**Controls:**
- Header Play Button (`#header-play-btn`)
- Bottom Bar Play Button (`#radio-play-btn`)
- Volume Slider (`#radio-volume`)

### 2. SoundCloud Custom Player

**Location:** [components/soundcloud-custom-player/](components/soundcloud-custom-player/)

**Features:**
- Custom styled SoundCloud embeds
- Visual/standard modes
- Responsive iframe
- Automatic height adjustment

### 3. YouTube Video Player

**Location:** [components/youtube-video-player/](components/youtube-video-player/)

**Features:**
- YouTube iframe API integration
- Responsive container
- Custom controls wrapper

### 4. Facebook Video Player

**Location:** [components/facebook-video-player/](components/facebook-video-player/)

**Features:**
- Facebook SDK integration
- Responsive video container
- Auto-initialization on page load

---

## Custom Post Types

### 1. Obavijesti o Smrti (Death Notices)

**File:** [configure/cpt-obavijesti-o-smrti.php](configure/cpt-obavijesti-o-smrti.php)

**Slug:** `obavijesti-o-smrti`

**Features:**
- ⏰ **Auto-deletion after 42 days**
- Custom taxonomy: `obavijest_tag`
- REST API enabled
- Supports: Title, Editor, Thumbnail, Excerpt, Custom Fields, Revisions

**Auto-Deletion System:**

**Cron Schedule:**
```php
wp_schedule_event(strtotime('tomorrow 02:00:00'), 'daily', 'obavijesti_daily_cleanup');
```

**Cleanup Function:**
```php
function delete_old_obavijesti() {
    // Deletes posts older than 42 days
    // Runs daily at 02:00 AM
    // Permanently deletes (not trash)
}
```

**REST API Fields:**

```json
{
  "days_remaining": 35,           // Calculated field
  "is_expiring_soon": false,      // True if ≤7 days left
  "author_name": "John Doe",      // Full name
  "author_avatar": "https://..."  // Avatar URL (96px)
}
```

**Template Files:**
- [archive-obavijesti-o-smrti.php](archive-obavijesti-o-smrti.php) - Archive
- [single-obavijesti-o-smrti.php](single-obavijesti-o-smrti.php) - Single post

### 2. Servicne Informacije (Service Information)

**Files:**
- [archive-servicne-informacije.php](archive-servicne-informacije.php)
- [single-servicne-informacije.php](single-servicne-informacije.php)

---

## Styling Architecture

### SASS Structure (7-1 Pattern)

```
assets/src/scss/
├── main.scss                    # Main entry (imports all)
├── abstracts/                   # Variables, mixins
│   └── _variables.scss
├── base/                        # Base styles
│   ├── _reset.scss
│   ├── _typography.scss
│   ├── _fonts.scss
│   ├── _base.scss
│   ├── _utilities.scss
│   └── _swup-transitions.scss
├── components/                  # UI components
│   ├── _buttons.scss
│   ├── _modal.scss
│   ├── _header-play-btn.scss
│   └── ai-content-helper.scss
├── layout/                      # Layout components
│   ├── _header.scss
│   ├── _footer.scss
│   ├── _navigation.scss
│   ├── _grid.scss
│   └── _forms.scss
├── pages/                       # Page-specific
│   ├── _front-page.scss
│   └── _page.scss
├── settings/                    # Settings
│   ├── _functions.scss
│   ├── _mixins.scss
│   └── _variables.scss
└── vendors/                     # Third-party
    ├── _bootstrap.scss
    └── _bootstrap-overrides.scss
```

### Bootstrap Customization

**Import Bootstrap modules:** [assets/src/scss/vendors/_bootstrap.scss](assets/src/scss/vendors/_bootstrap.scss)

**Override variables:** [assets/src/scss/vendors/_bootstrap-overrides.scss](assets/src/scss/vendors/_bootstrap-overrides.scss)

**JavaScript Modules:**
```javascript
// assets/src/js/bootstrap-components.js
import Dropdown from 'bootstrap/js/dist/dropdown';
import Collapse from 'bootstrap/js/dist/collapse';
import Modal from 'bootstrap/js/dist/modal';
```

---

## Development Workflow

### NPM Scripts ([package.json](package.json))

```bash
# Development
npm run dev              # Start Vite dev server with HMR

# Production Build
npm run build            # Build Vite assets + WordPress blocks
npm run build:vite       # Build only Vite assets
npm run build:blocks     # Build only Gutenberg blocks

# Preview
npm run preview          # Preview production build

# Testing
npm run test             # Run tests in watch mode
npm run test:ui          # Run tests with visual UI
npm run test:run         # Run tests once (CI)
npm run test:coverage    # Run with coverage report
```

### Development Server

**Start development:**
```bash
npm run dev
```

**Access:**
- Vite Server: http://localhost:5173 (HMR)
- WordPress Site: Your local WordPress URL

**Hot Reload:**
- ✅ JavaScript files (.js)
- ✅ SCSS files (.scss)
- ✅ PHP files (full page reload)
- ✅ React components (.jsx)

### Production Build

**Build for production:**
```bash
npm run build
```

**Output:**
```
dist/
├── js/
│   ├── main.js
│   ├── navigation.js
│   ├── bootstrap-components.js
│   ├── soundcloud-custom-player.js
│   ├── facebook-video-player.js
│   ├── youtube-video-player.js
│   └── ai-content-helper.js
├── css/
│   ├── main.css
│   └── components/
│       ├── radio-player.css
│       ├── soundcloud-custom-player.css
│       ├── facebook-video-player.css
│       ├── youtube-video-player.css
│       ├── ai-content-helper.css
│       └── ...
├── blocks/
│   ├── soundcloud/
│   ├── facebook-video/
│   └── youtube-video/
└── .vite/
    └── manifest.json
```

### Testing Setup ([tests/setup.js](tests/setup.js))

**Vitest Configuration:**
- Environment: JSDOM
- Globals: Enabled
- WordPress mocks: `wp`, `jQuery` objects

**Run tests:**
```bash
npm run test          # Watch mode
npm run test:ui       # Visual interface
npm run test:coverage # With coverage
```

---

## Configuration & Setup

### 1. Install Dependencies

```bash
cd wp-content/themes/dev-theme
npm install
```

### 2. Activate Theme

WordPress Admin → Appearance → Themes → Activate "AntsNet"

### 3. Configure Menus

WordPress Admin → Appearance → Menus

Create two menus:
- **Desktop Menu** → Assign to "Desktop Menu" location
- **Mobile Menu** → Assign to "Mobile Menu" location

### 4. AI Content Generation (Optional)

**Step 1:** Get Gemini API Key
- Visit: [Google AI Studio](https://makersuite.google.com/app/apikey)
- Sign in with Google account
- Click "Create API Key"
- Copy the key

**Step 2:** Configure in WordPress
- WordPress Admin → Settings → AI Sadržaj
- Paste API key
- Enable AI functions
- Save settings

**Step 3:** Use in Editor
- Create/Edit post
- Look for "✨ AI Sadržaj" panel in sidebar (right side)
- Click buttons to generate/optimize content

### 5. Upload Logo (Optional)

Configure logo via WordPress Customizer or add to theme settings.

### 6. Image Optimization

Automatic - no configuration needed.

**Settings:**
- WebP conversion: Enabled
- JPEG quality: 80%
- PNG quality: 80%
- WebP quality: 85%

### 7. Development vs Production

**Development Mode:**
```bash
npm run dev
```
- Assets loaded from: http://localhost:5173
- HMR enabled
- Source maps enabled

**Production Mode:**
```bash
npm run build
```
- Assets loaded from: `/dist/`
- Minified and optimized
- Cache-busting hashes
- PurgeCSS applied

**Detection:**
```php
define('VITE_BUILD', file_exists(DIST_PATH . '/.vite/manifest.json'));
```

---

## Features Summary

### ✅ Core Features
- Modern WordPress theme built with Vite.js
- Bootstrap 5 framework (fully customizable)
- SASS preprocessor with 7-1 architecture
- ES6+ JavaScript with module support
- Hot Module Replacement (HMR)
- Production CSS optimization (PurgeCSS)

### ✅ Performance Optimizations
- Vite.js ultra-fast builds
- Asset code splitting
- Lazy loading support
- WebP image conversion
- Responsive image sizes (desktop, tablet, mobile)
- Removed unnecessary WordPress scripts
- Conditional component loading
- Cache-busting with hashed filenames

### ✅ AI Content Generation
- Google Gemini 2.0 Flash integration
- Generate SEO-optimized titles
- Generate meta descriptions/excerpts
- Optimize existing content
- WordPress editor sidebar integration
- Character count validation
- Toast notifications

### ✅ Client-Side Routing
- Swup.js PJAX navigation
- Smooth page transitions
- SEO-friendly (meta tags, Open Graph)
- Google Analytics integration
- Accessibility announcements
- Progress indicator

### ✅ Custom Gutenberg Blocks
- SoundCloud player block
- YouTube video block
- Facebook video block
- Interactive editor UI
- Server-side rendering
- Custom styling

### ✅ Media Players
- Persistent radio player (bottom bar)
- Live streaming support
- Volume controls
- Custom SoundCloud player
- YouTube player integration
- Facebook video player

### ✅ Custom Post Types
- Death Notices (Obavijesti o Smrti)
  - Auto-deletion after 42 days
  - Daily cron at 02:00 AM
  - Custom taxonomy support
  - REST API fields (days_remaining, author info)
- Service Information (Servicne Informacije)

### ✅ Component System
- Modular PHP components
- Conditional asset loading
- Performance optimized
- Reusable across templates

### ✅ Image Optimization
- Custom responsive sizes
- WebP conversion
- Quality optimization
- XAMPP/GD compatibility fix
- Lazy loading ready

### ✅ Developer Experience
- Vitest for unit testing
- Playwright for E2E testing
- Biome for linting/formatting
- JSDOM for DOM testing
- WordPress mocks for testing
- TypeScript support

### ✅ SEO & Accessibility
- Semantic HTML5 markup
- ARIA labels and roles
- Screen reader support
- Meta tags optimization
- Open Graph ready
- Canonical URLs
- Accessibility-ready

### ✅ Security
- HTTPS enforcement
- Nonce verification
- Input sanitization
- Direct access prevention
- Permission checks

### ✅ WordPress Integration
- Theme customizer support
- Navigation menus (desktop/mobile)
- Post thumbnails
- Custom image sizes
- Widget areas ready
- Translation ready

---

## Browser Support

- Chrome/Edge (latest 2 versions)
- Firefox (latest 2 versions)
- Safari (latest 2 versions)
- Modern mobile browsers

---

## Requirements

- Node.js v22+
- WordPress 5.0+
- PHP 7.4+
- MySQL 5.7+ / MariaDB 10.3+

---

## Troubleshooting

### Vite Dev Server Not Loading
- Ensure port 5173 is available
- Check `VITE_SERVER` constant in [configure/js-css.php](configure/js-css.php)
- Run `npm run dev` before accessing WordPress site

### Assets Not Loading in Production
- Run `npm run build` to generate production assets
- Check that `dist/.vite/manifest.json` exists
- Clear WordPress cache

### Image Optimization Issues (XAMPP)
- GD library is forced (Imagick disabled)
- Check PHP GD extension is enabled
- Verify file permissions on `wp-content/uploads`

### AI Features Not Working
- Verify Gemini API key is configured
- Check API key has not exceeded rate limits (60/min free tier)
- Check browser console for errors
- Verify REST API endpoints are accessible

### Swup Navigation Issues
- Ensure `#swup` container exists in all templates
- Check scripts have `data-swup-ignore-script` attribute
- Verify Swup plugins are loaded correctly

---

## Credits

**Developer:** Alen Melkić
**Website:** [antsnet.site](https://antsnet.site)
**Theme:** Tailored for Radio Velika Kladuša

**Technologies:**
- Vite.js - Evan You
- Bootstrap - Twitter
- WordPress - Automattic
- Swup.js - Swup Contributors
- Google Gemini - Google AI

---

## License

This theme is licensed under [GPL v2 or later](https://www.gnu.org/licenses/gpl-2.0.html).

---

**Last Updated:** December 5, 2025
**Version:** 1.0.0
**Documentation Generated by:** Claude (Anthropic)
