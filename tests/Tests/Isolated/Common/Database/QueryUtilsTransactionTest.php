<?php

/**
 * Tests nested transaction handling in QueryUtils.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR
 * @copyright Copyright (c) 2026 OpenEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Database;

use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class QueryUtilsTransactionRunner extends QueryUtils
{
    /**
     * @var list<string>
     */
    public static array $calls = [];

    public static function startTransaction(): void
    {
        self::$calls[] = 'begin';
    }

    public static function commitTransaction(): void
    {
        self::$calls[] = 'commit';
    }

    public static function rollbackTransaction(): void
    {
        self::$calls[] = 'rollback';
    }
}

#[Group('isolated')]
class QueryUtilsTransactionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        QueryUtilsTransactionRunner::$calls = [];
    }

    public function testNestedTransactionsUseTheOutermostDatabaseTransaction(): void
    {
        $result = QueryUtilsTransactionRunner::inTransaction(
            static fn(): string => QueryUtils::inTransaction(static fn(): string => 'saved')
        );

        $this->assertSame('saved', $result);
        $this->assertSame(['begin', 'commit'], QueryUtilsTransactionRunner::$calls);
    }

    public function testNestedFailureRollsBackTheOutermostTransaction(): void
    {
        try {
            QueryUtilsTransactionRunner::inTransaction(
                static fn(): mixed => QueryUtils::inTransaction(
                    static fn(): never => throw new RuntimeException('nested failure')
                )
            );
            $this->fail('The nested exception should propagate');
        } catch (RuntimeException $exception) {
            $this->assertSame('nested failure', $exception->getMessage());
        }
        $this->assertSame(['begin', 'rollback'], QueryUtilsTransactionRunner::$calls);
    }

    public function testSwallowedNestedFailureStillPreventsCommit(): void
    {
        try {
            QueryUtilsTransactionRunner::inTransaction(static function (): void {
                try {
                    QueryUtils::inTransaction(
                        static fn(): never => throw new RuntimeException('nested failure')
                    );
                } catch (RuntimeException) {
                }
            });
            $this->fail('A transaction must not commit after a nested failure');
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'Cannot commit a transaction after a nested transaction failed',
                $exception->getMessage()
            );
        }
        $this->assertSame(['begin', 'rollback'], QueryUtilsTransactionRunner::$calls);
    }
}
