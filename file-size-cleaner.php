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
use FileSizeCleaner\Admin\AdminMenu;
use FileSizeCleaner\Admin\Settings;
use FileSizeCleaner\Admin\Dashboard;

/**
 * Initializes the plugin properly
 */
function run_file_size_cleaner() {
    static $pluginInstance = null;

    if ($pluginInstance === null) {
        $pluginInstance = new Plugin();
        $pluginInstance->run();
    }

    // ✅ Register Admin Menu with Singleton
    static $adminMenuInstance = null;
    if ($adminMenuInstance === null) {
        $settings = new Settings();
        $dashboard = new Dashboard();
        $adminMenuInstance = AdminMenu::getInstance($dashboard, $settings);
        $adminMenuInstance->register();
    }
}
add_action('plugins_loaded', 'run_file_size_cleaner', 9); // ✅ Ensures it runs only once

// ✅ Properly Register Deactivation Hook
register_deactivation_hook(__FILE__, function () {
    $plugin = new Plugin();
    
    if (method_exists($plugin, 'getScheduler')) {
        $plugin->getScheduler()->removeScheduledCleanup();
    }
});
