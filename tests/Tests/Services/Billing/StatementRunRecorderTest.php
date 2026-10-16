<?php

/**
 * Database-backed tests for statement run recording.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR
 * @copyright Copyright (c) 2026 OpenEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing;

use OpenEMR\Billing\StatementRunRecorder;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Tests\Fixtures\EncounterFixtureManager;
use PHPUnit\Framework\TestCase;

class StatementRunRecorderTest extends TestCase
{
    private EncounterFixtureManager $encounterFixtures;
    private int $encounterId;

    protected function setUp(): void
    {
        $this->encounterFixtures = new EncounterFixtureManager();
        $this->encounterFixtures->installFixtures();
        $encounter = QueryUtils::querySingleRow(
            "SELECT id FROM form_encounter WHERE reason = ?",
            ['test-fixture-Complains of nausea, loose stools and weakness.']
        );
        if (!is_array($encounter)) {
            $this->fail('Encounter fixture was not created');
        }
        $encounterId = filter_var($encounter['id'] ?? null, FILTER_VALIDATE_INT);
        if ($encounterId === false) {
            $this->fail('Encounter fixture ID was not an integer');
        }
        $this->encounterId = $encounterId;
        QueryUtils::sqlStatementThrowException(
            "UPDATE form_encounter SET stmt_count = 2, last_stmt_date = ? WHERE id = ?",
            ['2026-10-01', $this->encounterId]
        );
    }

    protected function tearDown(): void
    {
        $this->encounterFixtures->removeFixtures();
    }

    public function testProducedStatementUpdatesCountAndDate(): void
    {
        $recorded = StatementRunRecorder::recordIfProduced(
            [$this->encounterId],
            'Rendered statement content',
            '2026-10-16',
            false,
            false
        );

        $this->assertTrue($recorded);
        $this->assertEncounterStatementState(3, '2026-10-16');
    }

    public function testEmailFailureDoesNotUndoProducedStatement(): void
    {
        $recorded = StatementRunRecorder::recordIfProduced(
            [$this->encounterId],
            'Rendered statement content',
            '2026-10-16',
            false,
            false
        );

        $this->assertTrue($recorded);
        try {
            throw new \RuntimeException('Simulated email delivery failure');
        } catch (\RuntimeException) {
            $this->assertEncounterStatementState(3, '2026-10-16');
        }
    }

    public function testMinimumPrintExclusionDoesNotUpdateCountOrDate(): void
    {
        $recorded = StatementRunRecorder::recordIfProduced(
            [$this->encounterId],
            '',
            '2026-10-16',
            false,
            false
        );

        $this->assertFalse($recorded);
        $this->assertEncounterStatementState(2, '2026-10-01');
    }

    public function testWithoutUpdateDoesNotChangeCountOrDate(): void
    {
        $recorded = StatementRunRecorder::recordIfProduced(
            [$this->encounterId],
            'Rendered statement content',
            '2026-10-16',
            true,
            false
        );

        $this->assertFalse($recorded);
        $this->assertEncounterStatementState(2, '2026-10-01');
    }

    public function testDebugRunDoesNotChangeCountOrDate(): void
    {
        $recorded = StatementRunRecorder::recordIfProduced(
            [$this->encounterId],
            'Rendered statement content',
            '2026-10-16',
            false,
            true
        );

        $this->assertFalse($recorded);
        $this->assertEncounterStatementState(2, '2026-10-01');
    }

    public function testDuePatientRunWithoutProducedStatementDoesNotChangeCountOrDate(): void
    {
        $recorded = StatementRunRecorder::recordIfProduced(
            [$this->encounterId],
            '',
            '2026-10-16',
            false,
            false
        );

        $this->assertFalse($recorded);
        $this->assertEncounterStatementState(2, '2026-10-01');
    }

    private function assertEncounterStatementState(int $count, ?string $date): void
    {
        $encounter = QueryUtils::querySingleRow(
            "SELECT stmt_count, last_stmt_date FROM form_encounter WHERE id = ?",
            [$this->encounterId]
        );
        if (!is_array($encounter)) {
            $this->fail('Encounter fixture was not found');
        }
        $actualCount = filter_var($encounter['stmt_count'] ?? null, FILTER_VALIDATE_INT);
        if ($actualCount === false) {
            $this->fail('Encounter statement count was not an integer');
        }
        $actualDate = $encounter['last_stmt_date'] ?? null;
        if ($actualDate !== null && !is_string($actualDate)) {
            $this->fail('Encounter statement date was not a string or null');
        }

        $this->assertSame($count, $actualCount);
        $this->assertSame($date, $actualDate);
    }
}
