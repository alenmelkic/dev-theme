<?php
/**
 * Image Processor
 * 
 * Handles automatic image optimization on upload:
 * - Generates AVIF format for modern browsers
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
     * Generate AVIF and optimized fallbacks for all sizes
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
        
        // AVIF generation (optional - only if enabled and Imagick available)
        if (DEV_THEME_ENABLE_AVIF && extension_loaded('imagick')) {
            try {
                // Process full size image
                if (file_exists($file)) {
                    $this->log("Generating AVIF for full size...");
                    $result = $this->generate_avif_version($file, $mime_type);
                    $this->log("Full size result: " . ($result ? 'Success' : 'Failed'));
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
                            $this->log("Generating AVIF for size: $size_name");
                            $this->generate_avif_version($size_file, $mime_type);
                        }
                    }
                }
                
            } catch (Exception $e) {
                $this->log("Exception: " . $e->getMessage());
                error_log('Dev Theme Image Optimization Error: ' . $e->getMessage());
            }
        } else {
            if (!DEV_THEME_ENABLE_AVIF) {
                $this->log("AVIF generation disabled.");
            } else {
                $this->log("Imagick not available - skipping AVIF generation.");
            }
        }
        
        // Delete original full-size image to save space
        // The largest size (1440px) will be used as the "full" size
        if (file_exists($file) && isset($metadata['sizes']['desktop'])) {
            $this->log("Deleting original full-size image to save space...");
            
            // Get the desktop (1440px) file info
            $desktop_file = dirname($file) . '/' . $metadata['sizes']['desktop']['file'];
            
            if (@unlink($file)) {
                $this->log("Original image deleted successfully.");
                
                // Update metadata to point to desktop size as the new "full" size
                // This is critical for WordPress to find the image
                $metadata['width'] = $metadata['sizes']['desktop']['width'];
                $metadata['height'] = $metadata['sizes']['desktop']['height'];
                $metadata['file'] = str_replace(basename($file), $metadata['sizes']['desktop']['file'], $metadata['file']);
                
                // Update filesize to reflect the actual file size
                if (file_exists($desktop_file)) {
                    $metadata['filesize'] = filesize($desktop_file);
                }
                
                // Remove desktop from sizes array since it's now the "full" size
                unset($metadata['sizes']['desktop']);
                
                // Update the attached file path in WordPress
                update_attached_file($attachment_id, $desktop_file);
                
                $this->log("Metadata updated to use desktop size as full size.");
            } else {
                $this->log("Failed to delete original image.");
            }
        }
        
        // IMPORTANT: Always return metadata so WordPress can continue processing
        return $metadata;
    }
    
    /**
     * Generate AVIF version of an image
     * 
     * @param string $file_path Path to source image file
     * @param string $mime_type Source image MIME type
     * @return bool True on success, false on failure
     */
    public function generate_avif_version($file_path, $mime_type) {
        // Check if AVIF generation is enabled
        if (!DEV_THEME_ENABLE_AVIF) {
            return false;
        }
        
        try {
            $imagick = new Imagick($file_path);
            
            // Set AVIF quality
            $imagick->setImageFormat('avif');
            $imagick->setImageCompressionQuality(DEV_THEME_AVIF_QUALITY);
            
            // Generate AVIF filename
            $avif_path = preg_replace('/\.(jpe?g|png)$/i', '.avif', $file_path);
            
            // Save AVIF file
            $imagick->writeImage($avif_path);
            $imagick->clear();
            $imagick->destroy();
            
            return true;
            
        } catch (Exception $e) {
            $this->log("AVIF Generation Error: " . $e->getMessage());
            error_log('AVIF Generation Error for ' . $file_path . ': ' . $e->getMessage());
            return false;
        }
    }
}

// Initialize the image processor
new Dev_Theme_Image_Processor();
