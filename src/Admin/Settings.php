<?php
namespace FileSizeCleaner\Admin;

class Settings {
    public function render(): void {
        require_once plugin_dir_path(__FILE__) . '../../views/settings.php';
    }
}
