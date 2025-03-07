<?php
namespace FileSizeCleaner\Core;

class Assets {
    
    public function register(): void {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public function enqueue_admin_assets(): void {
        // Enqueue Alpine.js (lightweight JavaScript framework)
        wp_enqueue_script(
            'alpine-js', 
            'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js', 
            [], 
            '3.12.0', 
            true
        );

        // Enqueue custom Alpine.js logic
        wp_enqueue_script(
            'file-cleaner-alpine-init', 
            plugin_dir_url(__FILE__) . '../../assets/js/alpine-init.js', 
            ['alpine-js'], 
            '1.0.0', 
            true
        );

        // Enqueue compiled SCSS (CSS)
        wp_enqueue_style(
            'file-cleaner-admin-style', 
            plugin_dir_url(__FILE__) . '../../assets/css/admin-styles.css', 
            [], 
            '1.0.0'
        );
    }
}
