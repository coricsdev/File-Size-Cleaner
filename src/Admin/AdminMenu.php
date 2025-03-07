<?php
namespace FileSizeCleaner\Admin;

class AdminMenu {
    private Dashboard $dashboard;
    private Settings $settings;

    public function __construct(Dashboard $dashboard, Settings $settings) {
        $this->dashboard = $dashboard;
        $this->settings = $settings;
    }

    public function register(): void {
        add_action('admin_menu', [$this, 'addAdminMenu']);
    }

    public function addAdminMenu(): void {
        add_menu_page(
            __('File Cleaner', 'file-size-cleaner'),
            __('File Cleaner', 'file-size-cleaner'),
            'manage_options',
            'file-size-cleaner',
            [$this->dashboard, 'render'],
            'dashicons-trash'
        );

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
