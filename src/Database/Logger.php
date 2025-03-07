<?php
namespace FileSizeCleaner\Database;

class Logger {
    private string $logFile;

    public function __construct(string $logFile = null) {
        $logDir = plugin_dir_path(__FILE__) . '../../logs/';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true); // Create logs directory if it doesn't exist
        }

        $this->logFile = $logFile ?? $logDir . 'actions.log';
    }

    public function logAction(string $action, string $details): void {
        $entry = sprintf("[%s] %s: %s\n", date('Y-m-d H:i:s'), strtoupper($action), $details);
        file_put_contents($this->logFile, $entry, FILE_APPEND);
    }
}
