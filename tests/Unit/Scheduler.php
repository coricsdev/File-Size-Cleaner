<?php
use PHPUnit\Framework\TestCase;
use FileSizeCleaner\Scheduler\Scheduler;
use FileSizeCleaner\FileManager\Cleaner;
use FileSizeCleaner\Database\DatabaseOptimizer;
use FileSizeCleaner\Database\Logger;

class SchedulerTest extends TestCase {
    private Scheduler $scheduler;

    protected function setUp(): void {
        $cleaner = $this->createMock(Cleaner::class);
        $database = $this->createMock(DatabaseOptimizer::class);
        $logger = $this->createMock(Logger::class);

        $this->scheduler = new Scheduler($cleaner, $database, $logger);
    }

    public function testRemoveScheduledCleanup() {
        $this->scheduler->removeScheduledCleanup();
        $this->assertNull(wp_next_scheduled('fsc_scheduled_cleanup'));
    }
}
