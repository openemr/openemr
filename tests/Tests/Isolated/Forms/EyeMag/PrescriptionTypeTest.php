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
        $this->assertSame(count($labels), count(array_unique($labels)));
    }

    public function testUnknownRefractionMethodsDoNotResolve(): void
    {
        $this->assertNull(RefType::tryFrom(''));
        $this->assertNull(RefType::tryFrom('ctl'));
        $this->assertNull(RefType::tryFrom('BOGUS'));
    }

    /**
     * The codes come from `form_eye_mag_wearing.RX_TYPE` and the names are what
     * the print form renders and stores in `form_eye_mag_dispense.RXTYPE`.
     */
    public function testLensTypeCodesMapToTheStoredLabels(): void
    {
        $this->assertSame('Single', RxType::from('0')->name);
        $this->assertSame('Bifocal', RxType::from('1')->name);
        $this->assertSame('Trifocal', RxType::from('2')->name);
        $this->assertSame('Progressive', RxType::from('3')->name);
    }

    public function testUnrecognizedLensCodesFallBackToSingleVision(): void
    {
        foreach (['', '4', 'Single', 'x'] as $unrecognized) {
            $this->assertNull(RxType::tryFrom($unrecognized));
        }

        $this->assertSame(RxType::Single, RxType::DEFAULT);
    }
}
