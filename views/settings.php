<div class="wrap">
    <h1><?php esc_html_e('File Cleaner Settings', 'file-size-cleaner'); ?></h1>
    
    <form method="post" action="options.php">
        <?php
        settings_fields('file-cleaner-settings-group');
        do_settings_sections('file-cleaner-settings');
        submit_button();
        ?>
    </form>
</div>
