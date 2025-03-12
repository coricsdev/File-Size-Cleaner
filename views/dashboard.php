<?php

function fsc_enqueue_admin_scripts() {
    error_log("✅ fsc_enqueue_admin_scripts() is running!"); // Debugging

    // ✅ Register the script correctly
    wp_register_script(
        'file-cleaner-admin',
        plugins_url('../../assets/js/admin-scripts.js', __FILE__), // Ensure correct path
        ['jquery'],
        '1.0.0',
        true
    );

    // ✅ Ensure script is enqueued
    wp_enqueue_script('file-cleaner-admin');

    $fsc_data = [
        'ajax_url'     => admin_url('admin-ajax.php'),
        'scan_nonce'   => wp_create_nonce('fsc_scan_nonce'),
        'delete_nonce' => wp_create_nonce('fsc_delete_nonce')
    ];

    // ✅ Use wp_add_inline_script() instead of wp_localize_script()
    $inline_script = 'var fsc_data = ' . json_encode($fsc_data) . ';';
    wp_add_inline_script('file-cleaner-admin', $inline_script, 'before');

    error_log("✅ fsc_data: " . print_r($fsc_data, true)); // Check in debug.log
}
add_action('admin_enqueue_scripts', 'fsc_enqueue_admin_scripts');




?>
<div class="wrap">
    <h1><?php esc_html_e('File Cleaner Dashboard', 'file-size-cleaner'); ?></h1>

    <script>
        var fsc_data = <?php echo json_encode([
            'ajax_url'     => admin_url('admin-ajax.php'),
            'scan_nonce'   => wp_create_nonce('fsc_scan_nonce'),
            'delete_nonce' => wp_create_nonce('fsc_delete_nonce')
        ]); ?>;
        console.log("✅ fsc_data manually injected:", fsc_data);
    </script>

    <div id="fileScanner">
        <div id="loadingOverlay">
            <div class="spinner"></div>
        </div>
        <button id="startScanBtn" class="button-primary">Start Scan</button>
        
        <div id="progressBar" class="progress-bar" style="display: none;">
            <div id="progressBarFill" style="width: 0%;"></div>
        </div>
        <p id="scanPercentage" style="font-weight: bold; display: none;">0%</p>

        <!-- 🔥 Add Total Size Here -->
        <p><?php esc_html_e('Total Size: ', 'file-size-cleaner'); ?> 
            <span id="totalSize">0</span> bytes
        </p>

        <!-- Ensure elements exist -->
        <div id="infoBoxes"></div>
        <div id="resultsTable"></div> <!-- 🔥 This ensures resultsTable exists -->
    </div>
</div>
