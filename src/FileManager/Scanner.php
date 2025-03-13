<?php
namespace FileSizeCleaner\FileManager;

namespace FileSizeCleaner\FileManager;

class Scanner {
    
    private string $option_name = 'fsc_settings';

    public function __construct() {
        add_action('wp_ajax_fsc_scan_files', [$this, 'scanAjaxHandler']);
        add_action('wp_ajax_fsc_delete_file', [$this, 'deleteFileHandler']);
    }

    public function scanAjaxHandler(): void {
        check_ajax_referer('fsc_scan_nonce', 'nonce');

        $settings = get_option($this->option_name, []);
        $scan_mode = $settings['scan_mode'] ?? 'basic';
        $excluded_files = isset($settings['excluded_files']) ? explode("\n", $settings['excluded_files']) : [];

        $files = [];

        switch ($scan_mode) {
            case 'basic':
                $files = $this->runBasicScan($excluded_files);
                break;
            case 'full':
                $files = $this->runFullScan($excluded_files);
                break;
            case 'custom':
                $files = $this->runCustomScan($excluded_files);
                break;
            default:
                wp_send_json_error(['message' => 'Invalid scan mode selected.']);
        }

        $totalSize = array_sum(array_column($files, 'size'));

        wp_send_json_success([
            'totalSize' => $totalSize,
            'files' => $files
        ]);
    }

    /**
     * Basic Scan: Scans WordPress core folders only.
     */
    private function runBasicScan(array $excluded_files): array {
        $wp_directories = [
            'wp-admin'    => ABSPATH . 'wp-admin',
            'wp-includes' => ABSPATH . 'wp-includes',
            'wp-content'  => WP_CONTENT_DIR, // Parent folder
        ];

        $results = [];

        foreach ($wp_directories as $name => $path) {
            if (is_dir($path)) {
                $subfiles = ($name === 'wp-content') 
                    ? $this->getWpContentSubfolders($excluded_files) 
                    : $this->getFilesInDirectory($path, $excluded_files);

                $results[] = [
                    'name' => basename($path),
                    'location' => '/' . trim($name, '/'),
                    'type' => 'Folder',
                    'size' => $this->getDirectorySize($path),
                    'modified' => filemtime($path),
                    'deletable' => false,
                    'subfiles' => $subfiles
                ];
            }
        }

        return $results;
    }

    /**
     * Full Scan: Scans the entire WordPress site.
     */
    private function runFullScan(array $excluded_files): array {
        return $this->getFilesInDirectory(ABSPATH, $excluded_files);
    }

    /**
     * Custom Scan: Scans user-defined folders.
     */
    private function runCustomScan(array $excluded_files): array {
        $settings = get_option($this->option_name, []);
        $custom_folders = isset($settings['custom_folders']) ? explode("\n", $settings['custom_folders']) : [];

        $results = [];

        foreach ($custom_folders as $folder) {
            $folder = trim($folder);
            $fullPath = ABSPATH . ltrim($folder, '/');

            if (is_dir($fullPath)) {
                $results[] = [
                    'name' => basename($folder),
                    'location' => '/' . trim($folder, '/'),
                    'type' => 'Folder',
                    'size' => $this->getDirectorySize($fullPath),
                    'modified' => filemtime($fullPath),
                    'deletable' => false,
                    'subfiles' => $this->getFilesInDirectory($fullPath, $excluded_files)
                ];
            }
        }

        return $results;
    }

    /**
     * Ensures that wp-content has nested subfolders (plugins, themes, uploads).
     */
    private function getWpContentSubfolders(array $excluded_files): array {
        $subfolders = [
            'plugins'  => WP_PLUGIN_DIR,
            'themes'   => get_theme_root(),
            'uploads'  => WP_CONTENT_DIR . '/uploads'
        ];

        $results = [];

        foreach ($subfolders as $name => $path) {
            if (is_dir($path)) {
                $results[] = [
                    'name' => $name,
                    'location' => '/wp-content/' . $name,
                    'type' => 'Folder',
                    'size' => $this->getDirectorySize($path),
                    'modified' => filemtime($path),
                    'deletable' => false,
                    'subfiles' => $this->getFilesInDirectory($path, $excluded_files)
                ];
            }
        }

        return $results;
    }

    /**
     * Recursively retrieves file data from a directory while respecting exclusions.
     */
    private function getFilesInDirectory(string $dir, array $excluded_files = []): array {
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
            $relativePath = str_replace(ABSPATH, '', $filePath);

            if (in_array($relativePath, $excluded_files, true)) {
                continue;
            }

            $fileType = is_dir($filePath) ? 'Folder' : 'File';
            $size = is_file($filePath) ? filesize($filePath) : $this->getDirectorySize($filePath);

            $results[] = [
                'name' => $file,
                'size' => $size,
                'location' => '/' . trim($relativePath, '/'),
                'modified' => filemtime($filePath),
                'type' => $fileType,
                'deletable' => !$this->isCoreFile($filePath),
                'path' => $relativePath,
                'subfiles' => is_dir($filePath) ? $this->getFilesInDirectory($filePath, $excluded_files) : []
            ];
        }

        return $results;
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

    private function isCoreFile($path) {
        $coreDirs = [
            ABSPATH . 'wp-admin',
            ABSPATH . 'wp-includes',
            ABSPATH . 'index.php',
            ABSPATH . 'wp-config.php',
            ABSPATH . 'wp-settings.php',
            get_theme_root() . '/' . get_template(),
            WP_PLUGIN_DIR
        ];
    
        foreach ($coreDirs as $coreDir) {
            if (strpos(realpath($path), realpath($coreDir)) === 0) {
                return true;
            }
        }
    
        return false;
    }
}


