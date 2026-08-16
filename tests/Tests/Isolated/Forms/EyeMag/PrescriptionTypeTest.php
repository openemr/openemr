<?php

/**
 * Isolated tests for eye exam prescription refraction and lens types.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Forms\EyeMag;

use OpenEMR\Forms\EyeMag\RefType;
use OpenEMR\Forms\EyeMag\RxType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PrescriptionTypeTest extends TestCase
{
    public function testOnlyContactLensPrescriptionsAreContactLenses(): void
    {
        $this->assertTrue(RefType::CTL->isContactLens());

        foreach (RefType::cases() as $refType) {
            if ($refType !== RefType::CTL) {
                $this->assertFalse($refType->isContactLens(), "{$refType->value} is not a contact lens");
            }
        }
    }

    public function testEveryRefractionMethodHasADistinctNonEmptyLabel(): void
    {
        $labels = array_map(static fn(RefType $refType): string => $refType->displayName(), RefType::cases());

        $this->assertSame([], array_filter($labels, static fn(string $label): bool => $label === ''));
        $this->assertCount(count($labels), array_unique($labels));
    }

    /**
     * @return list<array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function unknownRefractionMethodProvider(): array
    {
        return [[''], ['ctl'], ['BOGUS'], ['Wearing']];
    }

    #[DataProvider('unknownRefractionMethodProvider')]
    public function testUnknownRefractionMethodsDoNotResolve(string $value): void
    {
        $this->assertNull(RefType::tryFrom($value));
    }

    /**
     * The codes come from `form_eye_mag_wearing.RX_TYPE` and the names are what
     * the print form renders and stores in `form_eye_mag_dispense.RXTYPE`.
     *
     * @return list<array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function lensTypeCodeProvider(): array
    {
        return [
            ['0', 'Single'],
            ['1', 'Bifocal'],
            ['2', 'Trifocal'],
            ['3', 'Progressive'],
        ];
    }

    #[DataProvider('lensTypeCodeProvider')]
    public function testLensTypeCodesMapToTheStoredLabels(string $code, string $label): void
    {
        $this->assertSame($label, RxType::from($code)->name);
    }

    /**
     * @return list<array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function unrecognizedLensCodeProvider(): array
    {
        return [[''], ['4'], ['Single'], ['x']];
    }

    /**
     * Anything the print form does not recognize has to miss, so that it falls
     * back to {@see RxType::DEFAULT} rather than rendering a stray label.
     */
    #[DataProvider('unrecognizedLensCodeProvider')]
    public function testUnrecognizedLensCodesDoNotResolve(string $code): void
    {
        $this->assertNull(RxType::tryFrom($code));
    }

    /**
     * The dispense table stores the label, so a round trip through it has to
     * land back on the same case the numeric code came from.
     */
    #[DataProvider('lensTypeCodeProvider')]
    public function testStoredLabelsResolveBackToTheirLensType(string $code, string $label): void
    {
        $this->assertSame(RxType::from($code), RxType::fromLabel($label));
    }

    /**
     * @return list<array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function unrecognizedLensLabelProvider(): array
    {
        return [[''], ['0'], ['single'], ['Varifocal']];
    }

    #[DataProvider('unrecognizedLensLabelProvider')]
    public function testUnrecognizedLensLabelsDoNotResolve(string $label): void
    {
        $this->assertNull(RxType::fromLabel($label));
    }

    public function testOnlyTheSelectedLensTypeIsChecked(): void
    {
        foreach (RxType::cases() as $selected) {
            $checked = array_filter(
                RxType::cases(),
                static fn(RxType $case): bool => $case->checkedAttribute($selected) !== '',
            );

            $this->assertSame([$selected], array_values($checked));
        }
    }

    public function testNothingIsCheckedWhenNoLensTypeIsSelected(): void
    {
        foreach (RxType::cases() as $case) {
            $this->assertSame('', $case->checkedAttribute(null));
        }
    }

    /**
     * The prefix is what lets the print form read one refraction's measurements
     * without a branch per method, so it has to be distinct and non-empty for
     * every method that has one.
     */
    public function testEveryRefractionHasADistinctColumnPrefix(): void
    {
        $prefixes = [];
        foreach (RefType::cases() as $refType) {
            $prefix = $refType->columnPrefix();
            if ($prefix === null) {
                continue;
            }

            $this->assertNotSame('', $prefix);
            $prefixes[] = $prefix;
        }

        $this->assertCount(count($prefixes), array_unique($prefixes));
    }

    /**
     * A wearing prescription is copied out of `form_eye_mag_wearing`, whose
     * columns are unprefixed, so it is the one method with no prefix.
     */
    public function testOnlyWearingPrescriptionsLackAColumnPrefix(): void
    {
        $this->assertNull(RefType::W->columnPrefix());

        foreach (RefType::cases() as $refType) {
            if ($refType !== RefType::W) {
                $this->assertNotNull($refType->columnPrefix(), "{$refType->value} reads prefixed columns");
            }
        }
    }

    /**
     * The three spectacle refractions share the single CRCOMMENTS column of the
     * refraction record; the other two carry their own unprefixed COMMENTS.
     */
    public function testSpectacleRefractionsShareTheRefractionCommentsColumn(): void
    {
        foreach ([RefType::AR, RefType::MR, RefType::CR] as $refType) {
            $this->assertSame('CRCOMMENTS', $refType->commentsColumn());
        }

        $this->assertSame('COMMENTS', RefType::W->commentsColumn());
        $this->assertSame('COMMENTS', RefType::CTL->commentsColumn());
    }

    /**
     * A cycloplegic refraction paralyzes accommodation and a contact lens keeps
     * its add in the lens columns, so neither carries a spectacle add power.
     */
    public function testOnlyRefractionsWithAnAddPowerReportOne(): void
    {
        foreach ([RefType::W, RefType::AR, RefType::MR] as $refType) {
            $this->assertTrue($refType->hasAddPower(), "{$refType->value} records an add power");
        }

        foreach ([RefType::CR, RefType::CTL] as $refType) {
            $this->assertFalse($refType->hasAddPower(), "{$refType->value} records no add power");
        }
    }
}
