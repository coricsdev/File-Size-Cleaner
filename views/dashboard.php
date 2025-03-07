<div class="wrap">
    <h1><?php esc_html_e('File Cleaner Dashboard', 'file-size-cleaner'); ?></h1>

    <div x-data="fileScanner">
        <button 
            x-on:click="startScan()"
            x-bind:disabled="isScanning"
            class="button-primary"
        >
            <?php esc_html_e('Start Scan', 'file-size-cleaner'); ?>
        </button>

        <div x-show="isScanning" class="progress-bar">
            <div x-bind:style="{ width: scanProgress + '%' }"></div>
        </div>
    </div>
</div>
