<?php
/**
 * Image Processor
 *
 * Handles automatic image optimization on upload:
 * - Generates WebP format for modern browsers
 * - Creates optimized JPEG/PNG fallbacks at 80% quality
 * - Generates 5 responsive sizes
 * - Only processes JPEG and PNG (excludes SVG, GIF, WebP, etc.)
 *
 * @package Dev_Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Dev_Theme_Image_Processor {

    /**
     * Stores temporary backup of files for rollback
     * @var array
     */
    private $backup_files = [];

    /**
     * Track conversion errors
     * @var array
     */
    private $conversion_errors = [];

    /**
     * Initialize the image processor
     */
    public function __construct() {
        // Hook into WordPress image generation
        add_filter('wp_generate_attachment_metadata', [$this, 'process_uploaded_image'], 10, 2);

        // Add admin notice for conversion errors
        add_action('admin_notices', [$this, 'show_conversion_errors']);
    }

    /**
     * Check if image should be processed
     * Only process JPEG and PNG images
     *
     * @param string $mime_type Image MIME type
     * @return bool True if should process, false otherwise
     */
    public function should_process_image($mime_type) {
        $allowed_types = DEV_THEME_SUPPORTED_FORMATS;
        return in_array($mime_type, $allowed_types);
    }

    /**
     * Log debug message (only if debug logging is enabled)
     */
    private function log($message) {
        if (!defined('DEV_THEME_DEBUG_LOGGING') || !DEV_THEME_DEBUG_LOGGING) {
            return;
        }

        $log_file = get_template_directory() . '/image-optimization-debug.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
    }

    /**
     * Check if server has enough memory for image processing
     *
     * @param string $file_path Path to image file
     * @return bool True if enough memory, false otherwise
     */
    private function check_memory_limit($file_path) {
        // Get image dimensions
        $image_info = @getimagesize($file_path);
        if (!$image_info) {
            return false;
        }

        list($width, $height, $type) = $image_info;

        // Estimate memory needed (width * height * 4 bytes per pixel * 1.5 safety factor)
        $estimated_memory = $width * $height * 4 * 1.5;

        // Get current memory limit
        $memory_limit = ini_get('memory_limit');
        if ($memory_limit == -1) {
            return true; // Unlimited memory
        }

        // Convert memory limit to bytes
        $memory_limit_bytes = $this->convert_to_bytes($memory_limit);

        // Get current memory usage
        $current_usage = memory_get_usage(true);

        // Check if we have enough free memory
        $available_memory = $memory_limit_bytes - $current_usage;

        if ($available_memory < $estimated_memory) {
            $this->log("Insufficient memory for image: $file_path (needs " . round($estimated_memory / 1024 / 1024, 2) . "MB)");
            return false;
        }

        return true;
    }

    /**
     * Convert PHP memory limit notation to bytes
     *
     * @param string $value Memory value (e.g., "256M", "1G")
     * @return int Bytes
     */
    private function convert_to_bytes($value) {
        $value = trim($value);
        $last = strtolower($value[strlen($value) - 1]);
        $value = (int) $value;

        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }

        return $value;
    }

    /**
     * Process uploaded image
     * Generate WebP and optimized fallbacks for all sizes
     *
     * @param array $metadata Attachment metadata
     * @param int $attachment_id Attachment ID
     * @return array Modified metadata
     */
    public function process_uploaded_image($metadata, $attachment_id) {
        $this->log("Processing attachment ID: $attachment_id");

        // Reset backup and error arrays
        $this->backup_files = [];
        $this->conversion_errors = [];

        // Check if optimization is enabled
        if (!DEV_THEME_ENABLE_OPTIMIZATION) {
            $this->log("Optimization disabled via constant.");
            return $metadata;
        }

        // Get attachment file info
        $file = get_attached_file($attachment_id);
        $mime_type = get_post_mime_type($attachment_id);

        $this->log("File: $file");
        $this->log("MIME Type: $mime_type");

        // Only process JPEG and PNG
        if (!$this->should_process_image($mime_type)) {
            $this->log("Skipping: Unsupported MIME type.");
            return $metadata;
        }

        // Check memory limit for main file
        if (!$this->check_memory_limit($file)) {
            $this->conversion_errors[] = "Image too large to process (insufficient memory): " . basename($file);
            return $metadata;
        }

        // WebP generation (optional - only if enabled)
        if (DEV_THEME_ENABLE_WEBP) {
            $conversion_success = true;
            $converted_files = [];

            try {
                // Process full size image
                if (file_exists($file)) {
                    $this->log("Generating WebP for full size...");
                    $result = $this->generate_webp_version($file, $mime_type);
                    $this->log("Full size result: " . ($result ? 'Success' : 'Failed'));

                    if ($result) {
                        $converted_files[] = $file;
                    } else {
                        $conversion_success = false;
                        $this->conversion_errors[] = "Failed to convert full size image: " . basename($file);
                    }
                } else {
                    $this->log("Error: Full size file not found at $file");
                    $conversion_success = false;
                }

                // Process all generated sizes
                if ($conversion_success && isset($metadata['sizes']) && is_array($metadata['sizes'])) {
                    $upload_dir = wp_upload_dir();
                    $base_dir = dirname($file);

                    foreach ($metadata['sizes'] as $size_name => $size_data) {
                        $size_file = $base_dir . '/' . $size_data['file'];

                        if (file_exists($size_file)) {
                            $this->log("Generating WebP for size: $size_name");
                            $result = $this->generate_webp_version($size_file, $mime_type);

                            if ($result) {
                                $converted_files[] = $size_file;
                            } else {
                                $conversion_success = false;
                                $this->conversion_errors[] = "Failed to convert $size_name: " . basename($size_file);
                                break; // Stop processing if any size fails
                            }
                        }
                    }
                }

                // Only delete originals if ALL conversions succeeded
                if ($conversion_success) {
                    foreach ($converted_files as $original_file) {
                        if (@unlink($original_file)) {
                            $this->log("Deleted original image: $original_file");
                        } else {
                            $this->log("Warning: Could not delete original image: $original_file");
                        }
                    }
                } else {
                    // Rollback: Delete any WebP files that were created
                    $this->log("Rolling back: Deleting WebP files due to conversion failure");
                    foreach ($converted_files as $original_file) {
                        $webp_file = preg_replace('/\.(jpe?g|png)$/i', '.webp', $original_file);
                        if (file_exists($webp_file)) {
                            @unlink($webp_file);
                            $this->log("Deleted WebP file: $webp_file");
                        }
                    }

                    // Return original metadata without modifications
                    return $metadata;
                }

            } catch (Exception $e) {
                $this->log("Exception: " . $e->getMessage());
                $this->conversion_errors[] = "Exception during conversion: " . $e->getMessage();
                error_log('Dev Theme Image Optimization Error: ' . $e->getMessage());

                // Return original metadata on exception
                return $metadata;
            }
        } else {
            $this->log("WebP generation disabled.");
        }

        // Update metadata to use WebP version and desktop size as full size
        $desktop_size_name = null;

        // Try to find the desktop size or fallback to largest available
        if (isset($metadata['sizes']['desktop'])) {
            $desktop_size_name = 'desktop';
        } elseif (isset($metadata['sizes']['tablet'])) {
            $desktop_size_name = 'tablet';
            $this->log("Desktop size not found, using tablet as fallback");
        } elseif (isset($metadata['sizes']['mobile'])) {
            $desktop_size_name = 'mobile';
            $this->log("Desktop/tablet sizes not found, using mobile as fallback");
        }

        if ($desktop_size_name) {
            $this->log("Updating metadata to use WebP $desktop_size_name as full size...");

            // Get the desktop WebP file path
            $desktop_webp_file = dirname($file) . '/' . preg_replace('/\.(jpe?g|png)$/i', '.webp', $metadata['sizes'][$desktop_size_name]['file']);

            if (file_exists($desktop_webp_file)) {
                // Update metadata to point to desktop WebP as the new "full" size
                $metadata['width'] = $metadata['sizes'][$desktop_size_name]['width'];
                $metadata['height'] = $metadata['sizes'][$desktop_size_name]['height'];
                $metadata['file'] = str_replace(basename($file), basename($desktop_webp_file), $metadata['file']);

                // Update filesize to reflect the WebP file size
                $metadata['filesize'] = filesize($desktop_webp_file);

                // Remove desktop from sizes array since it's now the "full" size
                unset($metadata['sizes'][$desktop_size_name]);

                // Update the attached file path in WordPress to point to WebP
                update_attached_file($attachment_id, $desktop_webp_file);

                $this->log("Metadata updated to use $desktop_size_name WebP as full size.");
            } else {
                $this->log("Warning: Desktop WebP file not found at $desktop_webp_file");
            }
        } else {
            $this->log("Warning: No suitable size found for full size replacement");
        }

        // Update all size filenames in metadata to .webp extension
        if (isset($metadata['sizes']) && is_array($metadata['sizes'])) {
            foreach ($metadata['sizes'] as $size_name => &$size_data) {
                // Verify WebP file exists before updating metadata
                $base_dir = dirname($file);
                $webp_file = $base_dir . '/' . preg_replace('/\.(jpe?g|png)$/i', '.webp', $size_data['file']);

                if (file_exists($webp_file)) {
                    $size_data['file'] = preg_replace('/\.(jpe?g|png)$/i', '.webp', $size_data['file']);
                    $size_data['mime-type'] = 'image/webp';
                    $size_data['filesize'] = filesize($webp_file);
                }
            }
            unset($size_data);
        }

        // Update post MIME type to WebP
        wp_update_post([
            'ID' => $attachment_id,
            'post_mime_type' => 'image/webp'
        ]);

        $this->log("Updated all metadata and MIME type to WebP.");

        // IMPORTANT: Always return metadata so WordPress can continue processing
        return $metadata;
    }

    /**
     * Generate WebP version of an image using GD library
     *
     * @param string $file_path Path to source image file
     * @param string $mime_type Source image MIME type
     * @return bool True on success, false on failure
     */
    public function generate_webp_version($file_path, $mime_type) {
        // Check if WebP generation is enabled
        if (!DEV_THEME_ENABLE_WEBP) {
            return false;
        }

        try {
            // Load image based on MIME type
            if ($mime_type === 'image/jpeg') {
                $image = @imagecreatefromjpeg($file_path);
            } elseif ($mime_type === 'image/png') {
                $image = @imagecreatefrompng($file_path);
                if ($image) {
                    // Preserve transparency for PNG
                    imagealphablending($image, false);
                    imagesavealpha($image, true);
                }
            } else {
                return false;
            }

            if (!$image) {
                $this->log("Failed to create image resource from: $file_path");
                return false;
            }

            // Generate WebP filename
            $webp_path = preg_replace('/\.(jpe?g|png)$/i', '.webp', $file_path);

            // Save as WebP
            $result = imagewebp($image, $webp_path, DEV_THEME_WEBP_QUALITY);
            imagedestroy($image);

            // Verify WebP file was created
            if ($result && file_exists($webp_path)) {
                return true;
            }

            return false;

        } catch (Exception $e) {
            $this->log("WebP Generation Error: " . $e->getMessage());
            error_log('WebP Generation Error for ' . $file_path . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Show conversion errors in admin
     */
    public function show_conversion_errors() {
        if (!empty($this->conversion_errors)) {
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p><strong>Image Conversion Errors:</strong></p>';
            echo '<ul>';
            foreach ($this->conversion_errors as $error) {
                echo '<li>' . esc_html($error) . '</li>';
            }
            echo '</ul>';
            echo '</div>';
        }
    }
}

// Initialize the image processor
new Dev_Theme_Image_Processor();
