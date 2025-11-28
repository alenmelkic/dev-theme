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
     * Initialize the image processor
     */
    public function __construct() {
        // Hook into WordPress image generation
        add_filter('wp_generate_attachment_metadata', [$this, 'process_uploaded_image'], 10, 2);
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
     * Process uploaded image
     * Generate AVIF and optimized fallbacks for all sizes
     * 
     * @param array $metadata Attachment metadata
     * @param int $attachment_id Attachment ID
     * @return array Modified metadata
     */
    /**
     * Log debug message
     */
    private function log($message) {
        // Force logging for debugging purposes
        $log_file = get_template_directory() . '/image-optimization-debug.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
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
        
        // WebP generation (optional - only if enabled)
        if (DEV_THEME_ENABLE_WEBP) {
            try {
                // Process full size image
                if (file_exists($file)) {
                    $this->log("Generating WebP for full size...");
                    $result = $this->generate_webp_version($file, $mime_type);
                    $this->log("Full size result: " . ($result ? 'Success' : 'Failed'));

                    // Delete original JPG/PNG after successful conversion
                    if ($result) {
                        @unlink($file);
                        $this->log("Deleted original full size image: $file");
                    }
                } else {
                    $this->log("Error: Full size file not found at $file");
                }

                // Process all generated sizes
                if (isset($metadata['sizes']) && is_array($metadata['sizes'])) {
                    $upload_dir = wp_upload_dir();
                    $base_dir = dirname($file);

                    foreach ($metadata['sizes'] as $size_name => $size_data) {
                        $size_file = $base_dir . '/' . $size_data['file'];

                        if (file_exists($size_file)) {
                            $this->log("Generating WebP for size: $size_name");
                            $result = $this->generate_webp_version($size_file, $mime_type);

                            // Delete original JPG/PNG after successful conversion
                            if ($result) {
                                @unlink($size_file);
                                $this->log("Deleted original $size_name image: $size_file");
                            }
                        }
                    }
                }

            } catch (Exception $e) {
                $this->log("Exception: " . $e->getMessage());
                error_log('Dev Theme Image Optimization Error: ' . $e->getMessage());
            }
        } else {
            $this->log("WebP generation disabled.");
        }
        
        // Update metadata to use WebP version and desktop size as full size
        if (isset($metadata['sizes']['desktop'])) {
            $this->log("Updating metadata to use WebP desktop as full size...");

            // Get the desktop WebP file path
            $desktop_webp_file = dirname($file) . '/' . preg_replace('/\.(jpe?g|png)$/i', '.webp', $metadata['sizes']['desktop']['file']);

            if (file_exists($desktop_webp_file)) {
                // Update metadata to point to desktop WebP as the new "full" size
                $metadata['width'] = $metadata['sizes']['desktop']['width'];
                $metadata['height'] = $metadata['sizes']['desktop']['height'];
                $metadata['file'] = str_replace(basename($file), basename($desktop_webp_file), $metadata['file']);

                // Update filesize to reflect the WebP file size
                $metadata['filesize'] = filesize($desktop_webp_file);

                // Remove desktop from sizes array since it's now the "full" size
                unset($metadata['sizes']['desktop']);

                // Update the attached file path in WordPress to point to WebP
                update_attached_file($attachment_id, $desktop_webp_file);

                $this->log("Metadata updated to use desktop WebP as full size.");
            } else {
                $this->log("Warning: Desktop WebP file not found at $desktop_webp_file");
            }
        }
        
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
                $image = imagecreatefromjpeg($file_path);
            } elseif ($mime_type === 'image/png') {
                $image = imagecreatefrompng($file_path);
                // Preserve transparency for PNG
                imagealphablending($image, false);
                imagesavealpha($image, true);
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

            return $result;

        } catch (Exception $e) {
            $this->log("WebP Generation Error: " . $e->getMessage());
            error_log('WebP Generation Error for ' . $file_path . ': ' . $e->getMessage());
            return false;
        }
    }
}

// Initialize the image processor
new Dev_Theme_Image_Processor();
