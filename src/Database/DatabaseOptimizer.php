<?php
namespace FileSizeCleaner\Database;

use wpdb;

class DatabaseOptimizer {
    private wpdb $wpdb;

    public function __construct(wpdb $wpdb) {
        $this->wpdb = $wpdb;
    }

    public function optimize(): void {
        $this->wpdb->query("OPTIMIZE TABLE {$this->wpdb->prefix}posts, {$this->wpdb->prefix}comments, {$this->wpdb->prefix}postmeta");
        $this->wpdb->query("DELETE FROM {$this->wpdb->prefix}options WHERE option_name LIKE '_transient_%'");
        $this->wpdb->query("DELETE FROM {$this->wpdb->prefix}postmeta WHERE meta_key = '_edit_lock'");
    }
}
