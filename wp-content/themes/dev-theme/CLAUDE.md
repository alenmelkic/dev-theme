# Claude Code - WordPress Vite Theme

This file contains commands and information specifically for Claude Code to understand this WordPress theme's development environment.

## Development Commands

### Primary Commands
```bash
npm run dev          # Start development server with HMR
npm run build        # Build for production
npm run preview      # Preview production build
```

### Testing Commands
```bash
npm run test         # Run tests in watch mode
npm run test:ui      # Run tests with visual interface (opens browser)
npm run test:run     # Run tests once
npm run test:coverage # Run tests with coverage report
```

### Linting & Quality
```bash
npm run lint         # [Not configured yet - add if needed]
npm run typecheck    # [Not configured yet - add if needed]
```

## Project Structure

### Key Files
- `vite.config.js` - Main build configuration
- `package.json` - Dependencies and scripts
- `header.php` - WordPress header template
- `functions.php` - WordPress theme functions
- `style.css` - WordPress theme metadata

### Asset Directories
- `assets/src/js/` - Source JavaScript files (vanilla JS)
- `assets/src/react/` - React components and Gutenberg blocks
  - `assets/src/react/components/` - Reusable React components (Header, Footer, etc.)
  - `assets/src/react/app/` - Full page React application entry point
  - `assets/src/react/blocks/` - Custom Gutenberg blocks
  - `assets/src/react/hooks/` - Custom React hooks
  - `assets/src/react/utils/` - React utility functions
- `assets/src/scss/` - Source SCSS files
  - `main.scss` - Main stylesheet (Bootstrap + base)
  - `react-components.scss` - React component-specific styles
  - `*.module.scss` - CSS modules for scoped component styles
- `dist/` - Compiled assets (auto-generated)
- `tests/` - Vitest test files

## Technologies Used

### Build Tools
- **Vite.js** - Main build tool and dev server
- **Vitest** - Testing framework
- **PurgeCSS** - CSS optimization for production
- **SASS** - CSS preprocessor

### Frontend
- **React 18** - Component-based UI library
- **WordPress Gutenberg** - Block editor integration
- **Bootstrap 5** - CSS framework
- **ES6+** - Modern JavaScript
- **JSX** - React syntax extension
- **JSDOM** - DOM testing environment

### WordPress Integration
- Modern PHP template structure
- Vite manifest integration for asset loading
- Accessibility and SEO optimizations

## Configuration Details

### Vite Configuration Features
- Hot Module Replacement (HMR) for PHP, JS, JSX, SCSS
- React plugin for JSX transformation and fast refresh
- PurgeCSS with WordPress, Bootstrap, and React component safelist
- SASS deprecation warnings silenced
- Asset optimization (images, fonts, etc.)
- Development server on port 5173
- Separate entry points for React components and Gutenberg blocks

### PurgeCSS Safelist
Preserves these classes in production:
- Bootstrap: `btn-*`, `nav-*`, `dropdown-*`, `modal-*`, etc.
- WordPress: `wp-*`, `post-*`, `page-*`, `comment-*`, etc.
- Utilities: `d-*`, `text-*`, `bg-*`, `border-*`, `p-*`, `m-*`

### Testing Setup
- JSDOM environment for DOM testing
- WordPress globals mocked (wp, jQuery)
- Automatic DOM cleanup between tests
- Setup file: `tests/setup.js`

## React/Gutenberg Development

### React Components Structure
The theme uses React for modern, interactive frontend components:

- **Header Component** (`assets/src/react/components/Header.jsx`)
  - Dynamic navigation menu
  - Responsive mobile menu
  - Site branding integration
  - WordPress data integration

- **Footer Component** (`assets/src/react/components/Footer.jsx`)
  - Dynamic widget areas
  - Social media links
  - Copyright information
  - Multi-column layout support

### WordPress Data Integration
React components receive data from WordPress via `wp_localize_script()`:

```javascript
// Available in React components as window.wpReactData
{
  siteName: 'Site Name',
  tagline: 'Site Description',
  homeUrl: '/',
  isHome: true/false,
  currentYear: 2024,
  menuItems: [...], // Navigation menu items
  footerWidgets: [...], // Footer widget data
  socialLinks: [...] // Social media links
}
```

### Gutenberg Block Development
Custom blocks are located in `assets/src/react/blocks/`:

- Entry point: `assets/src/react/blocks/index.jsx`
- Each block should be in its own subfolder
- Use WordPress block API with React components
- Register blocks with `registerBlockType()`

### Progressive Enhancement
- React components enhance existing PHP templates
- Fallback PHP templates work when JavaScript is disabled
- SEO-friendly server-side rendering for critical content
- Client-side React takes over for interactive features

## Development Workflow

1. **Start Development**: `npm run dev`
2. **Make Changes**: Edit PHP, SCSS, JS, or JSX files
3. **React Development**: Components auto-reload with fast refresh
4. **Test**: `npm run test` (optional but recommended)
5. **Build**: `npm run build` for production

## Common Tasks

### Adding New Dependencies
```bash
npm install [package-name]          # Runtime dependency
npm install --save-dev [package]    # Development dependency
```

### Adding New Tests
- Create `.test.js` files in `tests/` directory
- Import from `vitest` for test functions
- Use JSDOM for DOM manipulation testing

### SCSS Development
- Main entry: `assets/src/scss/main.scss`
- Follow 7-1 pattern (abstracts, base, components, layout, pages)
- Use `@` alias for static directory references

### JavaScript Development
- Main entry: `assets/src/js/main.js`
- ES6+ features supported
- Hot reload enabled during development

### React Development
- Components entry: `assets/src/react/components/index.jsx`
- Blocks entry: `assets/src/react/blocks/index.jsx`
- JSX transformation with fast refresh
- WordPress data available via `window.wpReactData`

### Creating New React Components
1. Create component file in `assets/src/react/components/`
2. Export component from `index.jsx`
3. Add container element to PHP template
4. Initialize component in `index.jsx`

### Creating New Gutenberg Blocks
1. Create block folder in `assets/src/react/blocks/`
2. Register block in `assets/src/react/blocks/index.jsx`
3. Use WordPress block API with React components

## Troubleshooting

### Development Server Issues
- Ensure port 5173 is available
- Check that `npm run dev` is running
- Verify Vite configuration in `vite.config.js`

### Asset Loading Problems
- Check if assets exist in `assets/dist/` after build
- Verify WordPress is loading the correct manifest
- Ensure theme is activated in WordPress admin

### Test Failures
- Check test setup in `tests/setup.js`
- Verify JSDOM environment is working
- Look for WordPress global mocking issues

### SCSS/CSS Issues
- Check for deprecation warnings (should be silenced)
- Verify PurgeCSS isn't removing needed classes
- Use browser dev tools to check compiled CSS

## File Watching

The development server watches these file types:
- `**/*.php` - WordPress template files
- `assets/src/**/*.js` - JavaScript source files
- `assets/src/react/**/*.{js,jsx}` - React component files
- `assets/src/**/*.scss` - SCSS source files

## Build Output

Production build creates:
- JS/CSS files in `dist/` (without hashes for easy integration)
  - `dist/js/main.js` - Main JavaScript
  - `dist/js/components.js` - React components bundle
  - `dist/js/app.js` - Full page React app bundle
  - `dist/js/blocks.js` - Gutenberg blocks bundle
  - `dist/css/main.css` - Main stylesheet (Bootstrap + base)
  - `dist/css/react-components.css` - React component styles
- Manifest file for WordPress asset loading
- Optimized images and fonts in respective folders
- Purged CSS (unused styles removed)
- React components with fast refresh in development

## Notes for Claude Code

- Always run tests before committing changes
- Use `npm run build` to verify production build works
- PurgeCSS configuration may need adjustment for new CSS classes or React components
- WordPress-specific functionality requires local WordPress installation
- Hot reload works for all file types during development
- React components use progressive enhancement - PHP fallbacks ensure accessibility
- Gutenberg blocks should follow WordPress block development best practices
- WordPress data is passed to React via `wp_localize_script()` in `configure/js-css.php`
- New React components need container elements in PHP templates
- Build outputs separate bundles for components and blocks for optimal loading

## React Development Approaches

This theme supports multiple React integration patterns:

### 1. Progressive Enhancement (Current Default)
- **Files**: `assets/src/react/components/index.jsx`
- **Usage**: Individual React components enhance existing PHP templates
- **Styles**: Global CSS classes + `react-components.css`
- **Best For**: Traditional WordPress sites with interactive elements

### 2. CSS Modules with React
- **Files**: `*.module.scss` files alongside components
- **Usage**: Scoped styles imported as objects (`styles.className`)
- **Benefits**: Style encapsulation, better maintainability
- **Example**: `HeaderWithModules.jsx` + `Header.module.scss`

### 3. Full Page React App
- **Files**: `assets/src/react/app/index.jsx`
- **Usage**: Entire page rendered by React via special template
- **Template**: `page-react.php` (assign to pages in WordPress admin)
- **Features**: WordPress REST API integration, client-side routing

### Quick Start Examples

**CSS Modules Component:**
```jsx
import styles from './Component.module.scss';
<div className={styles.wrapper}>Content</div>
```

**Full Page App:**
1. Create page in WordPress admin
2. Assign "React Full Page" template
3. Page renders entirely via React

See `REACT-APPROACHES.md` for detailed documentation.