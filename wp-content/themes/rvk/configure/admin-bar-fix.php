<?php
/**
 * Admin Bar Consistency Fix
 * 
 * Ensures the admin bar margin is applied consistently across all pages (Posts, Pages, Categories).
 * Fixes the issue where the header "jumps" when switching between templates because
 * standard WP pages were missing the admin bar bump CSS.
 */

if (!defined('ABSPATH')) {
    exit;
}

function rvk_consistent_admin_bar_bump() {
    if (is_admin_bar_showing() && !is_admin()) {
        ?>
        <style type="text/css" media="screen">
            html { margin-top: 32px !important; }
            * html body { margin-top: 32px !important; }
            @media screen and ( max-width: 782px ) {
                html { margin-top: 46px !important; }
                * html body { margin-top: 46px !important; }
            }
        </style>
        <?php
    }
}
// Hook into wp_head with high priority (late execution) to ensure it overrides any removals
add_action('wp_head', 'rvk_consistent_admin_bar_bump', 999);
