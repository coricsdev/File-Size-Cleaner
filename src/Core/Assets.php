<?php
namespace FileSizeCleaner\Core;

class Assets {
    
    public function register(): void {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('admin_footer', [$this, 'add_inline_js_variables'], 100);
    }

    public function enqueue_admin_assets(): void {
        // ✅ Load admin scripts
        wp_enqueue_script(
            'file-cleaner-admin-scripts', 
            plugin_dir_url(__FILE__) . '../../assets/js/admin-scripts.js', 
            ['jquery'], 
            '1.0.0', 
            true // Load in the FOOTER
        );

        // ✅ Load styles
        wp_enqueue_style(
            'file-cleaner-admin-style', 
            plugin_dir_url(__FILE__) . '../../assets/css/admin-styles.css', 
            [], 
            '1.0.0'
        );
    }

    public function add_inline_js_variables(): void {
        ?>
        <script>
            window.ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
            window.fsc_scan_nonce = "<?php echo wp_create_nonce('fsc_scan_nonce'); ?>";
            console.log("✅ Inline JS variables loaded.");
        </script>
        <?php
    }
}
