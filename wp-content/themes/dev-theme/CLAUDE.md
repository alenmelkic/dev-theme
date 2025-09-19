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
- `assets/src/js/` - Source JavaScript files
- `assets/src/scss/` - Source SCSS files
- `assets/dist/` - Compiled assets (auto-generated)
- `tests/` - Vitest test files

## Technologies Used

### Build Tools
- **Vite.js** - Main build tool and dev server
- **Vitest** - Testing framework
- **PurgeCSS** - CSS optimization for production
- **SASS** - CSS preprocessor

### Frontend
- **Bootstrap 5** - CSS framework
- **ES6+** - Modern JavaScript
- **JSDOM** - DOM testing environment

### WordPress Integration
- Modern PHP template structure
- Vite manifest integration for asset loading
- Accessibility and SEO optimizations

## Configuration Details

### Vite Configuration Features
- Hot Module Replacement (HMR) for PHP, JS, SCSS
- PurgeCSS with WordPress and Bootstrap safelist
- SASS deprecation warnings silenced
- Asset optimization (images, fonts, etc.)
- Development server on port 5173

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

## Development Workflow

1. **Start Development**: `npm run dev`
2. **Make Changes**: Edit PHP, SCSS, or JS files
3. **Test**: `npm run test` (optional but recommended)
4. **Build**: `npm run build` for production

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
- `assets/src/**/*.scss` - SCSS source files

## Build Output

Production build creates:
- Hashed JS/CSS files in `assets/dist/`
- Manifest file for WordPress asset loading
- Optimized images and fonts
- Purged CSS (unused styles removed)

## Notes for Claude Code

- Always run tests before committing changes
- Use `npm run build` to verify production build works
- PurgeCSS configuration may need adjustment for new CSS classes
- WordPress-specific functionality requires local WordPress installation
- Hot reload works for all file types during development