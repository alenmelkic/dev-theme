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

	test: {
		environment: 'jsdom',
		globals: true,
		setupFiles: './tests/setup.js',
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
		PurgeCSS({
			content: [
				'./**/*.php',
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
				],
			},
			variables: true,
			keyframes: true,
		}),
	],

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

		rollupOptions: {
			input: {
				// JavaScript
				'js/main': resolve(`${__dirname}/assets/src/js/main.js`),
				'js/navigation': resolve(`${__dirname}/assets/src/js/navigation.js`),
				'js/servicne-informacije': resolve(`${__dirname}/assets/src/js/servicne-informacije.js`),
				'js/soundcloud-custom-player': resolve(`${__dirname}/assets/src/js/soundcloud-custom-player.js`),
				'js/facebook-video-player': resolve(`${__dirname}/assets/src/js/facebook-video-player.js`),

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
