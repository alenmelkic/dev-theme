<?php
/**
 * AVIF Support Verification Script
 *
 * Upload this to your production server and access it via browser
 * to check if your server can generate AVIF images.
 *
 * URL: https://yourdomain.com/wp-content/themes/dev-theme/verify-avif-support.php
 *
 * @package Dev_Theme
 */

// Security: Add a secret key to prevent unauthorized access
define('VERIFICATION_KEY', 'your-secret-key-' . date('Ymd')); // Change this!

// Check for access key
if (!isset($_GET['key']) || $_GET['key'] !== VERIFICATION_KEY) {
    die('Access denied. Add ?key=' . VERIFICATION_KEY . ' to the URL');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>AVIF Support Verification</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
        h1 { color: #2c3e50; }
        .check { margin: 20px 0; padding: 15px; border-radius: 5px; }
        .pass { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .fail { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .warn { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .status { font-weight: bold; }
    </style>
</head>
<body>
    <h1>🔍 AVIF Support Verification</h1>
    <p>Server: <strong><?php echo $_SERVER['SERVER_NAME']; ?></strong></p>
    <p>PHP Version: <strong><?php echo PHP_VERSION; ?></strong></p>
    <hr>

<?php

// Check 1: PHP Imagick Extension
echo '<div class="check">';
echo '<h2>1. ImageMagick Extension</h2>';
if (extension_loaded('imagick')) {
    echo '<p class="status pass">✓ PASS: Imagick extension is installed</p>';

    $imagick = new Imagick();
    $version = $imagick->getVersion();
    echo '<p><strong>Version:</strong> ' . htmlspecialchars($version['versionString']) . '</p>';
} else {
    echo '<p class="status fail">✗ FAIL: Imagick extension is NOT installed</p>';
    echo '<p>Solution: Install php-imagick extension on your server</p>';
}
echo '</div>';

// Check 2: AVIF Format Support
echo '<div class="check">';
echo '<h2>2. AVIF Format Support</h2>';
if (extension_loaded('imagick')) {
    $imagick = new Imagick();
    $formats = $imagick->queryFormats('AVIF');

    if (!empty($formats)) {
        echo '<p class="status pass">✓ PASS: AVIF format is supported</p>';
        echo '<p>Supported AVIF formats: ' . implode(', ', $formats) . '</p>';
    } else {
        echo '<p class="status fail">✗ FAIL: AVIF format is NOT supported</p>';
        echo '<p>Your ImageMagick installation does not have AVIF encoders (libheif/libaom)</p>';
        echo '<p><strong>Solution:</strong> Contact your hosting provider to:</p>';
        echo '<ul>';
        echo '<li>Rebuild ImageMagick with libheif support, OR</li>';
        echo '<li>Install libaom and rebuild ImageMagick, OR</li>';
        echo '<li>Upgrade to a more recent ImageMagick version with AVIF support</li>';
        echo '</ul>';
    }
} else {
    echo '<p class="status fail">✗ SKIP: Cannot check (Imagick not installed)</p>';
}
echo '</div>';

// Check 3: All Supported Formats
echo '<div class="check info">';
echo '<h2>3. Supported Image Formats</h2>';
if (extension_loaded('imagick')) {
    $imagick = new Imagick();
    $all_formats = $imagick->queryFormats();

    $important_formats = ['JPEG', 'JPG', 'PNG', 'WEBP', 'AVIF', 'GIF', 'HEIC', 'HEIF'];
    $supported = array_intersect($important_formats, $all_formats);
    $missing = array_diff($important_formats, $all_formats);

    echo '<p><strong>Supported formats:</strong></p>';
    echo '<pre>' . implode(', ', $supported) . '</pre>';

    if (!empty($missing)) {
        echo '<p><strong>Missing formats:</strong></p>';
        echo '<pre>' . implode(', ', $missing) . '</pre>';
    }
} else {
    echo '<p>Cannot check (Imagick not installed)</p>';
}
echo '</div>';

// Check 4: GD Library (fallback)
echo '<div class="check">';
echo '<h2>4. GD Library (Fallback)</h2>';
if (extension_loaded('gd')) {
    echo '<p class="status pass">✓ PASS: GD extension is installed</p>';

    $gd_info = gd_info();
    echo '<p><strong>GD Version:</strong> ' . $gd_info['GD Version'] . '</p>';

    // Check for AVIF support in GD (PHP 8.1+)
    if (function_exists('imageavif')) {
        echo '<p class="status pass">✓ GD has AVIF support (imageavif function exists)</p>';
    } else {
        echo '<p class="status warn">⚠ GD does not have AVIF support (requires PHP 8.1+ with libavif)</p>';
    }
} else {
    echo '<p class="status warn">⚠ WARNING: GD extension is NOT installed</p>';
}
echo '</div>';

// Check 5: Test AVIF Generation
echo '<div class="check">';
echo '<h2>5. Live AVIF Generation Test</h2>';
if (extension_loaded('imagick')) {
    try {
        // Create a simple test image
        $test_image = new Imagick();
        $test_image->newImage(100, 100, new ImagickPixel('blue'));
        $test_image->setImageFormat('jpeg');

        $temp_jpeg = sys_get_temp_dir() . '/test-avif-' . time() . '.jpg';
        $temp_avif = sys_get_temp_dir() . '/test-avif-' . time() . '.avif';

        // Save as JPEG first
        $test_image->writeImage($temp_jpeg);

        // Try to convert to AVIF
        $test_image->setImageFormat('avif');
        $test_image->setImageCompressionQuality(85);
        $test_image->writeImage($temp_avif);

        if (file_exists($temp_avif) && filesize($temp_avif) > 0) {
            echo '<p class="status pass">✓ PASS: Successfully generated AVIF image</p>';
            echo '<p>Test file size: ' . filesize($temp_avif) . ' bytes</p>';
            echo '<p>✓ Your server CAN generate AVIF images!</p>';

            // Display the test image
            $avif_data = base64_encode(file_get_contents($temp_avif));
            echo '<p><strong>Test AVIF Image:</strong></p>';
            echo '<img src="data:image/avif;base64,' . $avif_data . '" alt="Test AVIF" style="border: 2px solid #155724;">';
        } else {
            echo '<p class="status fail">✗ FAIL: AVIF file was not created</p>';
        }

        // Cleanup
        @unlink($temp_jpeg);
        @unlink($temp_avif);
        $test_image->clear();
        $test_image->destroy();

    } catch (Exception $e) {
        echo '<p class="status fail">✗ FAIL: Error generating AVIF</p>';
        echo '<p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
} else {
    echo '<p class="status fail">✗ SKIP: Cannot test (Imagick not installed)</p>';
}
echo '</div>';

// Check 6: WordPress Theme Configuration
echo '<div class="check info">';
echo '<h2>6. WordPress Theme Configuration</h2>';

// Try to load WordPress
$wp_load_path = dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';
if (file_exists($wp_load_path)) {
    require_once($wp_load_path);

    echo '<p class="status pass">✓ WordPress loaded successfully</p>';

    // Check theme constants
    echo '<p><strong>Theme Settings:</strong></p>';
    echo '<ul>';
    echo '<li>DEV_THEME_ENABLE_AVIF: <code>' . (defined('DEV_THEME_ENABLE_AVIF') ? (DEV_THEME_ENABLE_AVIF ? 'true' : 'false') : 'not defined') . '</code></li>';
    echo '<li>DEV_THEME_AVIF_QUALITY: <code>' . (defined('DEV_THEME_AVIF_QUALITY') ? DEV_THEME_AVIF_QUALITY : 'not defined') . '</code></li>';
    echo '<li>DEV_THEME_ENABLE_OPTIMIZATION: <code>' . (defined('DEV_THEME_ENABLE_OPTIMIZATION') ? (DEV_THEME_ENABLE_OPTIMIZATION ? 'true' : 'false') : 'not defined') . '</code></li>';
    echo '</ul>';

    // Check if image processor is loaded
    if (class_exists('Dev_Theme_Image_Processor')) {
        echo '<p class="status pass">✓ Dev_Theme_Image_Processor class is loaded</p>';
    } else {
        echo '<p class="status warn">⚠ Dev_Theme_Image_Processor class is NOT loaded</p>';
    }

} else {
    echo '<p class="status warn">⚠ Could not load WordPress (wp-load.php not found)</p>';
    echo '<p>Path attempted: <code>' . htmlspecialchars($wp_load_path) . '</code></p>';
}
echo '</div>';

// Summary
echo '<hr>';
echo '<div class="check">';
echo '<h2>📊 Summary</h2>';

$can_generate_avif = extension_loaded('imagick') && !empty((new Imagick())->queryFormats('AVIF'));

if ($can_generate_avif) {
    echo '<p class="status pass" style="font-size: 1.2em;">✓ Your server CAN generate AVIF images!</p>';
    echo '<p><strong>Next steps:</strong></p>';
    echo '<ol>';
    echo '<li>Ensure <code>DEV_THEME_ENABLE_AVIF</code> is set to <code>true</code> in your theme</li>';
    echo '<li>Upload a new image to test AVIF generation</li>';
    echo '<li>Check <code>wp-content/themes/dev-theme/image-optimization-debug.log</code> for processing logs</li>';
    echo '<li>For existing images, regenerate thumbnails using a plugin</li>';
    echo '</ol>';
} else {
    echo '<p class="status fail" style="font-size: 1.2em;">✗ Your server CANNOT generate AVIF images</p>';
    echo '<p><strong>Required actions:</strong></p>';
    echo '<ol>';

    if (!extension_loaded('imagick')) {
        echo '<li>Install php-imagick extension</li>';
    }

    echo '<li>Ensure ImageMagick is compiled with libheif or libaom support</li>';
    echo '<li>Contact your hosting provider or server administrator</li>';
    echo '<li>Alternatively, use a different image optimization plugin that uses external services</li>';
    echo '</ol>';
}
echo '</div>';

?>

<hr>
<p style="text-align: center; color: #666; font-size: 0.9em;">
    Delete this file after verification for security purposes.
</p>

</body>
</html>
