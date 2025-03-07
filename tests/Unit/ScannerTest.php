<?php
use PHPUnit\Framework\TestCase;
use FileSizeCleaner\FileManager\Scanner;

class ScannerTest extends TestCase {

    public function testScanDirectoryReturnsInt() {
        $scanner = new Scanner();
        $size = $scanner->scanDirectory(__DIR__); // Scan the test directory

        $this->assertIsInt($size);
        $this->assertGreaterThan(0, $size); // Should return a positive number
    }

    public function testInvalidDirectoryThrowsException() {
        $this->expectException(InvalidArgumentException::class);

        $scanner = new Scanner();
        $scanner->scanDirectory('/invalid/path');
    }
}
