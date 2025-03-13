<?php
namespace FileSizeCleaner\Admin;

class Settings {
    
    private string $option_name = 'fsc_settings'; // Ensure settings use a single option name

    public function __construct() {
        add_action('admin_init', [$this, 'registerSettings']);
    }

    public function registerSettings(): void {
        register_setting('fsc_settings_group', $this->option_name);

        add_settings_section('fsc_main_settings', 'General Settings', null, 'file-size-cleaner-settings');

        add_settings_field('scan_mode', 'Scan Mode', [$this, 'scanModeCallback'], 'file-size-cleaner-settings', 'fsc_main_settings');
        // Ensure "Excluded Files & Folders" appears right after Scan Mode
        add_settings_field('excluded_files', '', [$this, 'excludedFilesCallback'], 'file-size-cleaner-settings', 'fsc_main_settings');
        add_settings_field('auto_scan', 'Enable Auto-Scan', [$this, 'autoScanCallback'], 'file-size-cleaner-settings', 'fsc_main_settings');
        add_settings_field('scan_schedule', 'Schedule Scan', [$this, 'scanScheduleCallback'], 'file-size-cleaner-settings', 'fsc_main_settings');
        add_settings_field('restrict_delete', 'Restrict Deletions to Admins', [$this, 'restrictDeleteCallback'], 'file-size-cleaner-settings', 'fsc_main_settings');
        add_settings_field('confirm_delete', 'Require Delete Confirmation', [$this, 'confirmDeleteCallback'], 'file-size-cleaner-settings', 'fsc_main_settings');
        add_settings_field('enable_debug', 'Enable Debug Mode', [$this, 'enableDebugCallback'], 'file-size-cleaner-settings', 'fsc_main_settings');
    }

    public function render(): void {
        // Always fetch settings and ensure it has default values
        $options = get_option($this->option_name, []);

        if (!is_array($options)) {
            $options = [];
        }

        // Define default values for missing settings to prevent warnings
        $defaults = [
            'scan_mode' => 'basic',
            'excluded_files' => '',
            'auto_scan' => 0,
            'scan_schedule' => 'weekly',
            'restrict_delete' => 1,
            'confirm_delete' => 1,
            'enable_debug' => 0
        ];

        // Merge defaults with existing options
        $options = array_merge($defaults, $options);

        require_once plugin_dir_path(__FILE__) . '../../views/settings.php';
    }

    // CALLBACKS TO RENDER FIELDS

    public function scanModeCallback(): void {
        $options = get_option('fsc_settings', []);
        ?>
        <select name="fsc_settings[scan_mode]" id="scan_mode">
            <option value="basic" <?php selected($options['scan_mode'] ?? '', 'basic'); ?>>Basic Scan (WP Folders Only)</option>
            <option value="full" <?php selected($options['scan_mode'] ?? '', 'full'); ?>>Full Scan (Entire WordPress Site)</option>
            <option value="custom" <?php selected($options['scan_mode'] ?? '', 'custom'); ?>>Custom Scan (Select Folders)</option>
        </select>
        <?php
    }
    
    public function excludedFilesCallback(): void {
        $options = get_option('fsc_settings', []);
        ?>
        <tr id="excluded_files_row" style="display: none;">
            <th><label for="excluded_files">Excluded Files & Folders</label></th>
            <td>
                <textarea name="fsc_settings[excluded_files]" id="excluded_files" rows="4" cols="50"><?php echo esc_textarea($options['excluded_files'] ?? ''); ?></textarea>
                <p class="description">Enter file/folder paths to exclude from scans. One per line.</p>
            </td>
        </tr>
        <?php
    }
    
 
    public function autoScanCallback(): void {
        $options = get_option($this->option_name, []);
        ?>
        <input type="checkbox" name="fsc_settings[auto_scan]" value="1" <?php checked($options['auto_scan'] ?? 0, 1); ?>>
        <label>Automatically scan files at scheduled intervals</label>
        <?php
    }

    public function scanScheduleCallback(): void {
        $options = get_option($this->option_name, []);
        ?>
        <select name="fsc_settings[scan_schedule]">
            <option value="daily" <?php selected($options['scan_schedule'] ?? 'weekly', 'daily'); ?>>Daily</option>
            <option value="weekly" <?php selected($options['scan_schedule'] ?? 'weekly', 'weekly'); ?>>Weekly</option>
            <option value="monthly" <?php selected($options['scan_schedule'] ?? 'weekly', 'monthly'); ?>>Monthly</option>
        </select>
        <?php
    }

    public function restrictDeleteCallback(): void {
        $options = get_option($this->option_name, []);
        ?>
        <input type="checkbox" name="fsc_settings[restrict_delete]" value="1" <?php checked($options['restrict_delete'] ?? 1, 1); ?>>
        <label>Only allow administrators to delete files</label>
        <?php
    }

    public function confirmDeleteCallback(): void {
        $options = get_option($this->option_name, []);
        ?>
        <input type="checkbox" name="fsc_settings[confirm_delete]" value="1" <?php checked($options['confirm_delete'] ?? 1, 1); ?>>
        <label>Users must confirm before deleting files</label>
        <?php
    }

    public function enableDebugCallback(): void {
        $options = get_option($this->option_name, []);
        ?>
        <input type="checkbox" name="fsc_settings[enable_debug]" value="1" <?php checked($options['enable_debug'] ?? 0, 1); ?>>
        <label>Log all actions (scans, deletions) for debugging</label>
        <?php
    }
}