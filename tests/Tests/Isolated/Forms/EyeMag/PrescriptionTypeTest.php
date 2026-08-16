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
}
