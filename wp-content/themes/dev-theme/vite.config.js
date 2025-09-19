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
				'./assets/src/**/*.scss',
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

		outDir: resolve(__dirname, 'assets/dist/'),

		// don't base64 images
		assetsInlineLimit: 0,

		rollupOptions: {
			input: {
				'js/main': resolve(`${__dirname}/assets/src/js/main.js`),
				...(() =>
					glob
						.sync(resolve(__dirname, 'assets/src/scss/[!_]*.scss'))
						.reduce((entries, filename) => {
							const [, name] = filename.match(/([^/]+)\.scss$/);
							return { ...entries, [name]: filename };
						}, {}))(),
			},
			output: {
				entryFileNames: '[name]-[hash].js',
				chunkFileNames: '[name]-[hash].js',
				assetFileNames: (assetInfo) => {
					const extType = assetInfo.name.split('.');

					// group fonts in a folder
					if (
						extType[1] === 'woff' ||
						extType[1] === 'woff2' ||
						extType[1] === 'ttf'
					) {
						return 'fonts/[name]-[hash].[ext]';
					}

					// group images in a folder
					if (
						extType[1] === 'gif' ||
						extType[1] === 'jpg' ||
						extType[1] === 'jpeg' ||
						extType[1] === 'png'
					) {
						return 'img/[name]-[hash].[ext]';
					}

					return '[ext]/[name]-[hash].[ext]';
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
