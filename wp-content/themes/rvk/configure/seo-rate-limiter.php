<?php
/**
 * SEO/AEO Rate Limiter
 * Prevents API abuse and excessive AI usage
 * Security: Protects against DoS and API cost attacks
 * Tailored by Alen Melkić
 */

// Security: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RVK_SEO_Rate_Limiter {

    /**
     * Check if user has exceeded rate limit
     *
     * @param int $user_id User ID
     * @param string $action Action being performed
     * @param int $limit Maximum actions per hour
     * @return bool|WP_Error True if within limit, WP_Error if exceeded
     */
    public static function check_rate_limit($user_id, $action, $limit = 50) {
        $transient_key = 'rvk_rate_limit_' . $user_id . '_' . $action;
        $requests = get_transient($transient_key);

        if ($requests === false) {
            // First request in this hour
            set_transient($transient_key, 1, HOUR_IN_SECONDS);
            return true;
        }

        if ($requests >= $limit) {
            return new WP_Error(
                'rate_limit_exceeded',
                sprintf('Rate limit exceeded. Maximum %d requests per hour allowed.', $limit),
                array('status' => 429)
            );
        }

        // Increment counter
        set_transient($transient_key, $requests + 1, HOUR_IN_SECONDS);
        return true;
    }

    /**
     * Get remaining requests for user
     *
     * @param int $user_id User ID
     * @param string $action Action being checked
     * @param int $limit Maximum actions per hour
     * @return array Remaining requests info
     */
    public static function get_remaining_requests($user_id, $action, $limit = 50) {
        $transient_key = 'rvk_rate_limit_' . $user_id . '_' . $action;
        $requests = get_transient($transient_key);

        if ($requests === false) {
            return array(
                'used' => 0,
                'limit' => $limit,
                'remaining' => $limit,
                'reset_time' => time() + HOUR_IN_SECONDS
            );
        }

        return array(
            'used' => $requests,
            'limit' => $limit,
            'remaining' => max(0, $limit - $requests),
            'reset_time' => time() + HOUR_IN_SECONDS
        );
    }

    /**
     * Reset rate limit for user (admin only)
     *
     * @param int $user_id User ID
     * @param string $action Action to reset
     * @return bool Success
     */
    public static function reset_rate_limit($user_id, $action) {
        if (!current_user_can('manage_options')) {
            return false;
        }

        $transient_key = 'rvk_rate_limit_' . $user_id . '_' . $action;
        return delete_transient($transient_key);
    }

    /**
     * Add rate limit headers to REST response
     *
     * @param WP_REST_Response $response Response object
     * @param int $user_id User ID
     * @param string $action Action being performed
     * @param int $limit Maximum requests
     * @return WP_REST_Response Modified response
     */
    public static function add_rate_limit_headers($response, $user_id, $action, $limit = 50) {
        $info = self::get_remaining_requests($user_id, $action, $limit);

        $response->header('X-RateLimit-Limit', $limit);
        $response->header('X-RateLimit-Remaining', $info['remaining']);
        $response->header('X-RateLimit-Reset', $info['reset_time']);

        return $response;
    }
}
