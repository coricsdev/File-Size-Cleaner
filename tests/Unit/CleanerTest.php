<?php
use PHPUnit\Framework\TestCase;
use FileSizeCleaner\FileManager\Cleaner;

class CleanerTest extends TestCase {

    private string $testFilePath;

    protected function setUp(): void {
        $this->testFilePath = __DIR__ . '/test-file.txt';
        file_put_contents($this->testFilePath, "Test Content");
    }

    protected function tearDown(): void {
        if (file_exists($this->testFilePath)) {
            unlink($this->testFilePath);
        }
    }

    public function testDeleteFileSuccess() {
        $cleaner = new Cleaner();
        $result = $cleaner->deleteFile($this->testFilePath);

        $this->assertTrue($result);
        $this->assertFileDoesNotExist($this->testFilePath);
    }

    public function testDeleteFileFailsOnNonexistentFile() {
        $cleaner = new Cleaner();
        $result = $cleaner->deleteFile(__DIR__ . '/nonexistent.txt');

        $this->assertFalse($result);
    }
}
