<?php
namespace FileSizeCleaner\Core;

use FileSizeCleaner\Admin\AdminMenu;
use FileSizeCleaner\Admin\Dashboard;
use FileSizeCleaner\Admin\Settings;
use FileSizeCleaner\Core\Assets;
use FileSizeCleaner\FileManager\Scanner;
use FileSizeCleaner\FileManager\Cleaner;
use FileSizeCleaner\Database\DatabaseOptimizer;
use FileSizeCleaner\Database\Logger;
use FileSizeCleaner\Scheduler\Scheduler;

class Plugin {
    private Assets $assets;
    private AdminMenu $adminMenu;
    private Scanner $scanner;
    private Cleaner $cleaner;
    private DatabaseOptimizer $database;
    private Logger $logger;
    private Scheduler $scheduler;

    public function __construct() {
        global $wpdb;

        $dashboard = new Dashboard();
        $settings = new Settings();

        $this->assets = new Assets();
        $this->adminMenu = AdminMenu::getInstance($dashboard, $settings); // ✅ Use Singleton Method
        $this->scanner = new Scanner();
        $this->cleaner = new Cleaner();
        $this->database = new DatabaseOptimizer($wpdb);
        $this->logger = new Logger();
        $this->scheduler = new Scheduler($this->cleaner, $this->database, $this->logger);
    }

    public function run(): void {
        $this->assets->register();
        $this->adminMenu->register();
        $this->scheduler->register();
    }

    public function getScheduler(): Scheduler {
        return $this->scheduler;
    }
}
