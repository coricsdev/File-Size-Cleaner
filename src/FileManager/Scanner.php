<?php
namespace FileSizeCleaner\FileManager;

class Scanner {
    
    public function __construct() {
        add_action('wp_ajax_fsc_scan_files', [$this, 'scanAjaxHandler']);
        add_action('wp_ajax_nopriv_fsc_scan_files', [$this, 'scanAjaxHandler']); // Allow non-logged-in users (optional)
    }

    public function scanDirectory(string $dir): int {
        if (!is_dir($dir)) {
            throw new \InvalidArgumentException("Invalid directory: $dir");
        }
        return $this->getDirectorySize($dir);
    }

    private function getDirectorySize(string $dir): int {
        $size = 0;
        $files = scandir($dir);

        if (!$files) {
            return 0;
        }

        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $filePath = $dir . DIRECTORY_SEPARATOR . $file;
                $size += is_file($filePath) ? filesize($filePath) : $this->getDirectorySize($filePath);
            }
        }
        return $size;
    }

    public function scanAjaxHandler(): void {
        check_ajax_referer('fsc_scan_nonce', 'nonce');

        $dir = ABSPATH;
        $size = $this->scanDirectory($dir);

        wp_send_json_success(['size' => $size]);
    }
}
