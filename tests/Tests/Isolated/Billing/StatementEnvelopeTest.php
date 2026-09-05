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

    public function testInchCentimeterConversionKeepsTenthousandthCm(): void
    {
        $this->assertEqualsWithDelta(2.54, StatementEnvelope::inchesToCentimeters(1.0), 0.0001);
        $this->assertEqualsWithDelta(0.9525, StatementEnvelope::inchesToCentimeters(0.375), 0.0001);
        $this->assertEqualsWithDelta(9.8425, StatementEnvelope::inchesToCentimeters(3.875), 0.0001);
        $this->assertEqualsWithDelta(1.0, StatementEnvelope::centimetersToInches(2.54), 0.000001);
        $this->assertEqualsWithDelta(0.375, StatementEnvelope::centimetersToInches(0.9525), 0.000001);
        $this->assertEqualsWithDelta(3.875, StatementEnvelope::centimetersToInches(9.8425), 0.000001);
        $this->assertEqualsWithDelta(0.0001, StatementEnvelope::inchesToCentimeters(StatementEnvelope::centimetersToInches(0.0001)), 0.0001);
    }

    public function testParseLengthReadsCentimeters(): void
    {
        $this->assertEqualsWithDelta(1.0, StatementEnvelope::parseLength('2.54', 'cm'), 0.000001);
        $this->assertEqualsWithDelta(1.0, StatementEnvelope::parseLength('2.54 cm', 'in'), 0.000001);
        $this->assertEqualsWithDelta(3.875, StatementEnvelope::parseLength('9.8425', 'cm'), 0.000001);
        $this->assertEqualsWithDelta(0.375, StatementEnvelope::parseLength('3/8', 'in'), 0.000001);
    }

    public function testParseInchReadsBoxFractions(): void
    {
        $this->assertEqualsWithDelta(0.375, StatementEnvelope::parseInch('3/8'), 0.0001);
        $this->assertEqualsWithDelta(0.5, StatementEnvelope::parseInch('1/2'), 0.0001);
        $this->assertEqualsWithDelta(1.1875, StatementEnvelope::parseInch('1-3/16'), 0.0001);
        $this->assertEqualsWithDelta(3.875, StatementEnvelope::parseInch('3 7/8'), 0.0001);
        $this->assertEqualsWithDelta(3.5, StatementEnvelope::parseInch('3-1/2'), 0.0001);
        $this->assertEqualsWithDelta(2.0, StatementEnvelope::parseInch('2'), 0.0001);
        $this->assertEqualsWithDelta(0.375, StatementEnvelope::parseInch('0.375'), 0.0001);
        $this->assertEqualsWithDelta(1.1875, StatementEnvelope::parseInch('1.1875'), 0.0001);
        $this->assertNull(StatementEnvelope::parseInch(''));
    }

    public function testCustomCartonNumbersMatchHash9Cut(): void
    {
        $hash9 = StatementEnvelope::presetHash9();
        $legacy = new StatementEnvelope(StatementEnvelope::PROFILE_CUSTOM, [
            'envelope_height' => '3-7/8',
            'return_height' => '1-3/16',
            'return_width' => '3-1/2',
            'return_left' => '3/8',
            'return_bottom' => '2',
            'to_height' => '1',
            'to_width' => '4',
            'to_left' => '3/8',
            'to_bottom' => '1/2',
        ]);
        $lg = $legacy->geometry();
        $this->assertNotNull($lg);
        $this->assertEqualsWithDelta($hash9['return']['top'], $lg['return']['top'], 0.0001);
        $this->assertEqualsWithDelta($hash9['to']['top'], $lg['to']['top'], 0.0001);
        $this->assertEqualsWithDelta($hash9['left'], $lg['left'], 0.0001);

        $cm = new StatementEnvelope(StatementEnvelope::PROFILE_CUSTOM, [
            'units' => 'cm',
            'envelope_height' => '9.8425',
            'return_height' => '3.0163',
            'return_width' => '8.89',
            'return_left' => '0.9525',
            'return_bottom' => '5.08',
            'to_height' => '2.54',
            'to_width' => '10.16',
            'to_left' => '0.9525',
            'to_bottom' => '1.27',
        ]);
        $cg = $cm->geometry();
        $this->assertNotNull($cg);
        $this->assertEqualsWithDelta($hash9['left'], $cg['left'], 0.001);
        $this->assertEqualsWithDelta($hash9['return']['top'], $cg['return']['top'], 0.001);

        $empty = new StatementEnvelope(StatementEnvelope::PROFILE_CUSTOM, [
            'return_width' => 0,
        ]);
        $this->assertNull($empty->geometry());
        $this->assertFalse($empty->isWindowed());

        $partial = new StatementEnvelope(StatementEnvelope::PROFILE_CUSTOM, [
            'return_height' => '1-3/16',
            'return_width' => '3-1/2',
            'to_height' => '1',
            'to_width' => '4',
        ]);
        $this->assertNull($partial->geometry());
        $this->assertFalse($partial->isWindowed());
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
