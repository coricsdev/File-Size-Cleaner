<div class="wrap">
    <h1><?php esc_html_e('File Cleaner Dashboard', 'file-size-cleaner'); ?></h1>

    <div id="fileScanner">
        <button 
            id="startScanBtn"
            class="button-primary"
        >
            <?php esc_html_e('Start Scan', 'file-size-cleaner'); ?>
        </button>

        <div id="progressBar" class="progress-bar" style="display: none;">
            <div id="progressBarFill" style="width: 0%;"></div>
        </div>

        <p><?php esc_html_e('Total Size: ', 'file-size-cleaner'); ?> 
           <span id="totalSize">0</span> bytes</p>
    </div>
</div>
