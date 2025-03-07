<?php
namespace FileSizeCleaner\Scheduler;

use FileSizeCleaner\FileManager\Cleaner;
use FileSizeCleaner\Database\DatabaseOptimizer;
use FileSizeCleaner\Database\Logger;

class Scheduler {
    private Cleaner $cleaner;
    private DatabaseOptimizer $database;
    private Logger $logger;

    public function __construct(Cleaner $cleaner, DatabaseOptimizer $database, Logger $logger) {
        $this->cleaner = $cleaner;
        $this->database = $database;
        $this->logger = $logger;
    }

    // Register the custom interval
    public function register(): void {
        add_filter('cron_schedules', [$this, 'add_custom_cron_schedule']);

        if (!wp_next_scheduled('fsc_scheduled_cleanup')) {
            wp_schedule_event(time(), 'every_minute', 'fsc_scheduled_cleanup');
        }

        add_action('fsc_scheduled_cleanup', [$this, 'executeCleanup']);
    }

    // Add a custom schedule for every 1 minute
    public function add_custom_cron_schedule($schedules): array {
        $schedules['every_minute'] = [
            'interval' => 60, // 60 seconds
            'display'  => __('Every Minute', 'file-size-cleaner')
        ];
        return $schedules;
    }

    public function executeCleanup(): void {
        $deletedFiles = $this->cleaner->deleteBulkFiles(['/wp-content/cache/', '/wp-content/backups/']);
        $this->database->optimize();
        $this->logger->logAction('Scheduled Cleanup', json_encode($deletedFiles));
    }

    public function removeScheduledCleanup(): void {
        $timestamp = wp_next_scheduled('fsc_scheduled_cleanup');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'fsc_scheduled_cleanup');
        }
    }
}
