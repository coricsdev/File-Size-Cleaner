<?php
use PHPUnit\Framework\TestCase;
use FileSizeCleaner\Database\DatabaseOptimizer;

class DatabaseOptimizerTest extends TestCase {
    private $wpdbMock;
    private DatabaseOptimizer $databaseOptimizer;

    protected function setUp(): void {
        global $wpdb;
        $this->wpdbMock = $this->createMock(get_class($wpdb));
        $this->databaseOptimizer = new DatabaseOptimizer($this->wpdbMock);
    }

    public function testOptimizeDatabaseCallsQuery() {
        $this->wpdbMock->expects($this->atLeastOnce())
                       ->method('query')
                       ->with($this->stringContains('OPTIMIZE TABLE'));

        $this->databaseOptimizer->optimize();
    }
}
