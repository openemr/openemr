<?php

/**
 * Tests for the Encounter Record renderer.
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
use OpenEMR\Billing\EdiHistory\EncounterHistoryRenderer;
use PHPUnit\Framework\TestCase;

class EncounterHistoryRendererTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/edih_html_stubs.php';
    }

    public function testShowsTheFactsInOrder(): void
    {
        $html = EncounterHistoryRenderer::html([
            new EncounterEvent('2026-01-27', EncounterHistory::KIND_BILLED, 'Claim billed', 'Claim 100-200', 'batch.txt'),
            new EncounterEvent('2026-01-28', EncounterHistory::KIND_STATUS, 'Claim status from PLAN (Payer)', 'Accepted', 'ack.277'),
        ], '200');
        $this->assertStringContainsString('Encounter Record for 200', $html);
        $this->assertLessThan(strpos($html, 'Claim status'), strpos($html, 'Claim billed'));
        $this->assertStringContainsString('2026-01-28', $html);
    }

    public function testAnUndatedFactSaysSo(): void
    {
        $html = EncounterHistoryRenderer::html([
            new EncounterEvent('', EncounterHistory::KIND_ACK, 'Acknowledgment', ''),
        ], '200');
        $this->assertStringContainsString('undated', $html);
    }

    public function testNothingFoundIsStated(): void
    {
        $this->assertStringContainsString('Nothing found for 200', EncounterHistoryRenderer::html([], '200'));
    }

    public function testFileLinksOnlyWithAToken(): void
    {
        $events = [new EncounterEvent('2026-01-27', EncounterHistory::KIND_BILLED, 'Claim billed', '', 'batch.txt')];
        $this->assertStringNotContainsString('<a ', EncounterHistoryRenderer::html($events, '200'));
        $linked = EncounterHistoryRenderer::html($events, '200', 'tok');
        $this->assertStringContainsString('fname=batch.txt', $linked);
        $this->assertStringContainsString('csrf_token_form=tok', $linked);
    }

    public function testTextIsEscaped(): void
    {
        $html = EncounterHistoryRenderer::html([
            new EncounterEvent('2026-01-27', EncounterHistory::KIND_STATUS, 'Status', '<script>x</script>'),
        ], '200');
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }
}