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

        $rootDirectory = ABSPATH; // 🔥 Scan the full WordPress root directory
        $files = $this->getFilesInDirectory($rootDirectory);

        $totalSize = array_sum(array_column($files, 'size')); // Calculate total size

        wp_send_json_success([
            'totalSize' => $totalSize,
            'files' => $files
        ]);
        
    }

    private function getFilesInDirectory(string $dir): array {
        $results = [];
    
        if (!is_dir($dir)) {
            return [];
        }
    
        $files = scandir($dir);
    
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
    
            $filePath = realpath($dir . DIRECTORY_SEPARATOR . $file);
            $relativePath = str_replace(ABSPATH, '', $filePath); // 🔥 Convert to relative path
            $fileType = is_dir($filePath) ? 'Folder' : 'File';
            $size = is_file($filePath) ? filesize($filePath) : $this->getDirectorySize($filePath);
    
            // 🔥 Recursively scan subfolders and store them inside 'subfiles'
            $subfiles = is_dir($filePath) ? $this->getFilesInDirectory($filePath) : [];
    
            $results[] = [
                'name' => $file,
                'size' => $size,
                'location' => '/' . trim($relativePath, '/'), // 🔥 Ensure proper path format
                'modified' => filemtime($filePath), // 🔥 Correct timestamp
                'type' => $fileType,
                'deletable' => !is_dir($filePath), // 🔥 Only allow file deletions
                'subfiles' => $subfiles // 🔥 Store nested subfiles properly!
            ];
        }
    
        return $results;
    }

    
}
