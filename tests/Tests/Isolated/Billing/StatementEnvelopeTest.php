<?php

/**
 * Envelope window presets and address fitting.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\StatementEnvelope;
use PHPUnit\Framework\TestCase;

final class StatementEnvelopeTest extends TestCase
{
    public function testDefaultIsNotWindowed(): void
    {
        $env = new StatementEnvelope(StatementEnvelope::PROFILE_DEFAULT);
        $this->assertFalse($env->isWindowed());
        $this->assertNull($env->geometry());
        $this->assertSame('', $env->windowCss());
        $this->assertSame('', $env->windowHtml('Clinic', '1 Main', 'Town, ND, 00000', ['Pat']));
    }

    public function testHash9UsesThreeEighthsLeftCut(): void
    {
        $g = (new StatementEnvelope(StatementEnvelope::PROFILE_HASH9))->geometry();
        $this->assertNotNull($g);
        $this->assertEqualsWithDelta(0.375, $g['left'], 0.0001);
        $this->assertEqualsWithDelta(0.6875, $g['return']['top'], 0.0001);
        $this->assertEqualsWithDelta(1.1875, $g['return']['h'], 0.0001);
        $this->assertEqualsWithDelta(3.5, $g['return']['w'], 0.0001);
        $this->assertEqualsWithDelta(2.375, $g['to']['top'], 0.0001);
        $this->assertEqualsWithDelta(1.0, $g['to']['h'], 0.0001);
        $this->assertEqualsWithDelta(4.0, $g['to']['w'], 0.0001);
        $this->assertEqualsWithDelta(11.0 / 3.0, $g['panel'], 0.0001);
    }

    public function testHash10UsesHalfInchLeftCut(): void
    {
        $g = (new StatementEnvelope(StatementEnvelope::PROFILE_HASH10))->geometry();
        $this->assertNotNull($g);
        $this->assertEqualsWithDelta(0.5, $g['left'], 0.0001);
        $this->assertEqualsWithDelta(0.625, $g['return']['top'], 0.0001);
        $this->assertEqualsWithDelta(1.0, $g['return']['h'], 0.0001);
        $this->assertEqualsWithDelta(3.5, $g['return']['w'], 0.0001);
        $this->assertEqualsWithDelta(2.0, $g['to']['top'], 0.0001);
        $this->assertEqualsWithDelta(1.375, $g['to']['h'], 0.0001);
        $this->assertEqualsWithDelta(4.0, $g['to']['w'], 0.0001);
    }

    public function testLongLineWrapsAndShrinks(): void
    {
        $long = 'Northwest Heart Institute of North Dakota Billing Office Suite';
        [$pt, $lines] = StatementEnvelope::fitLines([$long], 3.4, 1.0, 16.0);
        $this->assertNotEmpty($lines);
        $this->assertLessThanOrEqual(16.0, $pt);
        $this->assertGreaterThanOrEqual(7.0, $pt);
        $joined = implode(' ', $lines);
        $this->assertStringContainsString('Northwest', $joined);
        $this->assertGreaterThan(1, count($lines));
    }

    public function testWindowHtmlEscapesAndIsFirstPageOnly(): void
    {
        $env = new StatementEnvelope(StatementEnvelope::PROFILE_HASH9);
        $html = $env->windowHtml('Clinic & Co', "1 Main\nSte 2", 'Town, ND, 00000', ['Pat <X>']);
        $this->assertStringContainsString('stmt-env-windows', $html);
        $this->assertStringContainsString('Clinic &amp; Co', $html);
        $this->assertStringContainsString('Pat &lt;X&gt;', $html);
        $this->assertStringNotContainsString('position:absolute', $html);
    }
}
