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
            submit_button('Save Settings');
        ?>
        
        <h2>🔍 Scan Settings</h2>
        <table class="form-table">
            <tr>
                <th><label for="scan_mode">Scan Mode</label></th>
                <td>
                    <select name="fsc_settings[scan_mode]" id="scan_mode">
                        <option value="basic" <?php selected($options['scan_mode'], 'basic'); ?>>Basic Scan (WP Folders Only)</option>
                        <option value="full" <?php selected($options['scan_mode'], 'full'); ?>>Full Scan (Entire WordPress Site)</option>
                        <option value="custom" <?php selected($options['scan_mode'], 'custom'); ?>>Custom Scan (Select Folders)</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="excluded_files">Excluded Files & Folders</label></th>
                <td>
                    <textarea name="fsc_settings[excluded_files]" id="excluded_files" rows="4" cols="50"><?php echo esc_textarea($options['excluded_files']); ?></textarea>
                    <p class="description">Enter file/folder paths to exclude from scans. One per line.</p>
                </td>
            </tr>
        </table>

        <h2>⚙️ Automation & Scheduling</h2>
        <table class="form-table">
            <tr>
                <th><label for="auto_scan">Enable Auto-Scan</label></th>
                <td>
                    <input type="checkbox" name="fsc_settings[auto_scan]" id="auto_scan" value="1" <?php checked($options['auto_scan'], 1); ?>>
                    <label for="auto_scan">Automatically scan files at scheduled intervals</label>
                </td>
            </tr>
            <tr>
                <th><label for="scan_schedule">Schedule Scan</label></th>
                <td>
                    <select name="fsc_settings[scan_schedule]" id="scan_schedule">
                        <option value="daily" <?php selected($options['scan_schedule'], 'daily'); ?>>Daily</option>
                        <option value="weekly" <?php selected($options['scan_schedule'], 'weekly'); ?>>Weekly</option>
                        <option value="monthly" <?php selected($options['scan_schedule'], 'monthly'); ?>>Monthly</option>
                    </select>
                </td>
            </tr>
        </table>

        <h2>🔐 Security</h2>
        <table class="form-table">
            <tr>
                <th><label for="restrict_delete">Restrict Deletions to Admins</label></th>
                <td>
                    <input type="checkbox" name="fsc_settings[restrict_delete]" id="restrict_delete" value="1" <?php checked($options['restrict_delete'], 1); ?>>
                    <label for="restrict_delete">Only allow administrators to delete files</label>
                </td>
            </tr>
            <tr>
                <th><label for="confirm_delete">Require Delete Confirmation</label></th>
                <td>
                    <input type="checkbox" name="fsc_settings[confirm_delete]" id="confirm_delete" value="1" <?php checked($options['confirm_delete'], 1); ?>>
                    <label for="confirm_delete">Users must confirm before deleting files</label>
                </td>
            </tr>
        </table>

        <h2>📊 Logs & Reports</h2>
        <table class="form-table">
            <tr>
                <th><label for="enable_debug">Enable Debug Mode</label></th>
                <td>
                    <input type="checkbox" name="fsc_settings[enable_debug]" id="enable_debug" value="1" <?php checked($options['enable_debug'], 1); ?>>
                    <label for="enable_debug">Log all actions (scans, deletions) for debugging</label>
                </td>
            </tr>
        </table>

        <?php submit_button('Save Settings'); ?>
    </form>
</div>
