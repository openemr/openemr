<?php

/**
 * Tests for the claim filing statements.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Steve Wiig <https://github.com/Steve-Wiig>
 * @copyright Copyright (c) 2026 Steve Wiig
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\EdiHistory;

use OpenEMR\Billing\EdiHistory\ClaimFilingStatements;
use OpenEMR\Billing\EdiHistory\EncounterEvent;
use OpenEMR\Billing\EdiHistory\EncounterHistory;
use PHPUnit\Framework\TestCase;

class ClaimFilingStatementsTest extends TestCase
{
    private function ev(string $date, string $title, string $detail = ''): EncounterEvent
    {
        return new EncounterEvent($date, EncounterHistory::KIND_STATUS, $title, $detail);
    }

    public function testADatedFactBecomesADatedStatement(): void
    {
        $lines = ClaimFilingStatements::statements([
            $this->ev('2026-01-28', 'Claim status from PLAN (Payer)', 'Claim 100-200, Accepted, payer claim number PAYER99'),
        ]);
        $this->assertSame(
            'On 2026-01-28, Claim status from PLAN (Payer). Claim 100-200, Accepted, payer claim number PAYER99.',
            $lines[0]
        );
    }

    public function testAnUndatedFactSaysSo(): void
    {
        $lines = ClaimFilingStatements::statements([$this->ev('', 'Acknowledgment')]);
        $this->assertSame('Undated: Acknowledgment.', $lines[0]);
    }

    public function testNoFactsGiveNoStatements(): void
    {
        $this->assertSame([], ClaimFilingStatements::statements([]));
    }

    public function testDaysFromServiceToEachReceipt(): void
    {
        $out = ClaimFilingStatements::daysFromService('2026-01-20', [
            $this->ev('2026-01-27', 'billed'),
            $this->ev('2026-01-28', 'payer ack'),
            $this->ev('2026-07-10', 'remittance'),
        ]);
        $this->assertSame([7, 8, 171], array_column($out, 'days'));
        $this->assertSame('payer ack', $out[1]['title']);
    }

    public function testAFactBeforeTheServiceDateIsNegative(): void
    {
        $out = ClaimFilingStatements::daysFromService('2026-01-20', [$this->ev('2026-01-18', 'earlier')]);
        $this->assertSame(-2, $out[0]['days']);
    }

    public function testAnUndatedFactGivesNoCount(): void
    {
        $out = ClaimFilingStatements::daysFromService('2026-01-20', [
            $this->ev('', 'undated'),
            $this->ev('2026-01-28', 'dated'),
        ]);
        $this->assertCount(1, $out);
        $this->assertSame('dated', $out[0]['title']);
    }

    public function testNoServiceDateOrAnUnrealOneGivesNoCounts(): void
    {
        $events = [$this->ev('2026-01-28', 'x')];
        $this->assertSame([], ClaimFilingStatements::daysFromService('', $events));
        $this->assertSame([], ClaimFilingStatements::daysFromService('2026-13-40', $events));
    }

    public function testTheSameDayIsZero(): void
    {
        $out = ClaimFilingStatements::daysFromService('2026-01-20', [$this->ev('2026-01-20', 'same day')]);
        $this->assertSame(0, $out[0]['days']);
    }
}