<?php
namespace FileSizeCleaner\FileManager;

class Cleaner {
    
    public function deleteFile(string $filePath): bool {
        if (!file_exists($filePath)) {
            return false;
        }

        if (!is_writable($filePath)) {
            throw new \RuntimeException("File is not writable: $filePath");
        }

        return unlink($filePath);
    }

    public function deleteBulkFiles(array $files): array {
        $deletedFiles = [];

        foreach ($files as $file) {
            try {
                if ($this->deleteFile($file)) {
                    $deletedFiles[] = $file;
                }
            } catch (\RuntimeException $e) {
                error_log("Failed to delete file: " . $e->getMessage());
            }
        }

        return $deletedFiles;
    }
}
