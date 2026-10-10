<?php

/**
 * Isolated MiscBillingOptions::providerId() Test
 *
 * Box 17 is filled from two nullable sources: the form's own provider_id, an
 * int column the select writes 0 into when nothing is chosen, and the
 * patient's ref_providerID, which is NULL for most patients. Both arrive from
 * the database as strings.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\MiscBillingOptions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MiscBillingOptionsProviderIdTest extends TestCase
{
    /**
     * @return array<string, array{mixed, ?int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function valuesProvider(): array
    {
        return [
            'a provider id as the database returns it' => ['5', 5],
            'a provider id as an int' => [5, 5],
            'a padded provider id' => ['  5  ', 5],
            'no referring provider recorded' => [null, null],
            'the select posted nothing, stored as 0' => ['0', null],
            'zero as an int' => [0, null],
            'an empty string' => ['', null],
            'whitespace only' => ['   ', null],
            'a negative id' => [-1, null],
            'not a number' => ['none', null],
            'a decimal' => ['5.5', null],
            'a bool' => [true, null],
            'an array' => [[5], null],
        ];
    }

    #[DataProvider('valuesProvider')]
    public function testProviderId(mixed $value, ?int $expected): void
    {
        self::assertSame($expected, MiscBillingOptions::providerId($value));
    }

    public function testZeroIsNotAProviderSoBoxSeventeenFallsBackToThePatients(): void
    {
        // The form's own value is checked first; 0 must not win over the
        // patient's referring provider, which is what !empty() used to ensure.
        $formValue = MiscBillingOptions::providerId('0');
        $patientValue = MiscBillingOptions::providerId('7');
        self::assertSame(7, $formValue ?? $patientValue);
    }

    public function testTheFormsOwnProviderWinsWhenItIsSet(): void
    {
        $formValue = MiscBillingOptions::providerId('3');
        $patientValue = MiscBillingOptions::providerId('7');
        self::assertSame(3, $formValue ?? $patientValue);
    }

    public function testNeitherSourceGivesNullWhichTheSelectAccepts(): void
    {
        self::assertNull(MiscBillingOptions::providerId(null) ?? MiscBillingOptions::providerId(null));
    }
}
