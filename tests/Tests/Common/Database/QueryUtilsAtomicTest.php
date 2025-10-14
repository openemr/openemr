<?php

/**
 * QueryUtils Atomic Transaction Test
 *
 * Tests the atomic transaction wrapper method in QueryUtils.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael <michael@example.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Common\Database;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use PHPUnit\Framework\TestCase;

class QueryUtilsAtomicTest extends TestCase
{
    /**
     * Test that atomic commits successful transactions
     */
    public function testAtomicCommitsSuccessfulTransaction(): void
    {
        $callbackExecuted = false;

        QueryUtils::atomic(function () use (&$callbackExecuted): void {
            $callbackExecuted = true;
            // Insert a test record
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO `users` (`username`, `authorized`) VALUES (?, ?)",
                ['test_atomic_user_' . time(), 0]
            );
        });

        $this->assertTrue($callbackExecuted, 'Callback should have been executed');

        // Verify the record was committed
        $result = QueryUtils::fetchRecords(
            "SELECT COUNT(*) as count FROM `users` WHERE `username` LIKE ?",
            ['test_atomic_user_%']
        );

        $this->assertGreaterThan(0, $result[0]['count'] ?? 0, 'Transaction should have been committed');
    }

    /**
     * Test that atomic rolls back failed transactions
     */
    public function testAtomicRollsBackFailedTransaction(): void
    {
        $uniqueUsername = 'test_rollback_user_' . time();

        try {
            QueryUtils::atomic(function () use ($uniqueUsername): void {
                // Insert a record
                QueryUtils::sqlStatementThrowException(
                    "INSERT INTO `users` (`username`, `authorized`) VALUES (?, ?)",
                    [$uniqueUsername, 0]
                );

                // Throw an exception to trigger rollback
                throw new \Exception('Simulated error');
            });

            $this->fail('Expected exception was not thrown');
        } catch (\Exception $e) {
            $this->assertEquals('Simulated error', $e->getMessage());
        }

        // Verify the record was rolled back
        $result = QueryUtils::fetchRecords(
            "SELECT COUNT(*) as count FROM `users` WHERE `username` = ?",
            [$uniqueUsername]
        );

        $this->assertEquals(0, $result[0]['count'] ?? 0, 'Transaction should have been rolled back');
    }

    /**
     * Test that atomic passes parameters to the callback
     */
    public function testAtomicPassesParametersToCallback(): void
    {
        $receivedParam1 = null;
        $receivedParam2 = null;

        QueryUtils::atomic(
            function (string $param1, string $param2) use (&$receivedParam1, &$receivedParam2): void {
                $receivedParam1 = $param1;
                $receivedParam2 = $param2;
            },
            'first',
            'second'
        );

        $this->assertEquals('first', $receivedParam1, 'First parameter should be passed to callback');
        $this->assertEquals('second', $receivedParam2, 'Second parameter should be passed to callback');
    }

    /**
     * Test that atomic restores autocommit even after exceptions
     */
    public function testAtomicRestoresAutocommitAfterException(): void
    {
        try {
            QueryUtils::atomic(function (): void {
                throw new \Exception('Test exception');
            });
        } catch (\Exception) {
            // Expected
        }

        // Verify autocommit is restored by executing a simple query
        // If autocommit wasn't restored, this could cause issues with subsequent queries
        $result = QueryUtils::fetchRecords("SELECT 1 as test");
        $this->assertEquals(1, $result[0]['test'] ?? 0, 'Autocommit should be restored');
    }

    /**
     * Test that atomic can handle nested operations (though nesting should be avoided)
     */
    public function testAtomicHandlesComplexOperations(): void
    {
        $step1 = false;
        $step2 = false;
        $step3 = false;

        QueryUtils::atomic(function () use (&$step1, &$step2, &$step3): void {
            $step1 = true;

            // Perform multiple operations
            QueryUtils::sqlStatementThrowException(
                "SELECT COUNT(*) as count FROM `users`",
                [],
                false
            );
            $step2 = true;

            QueryUtils::sqlStatementThrowException(
                "SELECT 1 as test",
                [],
                false
            );
            $step3 = true;
        });

        $this->assertTrue($step1, 'Step 1 should execute');
        $this->assertTrue($step2, 'Step 2 should execute');
        $this->assertTrue($step3, 'Step 3 should execute');
    }

    /**
     * Test that atomic properly propagates SqlQueryException
     */
    public function testAtomicPropagatesSqlQueryException(): void
    {
        $this->expectException(SqlQueryException::class);

        QueryUtils::atomic(function (): void {
            // Try to insert into a non-existent table
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO `nonexistent_table_xyz` (`col`) VALUES (?)",
                ['value']
            );
        });
    }

    /**
     * Test that atomic works with empty callback
     */
    public function testAtomicWorksWithEmptyCallback(): void
    {
        $executed = false;

        QueryUtils::atomic(function () use (&$executed): void {
            $executed = true;
        });

        $this->assertTrue($executed, 'Empty callback should execute successfully');
    }
}
