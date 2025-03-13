<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Ensure settings exist and is an array
$options = get_option('fsc_settings', []);
if (!is_array($options)) {
    $options = [];
}

// Define default values to prevent undefined index warnings
$defaults = [
    'scan_mode' => 'basic',
    'excluded_files' => '',
    'auto_scan' => 0,
    'scan_schedule' => 'weekly',
    'restrict_delete' => 1,
    'confirm_delete' => 1,
    'enable_debug' => 0
];

// Merge defaults with existing settings
$options = array_merge($defaults, $options);
?>

<div class="wrap">
    <h1><?php esc_html_e('File Size Cleaner - Settings', 'file-size-cleaner'); ?></h1>
    <p>Configure scan modes, automation, security, and logs.</p>

    <form method="post" action="options.php">
        <?php
            settings_fields('fsc_settings_group');
            do_settings_sections('file-size-cleaner-settings');
        ?>

        <table class="form-table">
            <!-- Excluded Files & Folders should be inside the settings table, below Scan Mode -->
            <tr id="excluded_files_row" style="display: none;">
                <th><label for="excluded_files"><?php esc_html_e('Excluded Files & Folders', 'file-size-cleaner'); ?></label></th>
                <td>
                    <textarea name="fsc_settings[excluded_files]" id="excluded_files" rows="4" cols="50"><?php echo esc_textarea($options['excluded_files'] ?? ''); ?></textarea>
                    <p class="description"><?php esc_html_e('Enter file/folder paths to exclude from scans. One per line.', 'file-size-cleaner'); ?></p>
                </td>
            </tr>
        </table>

        <?php submit_button('Save Settings'); ?>
    </form>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const scanMode = document.getElementById("scan_mode");
            const excludedFilesRow = document.getElementById("excluded_files_row");

            function toggleExcludedFiles() {
                if (scanMode && excludedFilesRow) {
                    excludedFilesRow.style.display = (scanMode.value === "custom") ? "table-row" : "none";
                }
            }

            if (scanMode) {
                scanMode.addEventListener("change", toggleExcludedFiles);
                toggleExcludedFiles(); // Ensure correct visibility on page load
            }
        });
    </script>
</div>
