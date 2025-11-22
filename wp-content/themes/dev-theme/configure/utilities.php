<?php
/**
 * Utility Functions
 */

// Security: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function get_static_dir() {
	return get_template_directory_uri() . '/static';
}
