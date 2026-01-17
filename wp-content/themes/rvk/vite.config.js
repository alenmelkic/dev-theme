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
import { fontConverter } from './vite-plugin-font-converter.js';

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
		fontConverter(),
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
				'./blocks/**/*.php',
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
					// Article page specific
					/^single-post-page/,
					/category-link/,
					// Common utility classes
					/^d-/,
					/^text-/,
					/^bg-/,
					/^border-/,
					/^(p|m)(x|y|t|b|l|r)?-/,
					/^position-/,
					/^display-/,
					/^site-/,
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
				greedy: [
					/@font-face/, // Preserve font-face rules
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
				loadPaths: [resolve(__dirname, 'assets/src/scss')],
				additionalData: `
					@use "settings/variables" as *;
					@use "settings/mixins" as *;
					@use "settings/functions" as *;
				`,
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
				'js/seo-content-panel': resolve(`${__dirname}/assets/src/js/seo-content-panel.js`),
				'js/article-type-panel': resolve(`${__dirname}/assets/src/js/article-type-panel.js`),
				'js/social-share': resolve(`${__dirname}/assets/src/js/social-share.js`),
				'js/adsense-manager': resolve(`${__dirname}/assets/src/js/adsense-manager.js`),

				// Admin JS (protected)
				'admin/js/marketing-admin': resolve(`${__dirname}/assets/js/marketing-admin.js`),
				'admin/js/adsense-admin': resolve(`${__dirname}/assets/js/adsense-admin.js`),
				'admin/js/analytics-admin': resolve(`${__dirname}/assets/src/js/analytics-admin.js`),
				'admin/js/custom-scripts-admin': resolve(`${__dirname}/assets/src/js/custom-scripts-admin.js`),

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

				// Social share buttons
				'components/social-share-buttons': resolve(`${__dirname}/components/social-share-buttons/social-share-buttons.scss`),

				// Mini Banners Carousel
				'components/mini-banners-carousel': resolve(`${__dirname}/assets/src/scss/components/_mini-banners-carousel.scss`),

				// Post Listings Block (conditionally loaded)
				'components/post-listings-base': resolve(`${__dirname}/assets/src/scss/post-listings-base.scss`),
				'components/post-listings-cards-1': resolve(`${__dirname}/assets/src/scss/post-listings-cards-1.scss`),
				'components/post-listings-cards-2': resolve(`${__dirname}/assets/src/scss/post-listings-cards-2.scss`),
				'components/post-listings-list': resolve(`${__dirname}/assets/src/scss/post-listings-list.scss`),

				// Admin CSS (protected)
				'admin/css/sponsored-meta-box': resolve(`${__dirname}/assets/src/scss/admin/sponsored-meta-box.scss`),
				'admin/css/marketing': resolve(`${__dirname}/assets/scss/marketing-admin.scss`),
				'admin/css/adsense': resolve(`${__dirname}/assets/scss/adsense-admin.scss`),
				'admin/css/analytics': resolve(`${__dirname}/assets/src/scss/admin/analytics.scss`),
				'admin/css/custom-scripts': resolve(`${__dirname}/assets/src/scss/admin/custom-scripts.scss`),
			},
			output: {
				entryFileNames: (chunkInfo) => {
					// Admin JS files go to admin/js/
					if (chunkInfo.name.startsWith('admin/js/')) {
						return '[name].min.js';
					}
					// All other JS files go to their specified paths
					return '[name].min.js';
				},
				chunkFileNames: '[name].min.js',
				assetFileNames: (assetInfo) => {
					const extType = assetInfo.name.split('.');
					const name = assetInfo.name;

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

					// CSS files with .min.css extension
					if (extType[1] === 'css') {
						// Admin CSS files go to admin/css/
						if (name.includes('admin/css/')) {
							// Extract just the filename from admin/css/filename
							const filename = name.split('/').pop().replace('.css', '');
							return `admin/css/${filename}.min.css`;
						}
						return 'css/[name].min.[ext]';
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
