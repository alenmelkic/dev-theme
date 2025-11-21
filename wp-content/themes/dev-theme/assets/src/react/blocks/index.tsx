// Gutenberg blocks entry point
// This file will contain all custom Gutenberg blocks

// Import WordPress dependencies
// @ts-ignore
import { registerBlockType } from '@wordpress/blocks';
// @ts-ignore
import { createElement } from '@wordpress/element';

registerBlockType('dev-theme/sample-block', {
  title: 'Sample Block',
  icon: 'smiley',
  category: 'common',
  edit: () => createElement('div', { className: 'dev-theme-sample-block' }, 'Hello from the editor!'),
  save: () => createElement('div', { className: 'dev-theme-sample-block' }, 'Hello from the frontend!'),
});

console.log('Gutenberg blocks initialized for dev-theme');