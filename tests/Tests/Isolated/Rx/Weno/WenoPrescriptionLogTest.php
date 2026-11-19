<?php

/**
 * The WENO prescription log import keeps the substitution column's meaning.
 *
 * The import used `$line[14] = 'Allowed' ? 1 : 0`, an assignment, so every imported
 * prescription was stored as "substitution allowed". substituteValue() maps the column to
 * the prescriptions.substitute codes (1 allowed, 2 not allowed, 0 not stated).
 *
 * Saves report a database failure to the import instead of ending the request, so the
 * import can count the row as not saved.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    James DuRant <jdurant@ad.unc.edu>
 * @copyright Copyright (c) 2026 James DuRant <jdurant@ad.unc.edu>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Rx\Weno;

use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Rx\Weno\WenoPrescriptionLog;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('weno')]
class WenoPrescriptionLogTest extends TestCase
{
    /**
     * @return array<string, array{mixed, int}>
     */
    public static function columns(): array
    {
        return [
            'allowed' => ['Allowed', 1],
            'allowed, other case and spacing' => [' allowed ', 1],
            'not allowed' => ['Not Allowed', 2],
            'dispense as written' => ['DAW', 2],
            'empty' => ['', 0],
            'missing column' => [null, 0],
            'not a scalar' => [['Allowed'], 0],
        ];
    }

    #[Test]
    #[DataProvider('columns')]
    public function substituteFollowsTheLogColumn(mixed $column, int $expected): void
    {
        $this->assertSame($expected, WenoPrescriptionLog::substituteValue($column));
    }

    /**
     * @return array<string, mixed>
     */
    private static function row(): array
    {
        return [
            'active' => 1, 'date_added' => '2026-01-02 03:04:05', 'patient_id' => 7, 'user_id' => 3,
            'drug' => 'Amoxicillin 250 MG', 'quantity' => '30', 'refills' => '1', 'substitute' => 2,
            'note' => 'Take with food', 'rxnorm_drugcode' => '308182', 'provider_id' => 'P1:L2',
            'prescriptionguid' => 'message-guid',
        ];
    }

    #[Test]
    public function insertWritesTheRowAndReportsSuccess(): void
    {
        $calls = [];
        $log = new WenoPrescriptionLog(function (string $sql, array $binds) use (&$calls): int {
            $calls[] = [$sql, $binds];
            return 11;
        });
        $this->assertTrue($log->insert(self::row()));
        $this->assertCount(1, $calls);
        $this->assertStringStartsWith('INSERT INTO prescriptions', $calls[0][0]);
        $this->assertSame(
            [1, '2026-01-02 03:04:05', 7, 3, 'Amoxicillin 250 MG', '30', '1', 2, 'Take with food', '308182',
                'P1:L2', 'message-guid'],
            $calls[0][1]
        );
    }

    #[Test]
    public function updateMatchesTheMessageAndReportsSuccess(): void
    {
        $calls = [];
        $log = new WenoPrescriptionLog(null, function (string $sql, array $binds) use (&$calls): bool {
            $calls[] = [$sql, $binds];
            return true;
        });
        $this->assertTrue($log->update(self::row()));
        $this->assertCount(1, $calls);
        $this->assertStringStartsWith('UPDATE prescriptions', $calls[0][0]);
        $this->assertStringEndsWith('WHERE indication = ?', $calls[0][0]);
        $this->assertCount(12, $calls[0][1]);
        $this->assertSame('message-guid', $calls[0][1][11]);
    }

    #[Test]
    public function aFailedWriteIsReportedNotThrown(): void
    {
        $fail = static function (string $sql): never {
            throw new SqlQueryException($sql, 'synthetic database failure');
        };
        $log = new WenoPrescriptionLog($fail, $fail);
        $this->assertSame('synthetic database failure', $log->insert(self::row()));
        $this->assertSame('synthetic database failure', $log->update(self::row()));
    }

    #[Test]
    public function missingColumnsAreBoundAsNull(): void
    {
        $binds = [];
        $log = new WenoPrescriptionLog(function (string $sql, array $values) use (&$binds): int {
            $binds = $values;
            return 1;
        });
        $this->assertTrue($log->insert(['drug' => 'Only a drug']));
        $this->assertCount(12, $binds);
        $this->assertSame('Only a drug', $binds[4]);
        $this->assertSame([null, null, null, null], array_slice($binds, 0, 4));
        $this->assertNull($binds[11]);
    }
}
