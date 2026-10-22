<?php

/**
 * Tests for the per-action SELECT summary.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Logging\Audit;

use OpenEMR\Common\Logging\Audit\SelectRequestSummary;
use PHPUnit\Framework\TestCase;

class SelectRequestSummaryTest extends TestCase
{
    public function testSeveralSelectsBecomeOneSummary(): void
    {
        $summary = new SelectRequestSummary();
        $summary->add('forms', 'patient-record', true, null);
        $summary->add('patient_data', 'patient-record', true, 4);
        $summary->add('openemr_postcalendar_events', 'scheduling', false, 9);

        $row = $summary->consume('GET /interface/patient_file/summary/demographics.php');

        self::assertSame('patient-record-select', $row->event);
        self::assertSame('patient-record', $row->category);
        self::assertSame(4, $row->patientId);
        self::assertSame(0, $row->success);
        self::assertSame(
            'user action GET /interface/patient_file/summary/demographics.php; selects=3; failed=1; tables=forms,patient_data,openemr_postcalendar_events',
            $row->comments,
        );
        self::assertTrue($summary->isEmpty());
    }

    public function testTableListStopsAfterThirtyNames(): void
    {
        $summary = new SelectRequestSummary();
        for ($i = 1; $i <= 31; $i++) {
            $summary->add('table_' . $i, 'other', true, null);
        }

        $row = $summary->consume('cli job');

        self::assertStringContainsString('selects=31', $row->comments);
        self::assertStringContainsString('tables=table_1,', $row->comments);
        self::assertStringEndsWith(',...', $row->comments);
        self::assertStringNotContainsString('table_31', $row->comments);
    }
}
