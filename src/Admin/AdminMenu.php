<?php
namespace FileSizeCleaner\Admin;

class AdminMenu {
    private static ?AdminMenu $instance = null;
    private Dashboard $dashboard;
    private Settings $settings;

    private function __construct(Dashboard $dashboard, Settings $settings) {
        $this->dashboard = $dashboard;
        $this->settings = $settings;
    }

    public static function getInstance(Dashboard $dashboard, Settings $settings): AdminMenu {
        if (self::$instance === null) {
            self::$instance = new self($dashboard, $settings);
        }
        return self::$instance;
    }

    public function register(): void {
        if (!has_action('admin_menu', [$this, 'addAdminMenu'])) {
            add_action('admin_menu', [$this, 'addAdminMenu']);
        }
    }

    public function addAdminMenu(): void {
        // ✅ Ensure menu is not duplicated
        global $submenu;
        if (isset($submenu['file-size-cleaner'])) {
            return;
        }

        // ✅ Add Main "File Cleaner" Menu
        add_menu_page(
            __('File Cleaner', 'file-size-cleaner'),
            __('File Cleaner', 'file-size-cleaner'),
            'manage_options',
            'file-size-cleaner',
            [$this->dashboard, 'render'],
            'dashicons-trash'
        );

        // ✅ Add ONLY ONE Settings Page
        add_submenu_page(
            'file-size-cleaner',
            __('Settings', 'file-size-cleaner'),
            __('Settings', 'file-size-cleaner'),
            'manage_options',
            'file-size-cleaner-settings',
            [$this->settings, 'render']
        );
    }
}
