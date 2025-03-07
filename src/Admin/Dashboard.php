<?php
namespace FileSizeCleaner\Admin;

class Dashboard {
    public function render(): void {
        require_once plugin_dir_path(__FILE__) . '../../views/dashboard.php';
    }
}
