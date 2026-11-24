<?php

/**
 * The facility form's posted fields as facility column values.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services;

if (!defined('OPENEMR_STATIC_ANALYSIS')) {
    define('OPENEMR_STATIC_ANALYSIS', true);
}

use OpenEMR\Services\FacilityService;
use PHPUnit\Framework\TestCase;

class FacilityFormValuesTest extends TestCase
{
    public function testEveryColumnIsReturnedAndAMissingFieldIsEmpty(): void
    {
        $values = FacilityService::facilityFormValues([]);

        $this->assertSame(array_keys(FacilityService::FACILITY_FORM_FIELDS), array_keys($values));
        $this->assertSame([''], array_values(array_unique($values)));
    }

    public function testAFieldIsTrimmedIntoItsColumn(): void
    {
        $values = FacilityService::facilityFormValues([
            'phone' => ' 802-555-0100 ',
            'mammography_cert_number' => " 123456\n",
            'domain_identifier' => '47D0000000',
        ]);

        $this->assertSame('802-555-0100', $values['phone']);
        $this->assertSame('123456', $values['mammography_cert_number']);
        $this->assertSame('47D0000000', $values['domain_identifier']);
    }

    public function testThreeFieldsAreNamedDifferentlyFromTheirColumn(): void
    {
        $values = FacilityService::facilityFormValues([
            'facility' => 'Main Clinic',
            'ncolor' => '#99FFFF',
            'facility_id' => 'MC1',
            // The column names themselves are not form fields.
            'name' => 'ignored',
            'color' => 'ignored',
            'facility_code' => 'ignored',
        ]);

        $this->assertSame('Main Clinic', $values['name']);
        $this->assertSame('#99FFFF', $values['color']);
        $this->assertSame('MC1', $values['facility_code']);
    }

    public function testAFieldThatIsNotTextIsSavedAsEmptyAndOthersAreIgnored(): void
    {
        $values = FacilityService::facilityFormValues([
            'phone' => ['802-555-0100'],
            'mode' => 'facility',
            'fid' => '3',
        ]);

        $this->assertSame('', $values['phone']);
        $this->assertArrayNotHasKey('mode', $values);
        $this->assertArrayNotHasKey('fid', $values);
    }
}
