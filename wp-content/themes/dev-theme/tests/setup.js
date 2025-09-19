// Vitest setup file
import { beforeEach } from 'vitest';

// Global test setup
beforeEach(() => {
  // Reset DOM before each test
  document.body.innerHTML = '';

  // Clear any existing event listeners
  document.removeEventListener?.('DOMContentLoaded', () => {});
});

// Mock WordPress globals if needed
global.wp = global.wp || {};
global.jQuery = global.jQuery || (() => ({}));
global.$ = global.$ || global.jQuery;

// Mock console methods for cleaner test output
global.console = {
  ...console,
  warn: () => {},
  error: () => {},
};