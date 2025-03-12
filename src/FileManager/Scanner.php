<?php
namespace FileSizeCleaner\FileManager;

class Scanner {
    
    public function __construct() {
        add_action('wp_ajax_fsc_scan_files', [$this, 'scanAjaxHandler']);
        add_action('wp_ajax_nopriv_fsc_scan_files', [$this, 'scanAjaxHandler']); // Allow non-logged-in users (optional)
        add_action('wp_ajax_fsc_delete_file', [$this, 'deleteFileHandler']);
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
                'path' => $relativePath, 
                'subfiles' => $subfiles // 🔥 Store nested subfiles properly!
            ];
        }
    
        return $results;
    }

    private function isCoreFile($path) {
        $coreDirs = [
            ABSPATH . 'wp-admin',
            ABSPATH . 'wp-includes',
            ABSPATH . 'index.php',
            ABSPATH . 'wp-config.php',
            ABSPATH . 'wp-settings.php',
            get_theme_root() . '/' . get_template(), // 🔥 Prevent active theme deletion
            WP_PLUGIN_DIR // 🔥 Prevent plugin folder deletion
        ];
    
        foreach ($coreDirs as $coreDir) {
            if (strpos(realpath($path), realpath($coreDir)) === 0) {
                return true;
            }
        }
    
        return false;
    }
    

    /**
     * Handles the AJAX request for deleting files or folders.
     */
    public function deleteFileHandler() {
        check_ajax_referer('fsc_delete_nonce', 'nonce');
    
        if (!current_user_can('manage_options')) {
            error_log("❌ Unauthorized deletion attempt.");
            wp_send_json_error(['message' => 'Unauthorized action.']);
        }
    
        $path = isset($_POST['path']) ? sanitize_text_field($_POST['path']) : '';
        error_log("🛠️ Delete Request Path: " . $path);
    
        if (empty($path)) {
            error_log("❌ Invalid file path received.");
            wp_send_json_error(['message' => 'Invalid file path.']);
        }
    
        $fullPath = realpath(ABSPATH . ltrim($path, '/'));
        error_log("🛠️ Resolved Full Path: " . $fullPath);
    
        if (!$fullPath || !file_exists($fullPath)) {
            error_log("❌ File does not exist: " . $fullPath);
            wp_send_json_error(['message' => 'File does not exist.']);
        }
    
        if ($this->isCoreFile($fullPath)) {
            error_log("❌ Attempt to delete WordPress core file: " . $fullPath);
            wp_send_json_error(['message' => 'WordPress core files cannot be deleted.']);
        }
    
        // ✅ Delete File or Folder
        $result = is_dir($fullPath) ? $this->deleteFolder($fullPath) : $this->deleteFile($fullPath);
    
        if ($result) {
            error_log("✅ File successfully deleted: " . $fullPath);
            wp_send_json_success(['message' => 'File deleted successfully.']);
        } else {
            error_log("❌ Deletion failed: " . $fullPath);
            wp_send_json_error(['message' => 'Failed to delete file.']);
        }
    }
    
    

    /**
     * Delete a single file.
     */
    private function deleteFile($file) {
        return is_file($file) && unlink($file);
    }

    /**
     * Recursively delete a folder and its contents.
     */
    private function deleteFolder($folder) {
        if (!is_dir($folder)) return false;

        $files = array_diff(scandir($folder), ['.', '..']);
        foreach ($files as $file) {
            $filePath = $folder . DIRECTORY_SEPARATOR . $file;
            is_dir($filePath) ? $this->deleteFolder($filePath) : unlink($filePath);
        }
        return rmdir($folder);
    }

    

    
}
