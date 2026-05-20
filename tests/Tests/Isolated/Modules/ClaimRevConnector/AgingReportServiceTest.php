<?php

/**
 * Isolated tests for AgingReportService::toCsv().
 *
 * The full report needs a database, but the CSV serializer is pure: it
 * takes the typed AgingEncounter list the report produces and emits the
 * exact byte sequence the export endpoint streams to the client. Tests
 * pin down the column order, the quote-escaping for patient/payer names
 * containing double quotes, and the trailing-newline contract — any of
 * these silently changing breaks downstream spreadsheet imports.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ClaimRevConnector;

use OpenEMR\Modules\ClaimRevConnector\AgingReportService;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../../interface/modules/custom_modules/oe-module-claimrev-connect/src/AgingReportService.php';

class AgingReportServiceTest extends TestCase
{
    public function testToCsvWithEmptyEncountersStillEmitsHeader(): void
    {
        $csv = AgingReportService::toCsv([]);
        $this->assertSame(
            "Patient,Encounter,Service Date,Payer,Age Days,Bucket,Balance,Ins Level,Stmts Sent\n",
            $csv
        );
    }

    public function testToCsvSerializesSingleEncounter(): void
    {
        $csv = AgingReportService::toCsv([
            [
                'pid' => 1,
                'encounter' => 12345,
                'encounterDate' => '2026-01-15',
                'patientName' => 'Doe, Jane',
                'payerName' => 'Acme Health',
                'ageDays' => 47,
                'bucket' => 'days30',
                'balance' => 125.50,
                'lastLevelClosed' => 1,
                'stmtCount' => 0,
            ],
        ]);

        $expected = "Patient,Encounter,Service Date,Payer,Age Days,Bucket,Balance,Ins Level,Stmts Sent\n"
            . '"Doe, Jane",12345,2026-01-15,"Acme Health",47,days30,125.5,1,0' . "\n";

        $this->assertSame($expected, $csv);
    }

    public function testToCsvEscapesEmbeddedDoubleQuotes(): void
    {
        // RFC 4180 quoted field with embedded double quote: "" becomes a single
        // " inside the field. Without this escaping a payer name like
        // 'Aetna "Choice" PPO' would break the column count of every row after
        // it when re-imported.
        $csv = AgingReportService::toCsv([
            [
                'pid' => 1,
                'encounter' => 1,
                'encounterDate' => '2026-01-01',
                'patientName' => 'O"Brien, Pat',
                'payerName' => 'Aetna "Choice" PPO',
                'ageDays' => 10,
                'bucket' => 'current',
                'balance' => 0.0,
                'lastLevelClosed' => 0,
                'stmtCount' => 0,
            ],
        ]);

        $this->assertStringContainsString('"O""Brien, Pat"', $csv);
        $this->assertStringContainsString('"Aetna ""Choice"" PPO"', $csv);
    }

    public function testToCsvEmitsEachEncounterOnSeparateLine(): void
    {
        $csv = AgingReportService::toCsv([
            self::row('Alice', 100),
            self::row('Bob', 101),
            self::row('Carol', 102),
        ]);

        // Header + 3 data rows = 4 \n-terminated lines (and an empty
        // trailing element from the explode after the final \n).
        $lines = explode("\n", $csv);
        $this->assertCount(5, $lines);
        $this->assertSame('', $lines[4]); // trailing newline guaranteed
        $this->assertStringContainsString(',100,', $lines[1]);
        $this->assertStringContainsString(',101,', $lines[2]);
        $this->assertStringContainsString(',102,', $lines[3]);
    }

    /**
     * @return array{pid: int, encounter: int, encounterDate: string, patientName: string, payerName: string, ageDays: int, bucket: string, balance: float, lastLevelClosed: int, stmtCount: int}
     */
    private static function row(string $name, int $encounter): array
    {
        return [
            'pid' => 1,
            'encounter' => $encounter,
            'encounterDate' => '2026-01-15',
            'patientName' => $name,
            'payerName' => 'Acme',
            'ageDays' => 30,
            'bucket' => 'current',
            'balance' => 50.0,
            'lastLevelClosed' => 1,
            'stmtCount' => 0,
        ];
    }
}
