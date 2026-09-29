<?php

/**
 * Isolated tests for the facility-screen postal notice.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\X125010837P;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FacilityZipSaveWarningTest extends TestCase
{
    /**
     * @return array<string, array{string, bool, bool, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function noticeProvider(): array
    {
        return [
            'billing five digits' => ['12345', true, false, X125010837P::FACILITY_SAVED_BILLING_POSTAL],
            'service five digits' => ['12345', false, true, X125010837P::FACILITY_SAVED_SERVICE_POSTAL],
            'both roles' => ['12345', true, true, X125010837P::FACILITY_SAVED_BOTH_POSTAL],
            'empty postal' => ['', true, false, X125010837P::FACILITY_SAVED_BILLING_POSTAL],
            'zip plus 4 is nine digits' => ['12345-6789', true, true, ''],
            'nine digits' => ['123456789', false, true, ''],
            'letters after nine digits' => ['123456789abc', true, false, X125010837P::FACILITY_SAVED_BILLING_POSTAL],
            'digits with a space' => ['12345 6789', true, true, X125010837P::FACILITY_SAVED_BOTH_POSTAL],
            'foreign short still notices' => ['K1A 0B1', false, true, X125010837P::FACILITY_SAVED_SERVICE_POSTAL],
            'foreign nine digits is quiet' => ['123456789', true, false, ''],
            'not a service or billing location' => ['12345', false, false, ''],
        ];
    }

    /**
     * The save dialog says the row was stored.
     */
    #[DataProvider('noticeProvider')]
    public function testSaveNoticeFollowsTheFacilityRole(string $postal, bool $billing, bool $service, string $expected): void
    {
        $notice = X125010837P::facilityPostalSaveNotice($postal, $billing, $service);
        $this->assertSame($expected, $notice);
        if ($notice === '') {
            return;
        }
        $this->assertStringContainsString('was saved', $notice);
        $this->assertStringContainsString('can be rejected', $notice);
        $this->assertStringNotContainsString('***', $notice);
        $this->assertStringNotContainsString('CSC', $notice);
        $this->assertStringNotContainsString('MA114', $notice);
        $this->assertStringNotContainsString('277', $notice);
    }

    /**
     * @return array<string, array{mixed, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function writeLandedProvider(): array
    {
        return [
            'positive id' => [4, true],
            'digit string id' => ['12', true],
            'zero' => [0, false],
            'zero string' => ['0', false],
            'empty string' => ['', false],
            'false' => [false, false],
            'null' => [null, false],
            'statement result' => [new \stdClass(), true],
        ];
    }

    /**
     * The saved sentence waits until the facility write finishes.
     */
    #[DataProvider('writeLandedProvider')]
    public function testSavedSentenceWaitsForTheWrite(mixed $result, bool $landed): void
    {
        $this->assertSame($landed, X125010837P::facilityWriteLanded($result));
        $this->assertStringNotContainsString('was saved', X125010837P::FACILITY_NOT_SAVED);
        $this->assertStringContainsString('was saved', X125010837P::FACILITY_SAVED_USERS_NOT_UPDATED);
    }

    /**
     * A missing row, or a row whose stored values differ, is not saved.
     */
    public function testEditStaysUnsavedUnlessTheStoredRowMatches(): void
    {
        $posted = [
            'id' => '3',
            'name' => 'Main Office',
            'postal_code' => '12345',
            'billing_location' => '',
            'service_location' => '1',
            'inactive' => '',
        ];
        $stored = [
            'id' => 3,
            'name' => 'Main Office',
            'postal_code' => '12345',
            'billing_location' => 0,
            'service_location' => '1',
            'inactive' => 0,
        ];

        $this->assertFalse(X125010837P::facilityEditStored($posted, null));
        $this->assertFalse(X125010837P::facilityEditStored($posted, false));
        $this->assertFalse(X125010837P::facilityEditStored($posted, []));
        $this->assertFalse(X125010837P::facilityEditStored($posted, new \stdClass()));
        $this->assertTrue(X125010837P::facilityEditStored($posted, $stored));

        $stored['postal_code'] = '99999';
        $this->assertFalse(X125010837P::facilityEditStored($posted, $stored));

        $stored['postal_code'] = '12345';
        $stored['id'] = '4';
        $this->assertFalse(X125010837P::facilityEditStored($posted, $stored));
    }
}
