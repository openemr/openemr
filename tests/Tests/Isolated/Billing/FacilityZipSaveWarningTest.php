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
}
