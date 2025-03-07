<?php
/**
 * Plugin Name: File Size and Cleaner
 * Description: Monitors and cleans up unnecessary files to optimize WordPress performance.
 * Version: 1.0.0
 * Author: Rico Dadiz
 * License: GPL v2 or later
 * Text Domain: file-size-cleaner
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define path to autoload file
$autoloadPath = __DIR__ . '/vendor/autoload.php';

// Prevent activation if Composer dependencies are missing
if (!file_exists($autoloadPath)) {
    add_action('admin_notices', function () {
        echo '<div class="error"><p><strong>File Size and Cleaner:</strong> Missing <code>vendor/autoload.php</code>. Run <code>composer install</code> in the plugin directory.</p></div>';
    });
    return;
}

require_once $autoloadPath;

use FileSizeCleaner\Core\Plugin;

function run_file_size_cleaner() {
    $plugin = new Plugin();
    $plugin->run();
}

// ✅ Use the public getter method to safely access $scheduler
register_deactivation_hook(__FILE__, function () {
    $plugin = new Plugin();
    $plugin->getScheduler()->removeScheduledCleanup();
});

add_action('plugins_loaded', 'run_file_size_cleaner');
