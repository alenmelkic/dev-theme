/**
 * View your website at your own local server.
 * Example: if you're using WP-CLI then the common URL is: http://localhost:8080.
 *
 * http://localhost:5173 is serving Vite on development. Access this URL will show empty page.
 *
 */

import { defineConfig } from 'vite';
import { resolve } from 'path';
import { glob } from 'glob';
import PurgeCSS from 'vite-plugin-purgecss';
import react from '@vitejs/plugin-react';

// Get the relative path of the vite.config.js file for the alias
const fullPath = import.meta.url.slice(0, import.meta.url.lastIndexOf('/'));
const getWpContentIndex = fullPath.indexOf('wp-content');
const wpContentPath = fullPath.slice(getWpContentIndex);

export default defineConfig({
	base: './',

	// Optimize caching
	cacheDir: 'node_modules/.vite',

	test: {
		environment: 'jsdom',
		globals: true,
		setupFiles: './tests/setup.js',
	},

	// Optimize dependency pre-bundling
	optimizeDeps: {
		include: [
			'bootstrap',
			'photoswipe',
			'swup',
			'@swup/a11y-plugin',
			'@swup/body-class-plugin',
			'@swup/head-plugin',
			'@swup/progress-plugin',
			'@swup/scripts-plugin',
		],
		exclude: ['@wordpress/blocks', '@wordpress/components'],
	},

	plugins: [
		react(),
		{
			handleHotUpdate({ file, server }) {
				if (file.endsWith('.php')) {
					server.ws.send({ type: 'full-reload', path: '*' });
				}
			},
		},
		// Only run PurgeCSS in production builds
		process.env.NODE_ENV === 'production' && PurgeCSS({
			content: [
				// Only scan theme PHP files, not all plugins/vendor
				'./components/**/*.php',
				'./configure/**/*.php',
				'./inc/**/*.php',
				'./*.php',
				'./assets/src/js/**/*.js',
				'./assets/src/react/**/*.{js,jsx,ts,tsx}',
				'./assets/src/**/*.scss',
				'./blocks/**/*.{js,jsx,ts,tsx}',
			],
			safelist: {
				standard: [
					// Bootstrap classes that might be added dynamically
					/^btn/,
					/^nav/,
					/^dropdown/,
					/^modal/,
					/^tooltip/,
					/^popover/,
					/^carousel/,
					/^alert/,
					/^badge/,
					/^pagination/,
					// WordPress classes
					/^wp-/,
					/^post-/,
					/^page-/,
					/^comment-/,
					/^menu-/,
					/^widget-/,
					/^admin-bar/,
					// Article type classes
					/^post-type-/,
					/^article-type-/,
					/^sponsored-/,
					// Common utility classes
					/^d-/,
					/^text-/,
					/^bg-/,
					/^border-/,
					/^p-/,
					/^m-/,
					/^position-/,
					/^display-/,
				],
				deep: [
					// Dynamic classes that might be nested
					/accordion/,
					/collapse/,
					/tab/,
					// PhotoSwipe 5.4 classes
					/^pswp/,
					/pswp__/,
					// Gallery specific
					/rvk-image-gallery/,
					/gallery-grid/,
					/gallery-item/,
				],
			},
			variables: true,
			keyframes: true,
		}),
	].filter(Boolean), // Remove false values (when PurgeCSS is disabled)

	css: {
		devSourcemap: true,
		modules: {
			// Enable CSS modules for .module.scss files
			generateScopedName: '[name]__[local]___[hash:base64:5]',
		},
		preprocessorOptions: {
			scss: {
				quietDeps: true,
				silenceDeprecations: ['legacy-js-api', 'import'],
			},
		},
	},

	build: {
		// emit manifest so PHP can find the hashed files
		manifest: true,

		outDir: resolve(__dirname, 'dist/'),

		// don't base64 images
		assetsInlineLimit: 0,

		// Optimize build performance
		reportCompressedSize: false, // Skip compression size reporting (faster)
		chunkSizeWarningLimit: 1000, // Increase limit to reduce warnings

		// Enable better minification in production
		minify: 'esbuild',
		target: 'es2015',

		rollupOptions: {
			input: {
				// JavaScript
				'js/main': resolve(`${__dirname}/assets/src/js/main.js`),
				'js/navigation': resolve(`${__dirname}/assets/src/js/navigation.js`),

				'js/bootstrap-components': resolve(`${__dirname}/assets/src/js/bootstrap-components.js`),
				'js/soundcloud-custom-player': resolve(`${__dirname}/assets/src/js/soundcloud-custom-player.js`),
				'js/facebook-video-player': resolve(`${__dirname}/assets/src/js/facebook-video-player.js`),
				'js/youtube-video-player': resolve(`${__dirname}/assets/src/js/youtube-video-player.js`),
				'js/ai-content-helper': resolve(`${__dirname}/assets/src/js/ai-content-helper.js`),
				'js/article-type-panel': resolve(`${__dirname}/assets/src/js/article-type-panel.js`),
				'marketing-admin': resolve(`${__dirname}/assets/js/marketing-admin.js`),

				// Main CSS bundles
				'main': resolve(`${__dirname}/assets/src/scss/main.scss`),

				// Component CSS (loaded conditionally)
				'components/featured-image': resolve(`${__dirname}/components/featured-image/featured-image.scss`),
				'components/post-title': resolve(`${__dirname}/components/post-title/post-title.scss`),
				'components/post-date': resolve(`${__dirname}/components/post-date/post-date.scss`),
				'components/author': resolve(`${__dirname}/components/author/author.scss`),
				'components/post-terms': resolve(`${__dirname}/components/post-terms/post-terms.scss`),
				'components/radio-player': resolve(`${__dirname}/components/radio-player/radio-player.scss`),
				'components/soundcloud-custom-player': resolve(`${__dirname}/components/soundcloud-custom-player/soundcloud-custom-player.scss`),
				'components/facebook-video-player': resolve(`${__dirname}/components/facebook-video-player/facebook-video-player.scss`),
				'components/youtube-video-player': resolve(`${__dirname}/components/youtube-video-player/youtube-video-player.scss`),
				'components/ai-content-helper': resolve(`${__dirname}/assets/src/scss/components/ai-content-helper.scss`),

				// Article type components
				'components/article-type-badge': resolve(`${__dirname}/components/article-type-badge/article-type-badge.scss`),
				'components/article-type-overlay': resolve(`${__dirname}/components/article-type-overlay/article-type-overlay.scss`),
				'components/sponsored-disclaimer': resolve(`${__dirname}/components/sponsored-disclaimer/sponsored-disclaimer.scss`),
				'components/sponsored-badge': resolve(`${__dirname}/components/sponsored-badge/sponsored-badge.scss`),

				// Admin CSS
				'admin/sponsored-meta-box': resolve(`${__dirname}/assets/src/scss/admin/sponsored-meta-box.scss`),
				'admin/marketing': resolve(`${__dirname}/assets/scss/marketing-admin.scss`),
			},
			output: {
				entryFileNames: '[name].js',
				chunkFileNames: '[name].js',
				assetFileNames: (assetInfo) => {
					const extType = assetInfo.name.split('.');

					// group fonts in a folder
					if (
						extType[1] === 'woff' ||
						extType[1] === 'woff2' ||
						extType[1] === 'ttf'
					) {
						return 'fonts/[name].[ext]';
					}

					// group images in a folder
					if (
						extType[1] === 'gif' ||
						extType[1] === 'jpg' ||
						extType[1] === 'jpeg' ||
						extType[1] === 'png'
					) {
						return 'img/[name].[ext]';
					}

					// CSS files go directly in css folder
					if (extType[1] === 'css') {
						return 'css/[name].[ext]';
					}

					return '[ext]/[name].[ext]';
				},
			},
		},
	},

	server: {
		// required to load scripts from custom host
		cors: {
			origin: '*',
		},

		// We need a strict port to match on PHP side.
		strictPort: true,
		port: 5173,
	},

	resolve: {
		alias: {
			'@':
				process.env.NODE_ENV === 'development'
					? resolve(`${wpContentPath}/static`)
					: '/static',
		},
	},
});
