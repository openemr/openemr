<?php

/**
 * Tests for the encounter history assembler.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Steve Wiig <https://github.com/Steve-Wiig>
 * @copyright Copyright (c) 2026 Steve Wiig
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\EdiHistory;

use OpenEMR\Billing\EdiHistory\EncounterEvent;
use OpenEMR\Billing\EdiHistory\EncounterHistory;
use PHPUnit\Framework\TestCase;

class EncounterHistoryTest extends TestCase
{
    private function ev(string $date, string $kind, string $title, string $key = ''): EncounterEvent
    {
        return new EncounterEvent($date, $kind, $title, '', '', '', $key);
    }

    public function testSortsByDateAscending(): void
    {
        $out = EncounterHistory::assemble([
            $this->ev('2026-02-10', 'remittance', 'late'),
            $this->ev('2026-01-20', 'billed', 'early'),
            $this->ev('2026-01-28', 'status', 'mid'),
        ]);
        $this->assertSame(['early', 'mid', 'late'], array_column($out, 'title'));
    }

    public function testUndatedEventsGoLastInInputOrder(): void
    {
        $out = EncounterHistory::assemble([
            $this->ev('', 'ack', 'u1'),
            $this->ev('2026-01-20', 'billed', 'd1'),
            $this->ev('', 'ack', 'u2'),
        ]);
        $this->assertSame(['d1', 'u1', 'u2'], array_column($out, 'title'));
    }

    public function testSameDateKeepsInputOrder(): void
    {
        $out = EncounterHistory::assemble([
            $this->ev('2026-01-28', 'status', 's1'),
            $this->ev('2026-01-28', 'status', 's2'),
        ]);
        $this->assertSame(['s1', 's2'], array_column($out, 'title'));
    }

    public function testRemittanceWithSameContentIsListedOnce(): void
    {
        $out = EncounterHistory::assemble([
            $this->ev('2026-02-10', 'remittance', 'era copy', 'sha1'),
            $this->ev('2026-02-10', 'remittance', 'history copy', 'sha1'),
            $this->ev('2026-02-11', 'remittance', 'other', 'sha2'),
        ]);
        $this->assertSame(['era copy', 'other'], array_column($out, 'title'));
    }

    public function testNonRemittanceEventsAreNotDeduped(): void
    {
        $out = EncounterHistory::assemble([
            $this->ev('2026-01-28', 'status', 's1', 'k'),
            $this->ev('2026-01-28', 'status', 's2', 'k'),
        ]);
        $this->assertCount(2, $out);
    }

    public function testEmptyKeyNeverDedupes(): void
    {
        $out = EncounterHistory::assemble([
            $this->ev('2026-02-10', 'remittance', 'r1'),
            $this->ev('2026-02-10', 'remittance', 'r2'),
        ]);
        $this->assertCount(2, $out);
    }

    public function testEmptyInputGivesEmptyHistory(): void
    {
        $this->assertSame([], EncounterHistory::assemble([]));
    }
}