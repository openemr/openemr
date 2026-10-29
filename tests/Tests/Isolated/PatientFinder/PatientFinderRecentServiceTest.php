<?php

/**
 * Isolated PatientFinderRecentService Test
 *
 * Verifies the Recent Patients assembly: invalid recent_patient_columns are
 * dropped, date columns are DATE_FORMAT-ted per the display settings, and one
 * row is produced per recent patient — all without a database.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Open Plan IT Ltd
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PatientFinder;

use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Services\PatientFinder\PatientFinderRecentService;
use PHPUnit\Framework\TestCase;

class PatientFinderRecentServiceTest extends TestCase
{
    public function testBuildsHeadersAndFormattedRecentRows(): void
    {
        $fetch = static fn(string $sql, array $binds): array => str_contains($sql, 'recent_patient_columns')
            ? [
                ['option_id' => 'name', 'title' => 'Name'],
                ['option_id' => '*', 'title' => 'All'],       // dropped: asterisk
                ['option_id' => 'bad', 'title' => 'Bad'],     // dropped: fails validation
                ['option_id' => 'DOB', 'title' => 'DOB'],
            ]
            : [];

        $escaper = static function (string $col, array $tables): string {
            if ($col === 'bad') {
                throw new SqlQueryException('', 'invalid column');
            }
            return '`' . $col . '`';
        };

        $capturedSql = null;
        $sqlQuery = static function (string $sql, array $binds) use (&$capturedSql): array {
            $capturedSql = $sql;
            return ['pid' => $binds[0]];
        };

        $service = new PatientFinderRecentService(
            $fetch,
            static fn(): array => [['pid' => 1], ['pid' => 2]],
            static fn(): array => ['date' => ['DOB'], 'datetime' => []],
            $sqlQuery,
            $escaper,
            1, // MM/DD/YYYY
            0
        );

        $result = $service->build();

        $headerIds = array_map(static fn(array $h): string => (string) $h['option_id'], $result['headers']);
        $this->assertSame(['name', 'DOB'], $headerIds);

        $this->assertSame(
            "SELECT pid, `name`, DATE_FORMAT(`DOB`, '%m/%d/%Y') AS `DOB` FROM patient_data WHERE pid = ?",
            $capturedSql
        );

        $this->assertCount(2, $result['rp']);
        $this->assertSame(['pid' => 1], $result['rp'][0]);
        $this->assertSame(['pid' => 2], $result['rp'][1]);
    }

    public function testDatetimeColumnIncludesTimeFormat(): void
    {
        $capturedSql = null;
        $service = new PatientFinderRecentService(
            static fn(string $sql, array $binds): array => str_contains($sql, 'recent_patient_columns')
                ? [['option_id' => 'last_seen', 'title' => 'Last Seen']]
                : [],
            static fn(): array => [['pid' => 7]],
            static fn(): array => ['date' => [], 'datetime' => ['last_seen']],
            static function (string $sql, array $binds) use (&$capturedSql): array {
                $capturedSql = $sql;
                return ['pid' => 7];
            },
            static fn(string $col, array $tables): string => '`' . $col . '`',
            0, // YYYY-MM-DD
            1  // AM/PM time
        );

        $service->build();

        $this->assertStringContainsString(
            "DATE_FORMAT(`last_seen`, '%Y-%m-%d %r') AS `last_seen`",
            (string) $capturedSql
        );
    }
}
