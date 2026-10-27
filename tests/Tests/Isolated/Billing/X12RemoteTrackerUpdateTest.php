<?php

/**
 * updated_at written by X12RemoteTracker::update() must use a 24-hour clock.
 *
 * date('h') is 01-12 and carries no meridian, so an afternoon update is stored
 * as a morning time. create() already formats with H.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace {
    // BaseService pulls in custom/code_types.inc.php at load, which queries
    // unless this constant is set. Define it before X12RemoteTracker is loaded.
    if (!defined('OPENEMR_STATIC_ANALYSIS')) {
        define('OPENEMR_STATIC_ANALYSIS', true);
    }
}

namespace OpenEMR\Billing\BillingProcessor {
    // Stubs live in the class namespace so they shadow unqualified date() and
    // sqlStatement() calls. date() stays a pass-through unless this test pins
    // a timestamp. The isolated suite has no database, so sqlStatement() only records
    // the query.
    if (!function_exists('OpenEMR\Billing\BillingProcessor\date')) {
        function date(string $format, ?int $timestamp = null): string
        {
            if ($timestamp === null) {
                $pinned = \OpenEMR\Tests\Isolated\Billing\X12RemoteTrackerUpdateTest::pinnedTimestamp();
                if ($pinned !== null) {
                    $timestamp = $pinned;
                }
            }

            return $timestamp === null ? \date($format) : \date($format, $timestamp);
        }
    }

    if (!function_exists('OpenEMR\Billing\BillingProcessor\sqlStatement')) {
        function sqlStatement(string $statement, mixed $binds = false): bool
        {
            \OpenEMR\Tests\Isolated\Billing\X12RemoteTrackerUpdateTest::captureSql($statement, $binds);
            return true;
        }
    }
}

namespace OpenEMR\Tests\Isolated\Billing {

    use OpenEMR\Billing\BillingProcessor\X12RemoteTracker;
    use OpenEMR\Services\BaseService;
    use PHPUnit\Framework\TestCase;
    use ReflectionClass;

    class X12RemoteTrackerUpdateTest extends TestCase
    {
        private static ?int $pinnedTimestamp = null;

        /** @var array{0: string, 1: mixed}|null */
        private static ?array $captured = null;

        public static function pinnedTimestamp(): ?int
        {
            return self::$pinnedTimestamp;
        }

        public static function captureSql(string $statement, mixed $binds): void
        {
            self::$captured = [$statement, $binds];
        }

        /**
         * @return array{0: string, 1: mixed}|null
         */
        private static function capturedSql(): ?array
        {
            return self::$captured;
        }

        protected function tearDown(): void
        {
            self::$pinnedTimestamp = null;
            self::$captured = null;
        }

        public function testUpdateStoresAfternoonTimestampOnA24HourClock(): void
        {
            // 15:04 is 03:04 on a 12-hour clock with no AM/PM, which is the bug.
            $afternoon = mktime(15, 4, 5, 6, 15, 2026);
            $this->assertNotFalse($afternoon);

            self::$pinnedTimestamp = $afternoon;
            self::$captured = null;

            $tracker = (new ReflectionClass(X12RemoteTracker::class))->newInstanceWithoutConstructor();
            $parent = new ReflectionClass(BaseService::class);
            $parent->getProperty('fields')->setValue($tracker, [
                'id',
                'x12_partner_id',
                'x12_filename',
                'status',
                'claims',
                'messages',
                'created_at',
                'updated_at',
            ]);
            $parent->getProperty('autoIncrements')->setValue($tracker, [
                ['Field' => 'id'],
            ]);

            $tracker->update([
                'id' => 42,
                'status' => X12RemoteTracker::STATUS_SUCCESS,
                'messages' => 'uploaded',
            ]);

            $captured = self::capturedSql();
            $this->assertNotNull($captured);
            [$sql, $binds] = $captured;
            $this->assertIsArray($binds);
            $this->assertStringContainsString('UPDATE x12_remote_tracker', $sql);

            preg_match_all('/`([^`]+)` = \?/', $sql, $columns);
            $updatedAtIndex = array_search('updated_at', $columns[1], true);
            $this->assertNotFalse($updatedAtIndex, 'updated_at should be written by update()');
            $this->assertSame('2026-06-15 15:04:05', $binds[$updatedAtIndex]);
        }
    }
}
