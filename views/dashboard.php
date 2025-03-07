<div class="wrap">
    <h1><?php esc_html_e('File Cleaner Dashboard', 'file-size-cleaner'); ?></h1>

    <div id="fileScanner">
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
