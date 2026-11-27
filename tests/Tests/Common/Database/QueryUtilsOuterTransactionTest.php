<?php

/**
 * Database tests for QueryUtils::inOuterTransaction(): it commits, rolls back
 * when its callback throws, and takes the transactions its callees start
 * into its own.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Common\Database;

use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\TestCase;

final class QueryUtilsOuterTransactionTest extends TestCase
{
    // A temporary table belongs to this connection only, and creating or
    // dropping one doesn't commit an open transaction.
    private const TABLE = 'query_utils_outer_transaction_test';

    protected function setUp(): void
    {
        QueryUtils::sqlStatementThrowException(
            'CREATE TEMPORARY TABLE `' . self::TABLE . '` (`value` VARCHAR(20) NOT NULL) ENGINE=InnoDB',
            [],
            true,
        );
    }

    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException('DROP TEMPORARY TABLE IF EXISTS `' . self::TABLE . '`', [], true);
    }

    public function testCommitsAndReturnsTheCallbacksValue(): void
    {
        $valuesInside = QueryUtils::inOuterTransaction(function (): array {
            $this->insert('outer');
            return $this->values();
        });

        $this->assertSame(['outer'], $valuesInside);
        $this->assertSame(['outer'], $this->values());
    }

    public function testRollsBackWhenTheCallbackThrows(): void
    {
        $caught = null;
        try {
            QueryUtils::inOuterTransaction(function (): never {
                $this->insert('outer');
                throw new \RuntimeException('callback failed');
            });
        } catch (\RuntimeException $e) {
            $caught = $e;
        }

        $this->assertSame('callback failed', $caught?->getMessage());
        $this->assertSame([], $this->values());
    }

    public function testRollsBackAnInnerTransactionThatCommittedWithTheOuterOne(): void
    {
        $caught = null;
        try {
            QueryUtils::inOuterTransaction(function (): never {
                QueryUtils::inTransaction(function (): void {
                    $this->insert('inner');
                });
                $this->insert('outer');
                throw new \RuntimeException('callback failed');
            });
        } catch (\RuntimeException $e) {
            $caught = $e;
        }

        $this->assertSame('callback failed', $caught?->getMessage());
        $this->assertSame([], $this->values());
    }

    public function testAnInnerRollbackThatIsCaughtIsCommittedWithTheOuterOne(): void
    {
        QueryUtils::inOuterTransaction(function (): void {
            try {
                QueryUtils::inTransaction(function (): never {
                    $this->insert('inner');
                    throw new \RuntimeException('inner failed');
                });
            } catch (\RuntimeException) {
                // The callee carries on, so its write stays.
            }
            $this->insert('outer');
        });

        $this->assertSame(['inner', 'outer'], $this->values());
    }

    private function insert(string $value): void
    {
        QueryUtils::sqlStatementThrowException('INSERT INTO `' . self::TABLE . '` (`value`) VALUES (?)', [$value], true);
    }

    /**
     * @return list<string>
     */
    private function values(): array
    {
        $rows = QueryUtils::fetchRecordsNoLog('SELECT `value` FROM `' . self::TABLE . '` ORDER BY `value`', []);
        return array_map(static fn (array $row): string => is_string($row['value'] ?? null) ? $row['value'] : '', $rows);
    }
}
