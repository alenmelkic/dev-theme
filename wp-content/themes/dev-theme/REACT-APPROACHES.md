# React Integration Approaches

This WordPress theme supports multiple approaches for using React with SCSS modules.

## Approach 1: CSS Modules with React Components

### Setup
- **SCSS Files**: Create `.module.scss` files alongside components
- **Import**: Import styles as `styles` object in React components
- **Classes**: Use `styles.className` instead of string class names

### Example Structure
```
assets/src/react/components/
├── Header.jsx                 # Original component (global CSS)
├── Header.module.scss         # CSS module styles
├── HeaderWithModules.jsx      # Component using CSS modules
└── FullPageApp.jsx           # Full page React app
```

### CSS Module Example
```scss
// Header.module.scss
.header {
  background: #fff;
  padding: 1rem 0;

  .siteTitle {
    font-size: 1.5rem;

    a {
      color: #212529;
      text-decoration: none;

      &:hover {
        color: #0d6efd;
      }
    }
  }
}
```

### React Component Example
```jsx
// HeaderWithModules.jsx
import React from 'react';
import styles from './Header.module.scss';

const Header = () => {
  return (
    <header className={styles.header}>
      <h1 className={styles.siteTitle}>
        <a href="/">Site Name</a>
      </h1>
    </header>
  );
};
```

### Benefits
- **Scoped Styles**: CSS classes are automatically scoped to prevent conflicts
- **Type Safety**: Better IDE support and autocomplete
- **Component Co-location**: Styles live next to components
- **Hot Reload**: SCSS changes trigger component updates

## Approach 2: Full Page React App

### Setup
Instead of using React for individual components, render the entire page with React.

### Template Usage
1. Create a page in WordPress admin
2. Assign "React Full Page" template
3. The entire page renders via React

### Features
- **Complete React Control**: Full page lifecycle management
- **WordPress REST API**: Fetch content dynamically
- **Client-Side Routing**: Can implement SPA behavior
- **Progressive Enhancement**: Fallback for no-JS users

### File Structure
```
assets/src/react/
├── app/
│   └── index.jsx              # Full page app entry point
├── components/
│   ├── FullPageApp.jsx        # Main app component
│   ├── Header.jsx            # Header component
│   └── Footer.jsx            # Footer component
└── ...
```

### WordPress Integration
```php
// page-react.php - Special template
<?php
// Enqueue React app instead of components
wp_enqueue_script('app', '...');

// Provide WordPress data to React
wp_localize_script('app', 'wpReactData', [
    'siteName' => get_bloginfo('name'),
    'restUrl' => rest_url('wp/v2/'),
    'nonce' => wp_create_nonce('wp_rest')
]);
?>

<div id="react-app"></div>
```

## Configuration

### Vite Configuration
```js
// vite.config.js
export default defineConfig({
  css: {
    modules: {
      // CSS modules configuration
      generateScopedName: '[name]__[local]___[hash:base64:5]',
    },
  },
  build: {
    rollupOptions: {
      input: {
        'js/components': resolve('assets/src/react/components/index.jsx'),
        'js/app': resolve('assets/src/react/app/index.jsx'),
        // ... other entries
      }
    }
  }
});
```

### WordPress Enqueue
```php
// configure/js-css.php
$js_files = [
    'components' => 'assets/src/react/components/index.jsx', // Individual components
    'app' => 'assets/src/react/app/index.jsx',              // Full page app
];
```

## When to Use Each Approach

### CSS Modules Approach
- **Use When**: Building reusable components
- **Benefits**: Style encapsulation, better maintainability
- **Ideal For**: Component libraries, design systems

### Full Page React App
- **Use When**: Building SPA-like experiences
- **Benefits**: Complete control, modern React patterns
- **Ideal For**: Admin panels, dashboards, interactive pages

### Progressive Enhancement (Current)
- **Use When**: Enhancing existing WordPress themes
- **Benefits**: SEO-friendly, graceful degradation
- **Ideal For**: Marketing sites, blogs, traditional WordPress sites

## Development Workflow

### 1. Start Development Server
```bash
npm run dev
```

### 2. Choose Your Approach
```bash
# For component-based development
# Edit: assets/src/react/components/Header.jsx
# Styles: assets/src/react/components/Header.module.scss

# For full page apps
# Edit: assets/src/react/app/index.jsx
# Create: page-react.php template
```

### 3. Build for Production
```bash
npm run build
```

## CSS Module Class Naming

CSS modules generate scoped class names:
```scss
.header { /* becomes: Header__header___a1b2c */ }
.siteTitle { /* becomes: Header__siteTitle___d3e4f */ }
```

## WordPress REST API Integration

Full page apps can fetch WordPress content:
```jsx
// Fetch posts
const posts = await fetch('/wp-json/wp/v2/posts');

// Fetch pages
const pages = await fetch('/wp-json/wp/v2/pages');

// Fetch custom post types
const products = await fetch('/wp-json/wp/v2/products');
```

## Performance Considerations

### CSS Modules
- ✅ Smaller bundle sizes (only used styles)
- ✅ Better caching (styles with components)
- ✅ Automatic dead code elimination

### Full Page Apps
- ⚠️ Larger initial bundle
- ✅ Better for interactive experiences
- ✅ Can implement code splitting

## Migration Path

1. **Start**: Use current progressive enhancement approach
2. **Enhance**: Add CSS modules to existing components
3. **Upgrade**: Create full page templates for specific pages
4. **Scale**: Eventually migrate entire site to React (optional)

Both approaches can coexist in the same theme, allowing gradual migration.