<?php

/**
 * Isolated BmiCategory Test
 *
 * Tests BMI category classification logic.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Forms;

use OpenEMR\Common\Forms\BmiCategory;
use PHPUnit\Framework\TestCase;

class BmiCategoryTest extends TestCase
{
    public function testFromBmiReturnsObesityIIIForHighBmi(): void
    {
        $result = BmiCategory::fromBmi(45.0);
        $this->assertSame(BmiCategory::ObesityIII, $result);
    }

    public function testFromBmiReturnsObesityIIForBmiAbove34(): void
    {
        $result = BmiCategory::fromBmi(38.0);
        $this->assertSame(BmiCategory::ObesityII, $result);
    }

    public function testFromBmiReturnsObesityIForBmiAbove30(): void
    {
        $result = BmiCategory::fromBmi(32.0);
        $this->assertSame(BmiCategory::ObesityI, $result);
    }

    public function testFromBmiReturnsOverweightForBmiAbove27(): void
    {
        $result = BmiCategory::fromBmi(28.5);
        $this->assertSame(BmiCategory::Overweight, $result);
    }

    public function testFromBmiReturnsNormalBorderlineForBmiAbove25(): void
    {
        $result = BmiCategory::fromBmi(26.0);
        $this->assertSame(BmiCategory::NormalBorderline, $result);
    }

    public function testFromBmiReturnsNormalForBmiAbove18Point5(): void
    {
        $result = BmiCategory::fromBmi(22.0);
        $this->assertSame(BmiCategory::Normal, $result);
    }

    public function testFromBmiReturnsUnderweightForBmiAbove10(): void
    {
        $result = BmiCategory::fromBmi(16.0);
        $this->assertSame(BmiCategory::Underweight, $result);
    }

    public function testFromBmiReturnsNullForVeryLowBmi(): void
    {
        $result = BmiCategory::fromBmi(8.0);
        $this->assertNull($result);
    }

    public function testFromBmiReturnsNullForBmiAtExactly10(): void
    {
        $result = BmiCategory::fromBmi(10.0);
        $this->assertNull($result);
    }

    public function testFromBmiBoundaryAt42(): void
    {
        // At exactly 42, should be ObesityII (not ObesityIII, since > 42 is required)
        $result = BmiCategory::fromBmi(42.0);
        $this->assertSame(BmiCategory::ObesityII, $result);

        // Just above 42 should be ObesityIII
        $result = BmiCategory::fromBmi(42.1);
        $this->assertSame(BmiCategory::ObesityIII, $result);
    }

    public function testFromBmiBoundaryAt34(): void
    {
        $result = BmiCategory::fromBmi(34.0);
        $this->assertSame(BmiCategory::ObesityI, $result);

        $result = BmiCategory::fromBmi(34.1);
        $this->assertSame(BmiCategory::ObesityII, $result);
    }

    public function testFromBmiBoundaryAt30(): void
    {
        $result = BmiCategory::fromBmi(30.0);
        $this->assertSame(BmiCategory::Overweight, $result);

        $result = BmiCategory::fromBmi(30.1);
        $this->assertSame(BmiCategory::ObesityI, $result);
    }

    public function testFromBmiBoundaryAt27(): void
    {
        $result = BmiCategory::fromBmi(27.0);
        $this->assertSame(BmiCategory::NormalBorderline, $result);

        $result = BmiCategory::fromBmi(27.1);
        $this->assertSame(BmiCategory::Overweight, $result);
    }

    public function testFromBmiBoundaryAt25(): void
    {
        $result = BmiCategory::fromBmi(25.0);
        $this->assertSame(BmiCategory::Normal, $result);

        $result = BmiCategory::fromBmi(25.1);
        $this->assertSame(BmiCategory::NormalBorderline, $result);
    }

    public function testFromBmiBoundaryAt18Point5(): void
    {
        $result = BmiCategory::fromBmi(18.5);
        $this->assertSame(BmiCategory::Underweight, $result);

        $result = BmiCategory::fromBmi(18.6);
        $this->assertSame(BmiCategory::Normal, $result);
    }

    public function testTryFromValueWithValidValues(): void
    {
        $this->assertSame(BmiCategory::ObesityIII, BmiCategory::tryFromValue('Obesity III'));
        $this->assertSame(BmiCategory::ObesityII, BmiCategory::tryFromValue('Obesity II'));
        $this->assertSame(BmiCategory::ObesityI, BmiCategory::tryFromValue('Obesity I'));
        $this->assertSame(BmiCategory::Overweight, BmiCategory::tryFromValue('Overweight'));
        $this->assertSame(BmiCategory::NormalBorderline, BmiCategory::tryFromValue('Normal BL'));
        $this->assertSame(BmiCategory::Normal, BmiCategory::tryFromValue('Normal'));
        $this->assertSame(BmiCategory::Underweight, BmiCategory::tryFromValue('Underweight'));
    }

    public function testTryFromValueWithInvalidValue(): void
    {
        $this->assertNull(BmiCategory::tryFromValue('Invalid'));
        $this->assertNull(BmiCategory::tryFromValue(''));
    }
}
