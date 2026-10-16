<?php

/**
 * Facility-screen postal notice.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use OpenEMR\Services\FacilityService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FacilityPostalSaveNoticeTest extends TestCase
{
    /**
     * @return array<string, array{string, bool, bool, string, string, int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function noticeProvider(): array
    {
        $billing = FacilityService::FACILITY_SAVED_BILLING_POSTAL;
        $service = FacilityService::FACILITY_SAVED_SERVICE_POSTAL;
        $both = FacilityService::FACILITY_SAVED_BOTH_POSTAL;

        return [
            'billing five digits' => ['12345', true, false, $billing, '', 1],
            'service five digits' => ['12345', false, true, $service, '', 1],
            'both roles' => ['12345', true, true, $both, '', 1],
            'empty postal' => ['', true, false, $billing, '', 1],
            'zip plus 4 is nine digits' => ['12345-6789', true, true, '', '', 1],
            'nine digits' => ['123456789', false, true, '', '', 1],
            'letters after nine digits' => ['123456789abc', true, false, '', '', 1],
            'digits with a space' => ['12345 6789', true, true, '', '', 1],
            'unlabeled canadian shape uses the us rule' => ['K1A 0B1', false, true, $service, '', 1],
            'canada stays quiet' => ['K1A 0B1', false, true, '', 'Canada', 1],
            'ca stays quiet' => ['K1A0B1', true, false, '', 'CA', 1],
            'ireland stays quiet' => ['D02 AF30', true, true, '', 'Ireland', 353],
            'ie stays quiet' => ['D02AF30', true, false, '', 'IE', 353],
            'blank country outside north america stays quiet' => ['12345', true, false, '', '', 353],
            'united states still notices' => ['12345', true, false, $billing, 'United States', 1],
            'usa abbreviation still notices' => ['12345', false, true, $service, 'U.S.A.', 44],
            'us code still notices' => ['12345', true, true, $both, 'US', 1],
            'foreign nine digits is quiet' => ['123456789', true, false, '', '', 1],
            'not a service or billing location' => ['12345', false, false, '', 'Canada', 1],
        ];
    }

    /**
     * The save dialog says the row was stored.
     */
    #[DataProvider('noticeProvider')]
    public function testSaveNoticeFollowsTheFacilityRole(
        string $postal,
        bool $billing,
        bool $service,
        string $expected,
        string $country,
        int $phoneCountryCode,
    ): void {
        $notice = FacilityService::facilityPostalSaveNotice($postal, $billing, $service, $country, $phoneCountryCode);
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
     * The dialog body names saved or not saved, and stays JSON when the sentence cannot be encoded.
     */
    public function testSaveDialogBodyNamesTheStatus(): void
    {
        $saved = json_decode(FacilityService::facilitySaveDialogBody(true, 'Notice'), true);
        $open = json_decode(FacilityService::facilitySaveDialogBody(false, ''), true);
        $this->assertIsArray($saved);
        $this->assertIsArray($open);

        $this->assertSame('saved', $saved['status']);
        $this->assertSame('Notice', $saved['message']);
        $this->assertSame('not_saved', $open['status']);
        $this->assertSame('', $open['message']);
    }

    /**
     * A sentence that is not valid text drops the notice and keeps the status.
     */
    public function testSaveDialogBodyKeepsTheStatusWhenTheSentenceCannotBeEncoded(): void
    {
        $saved = json_decode(FacilityService::facilitySaveDialogBody(true, "\xB1\x31"), true);
        $open = json_decode(FacilityService::facilitySaveDialogBody(false, "\xB1\x31"), true);
        $this->assertIsArray($saved);
        $this->assertIsArray($open);

        $this->assertSame('saved', $saved['status']);
        $this->assertSame('', $saved['message']);
        $this->assertSame('not_saved', $open['status']);
        $this->assertSame('', $open['message']);
    }
}
