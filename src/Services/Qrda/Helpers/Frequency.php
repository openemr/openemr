<?php

/**
 * Frequency is a mustache helper trait with various helper methods for dealing with medication frequencies.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Qrda\Helpers;

use Mustache_Context;

trait Frequency
{
    // note the ruby makes this a readonly constant... but we can't do that in a trait.
    protected $FREQUENCY_CODE_MAP = [
        '396107007' => [ "low" => 12, "high" => 24, "unit" => 'h', "institution_specified" => true, "code_system" => '2.16.840.1.113883.6.96', "code_system_name" => 'SNOMEDCT', "display_name" => 'One to two times a day (qualifier value)' ],
        '396108002' => [ "low" => 8, "high" => 24, "unit" => 'h', "institution_specified" => true, "code_system" => '2.16.840.1.113883.6.96', "code_system_name" => 'SNOMEDCT', "display_name" => 'One to three times a day (qualifier value)' ],
        '396109005' => [ "low" => 6, "high" => 24, "unit" => 'h', "institution_specified" => true, "code_system" => '2.16.840.1.113883.6.96', "code_system_name" => 'SNOMEDCT', "display_name" => 'One to four times a day (qualifier value)' ],
        '396111001' => [ "low" => 6, "high" => 12, "unit" => 'h', "institution_specified" => true, "code_system" => '2.16.840.1.113883.6.96', "code_system_name" => 'SNOMEDCT', "display_name" => 'Two to four times a day (qualifier value)' ],

        '229797004' => [ "low" => 24, "high" => null, "unit" => 'h', "institution_specified" => true, "code_system" => '2.16.840.1.113883.6.96', "code_system_name" => 'SNOMEDCT', "code_system_version" => '2018-03', "display_name" => 'Once daily (qualifier value)' ],
        '229799001' => [ "low" => 12, "high" => null, "unit" => 'h', "institution_specified" => true, "code_system" => '2.16.840.1.113883.6.96', "code_system_name" => 'SNOMEDCT', "code_system_version" => '2018-03', "display_name" => 'Twice a day (qualifier value)' ],
        '229798009' => [ "low" => 8, "high" => null, "unit" => 'h', "institution_specified" => true, "code_system" => '2.16.840.1.113883.6.96', "code_system_name" => 'SNOMEDCT', "code_system_version" => '2018-03', "display_name" => 'Three times daily (qualifier value)' ],
        '307439001' => [ "low" => 6, "high" => null, "unit" => 'h', "institution_specified" => true, "code_system" => '2.16.840.1.113883.6.96', "code_system_name" => 'SNOMEDCT', "code_system_version" => '2018-03', "display_name" => 'Four times daily (qualifier value)' ],

        '225752000' => [ "low" => 2, "high" => 4, "unit" => 'h', "institution_specified" => false, "code_system" => '2.16.840.1.113883.6.96', "code_system_name" => 'SNOMEDCT', "display_name" => 'Every two to four hours (qualifier value)' ],
        '225754004' => [ "low" => 3, "high" => 4, "unit" => 'h', "institution_specified" => false, "code_system" => '2.16.840.1.113883.6.96', "code_system_name" => 'SNOMEDCT', "display_name" => 'Every three to four hours (qualifier value)' ],
        '396127008' => [ "low" => 3, "high" => 6, "unit" => 'h', "institution_specified" => false, "code_system" => '2.16.840.1.113883.6.96', "code_system_name" => 'SNOMEDCT', "display_name" => 'Every three to six hours (qualifier value)' ],
        '396139000' => [ "low" => 6, "high" => 8, "unit" => 'h', "institution_specified" => false, "code_system" => '2.16.840.1.113883.6.96', "code_system_name" => 'SNOMEDCT', "display_name" => 'Every six to eight hours (qualifier value)' ],
        '396140003' => [ "low" => 8, "high" => 12, "unit" => 'h', "institution_specified" => false, "code_system" => '2.16.840.1.113883.6.96', "code_system_name" => 'SNOMEDCT', "display_name" => 'Every eight to twelve hours (qualifier value)' ],

        '225756002' => [ "low" => 4, "high" => null, "unit" => 'h', "institution_specified" => false, "code_system" => '2.16.840.1.113883.6.96', "code_system_name" => 'SNOMEDCT', "display_name" => 'Every four hours (qualifier value)' ],
        '307468000' => [ "low" => 6, "high" => null, "unit" => 'h', "institution_specified" => false, "code_system" => '2.16.840.1.113883.6.96', "code_system_name" => 'SNOMEDCT', "code_system_version" => '2018-03', "display_name" => 'Every six hours (qualifier value)' ],
        '307469008' => [ "low" => 8, "high" => null, "unit" => 'h', "institution_specified" => false, "code_system" => '2.16.840.1.113883.6.96', "code_system_name" => 'SNOMEDCT', "code_system_version" => '2018-03', "display_name" => 'Every eight hours (qualifier value)' ],
        '307470009' => [ "low" => 12, "high" => null, "unit" => 'h', "institution_specified" => false, "code_system" => '2.16.840.1.113883.6.96', "code_system_name" => 'SNOMEDCT', "code_system_version" => '2018-03', "display_name" => 'Every twelve hours (qualifier value)' ],
        '396125000' => [ "low" => 24, "high" => null, "unit" => 'h', "institution_specified" => false, "code_system" => '2.16.840.1.113883.6.96', "code_system_name" => 'SNOMEDCT', "code_system_version" => '2018-03', "display_name" => 'Every twenty four hours (qualifier value)' ],
        '396126004' => [ "low" => 36, "high" => null, "unit" => 'h', "institution_specified" => false, "code_system" => '2.16.840.1.113883.6.96', "code_system_name" => 'SNOMEDCT', "code_system_version" => '2018-03', "display_name" => 'Every thirty six hours (qualifier value)' ],
        '396131002' => [ "low" => 48, "high" => null, "unit" => 'h', "institution_specified" => false, "code_system" => '2.16.840.1.113883.6.96', "code_system_name" => 'SNOMEDCT', "code_system_version" => '2018-03', "display_name" => 'Every forty eight hours (qualifier value)' ],
        '396143001' => [ "low" => 72, "high" => null, "unit" => 'h', "institution_specified" => false, "code_system" => '2.16.840.1.113883.6.96', "code_system_name" => 'SNOMEDCT', "code_system_version" => '2018-03', "display_name" => 'Every seventy two hours (qualifier value)' ]
    ];
    public function medication_frequency(Mustache_Context $context)
    {
        // If the code matches one of the known Direct Reference Codes, export that time in hours. Otherwise default to "every twenty four hours" code
        $code = $context->find('code');
        $frequency_code_entry = $this->FREQUENCY_CODE_MAP[$code] ?? $this->FREQUENCY_CODE_MAP['396125000'];
        if (!$frequency_code_entry['institution_specified']) {
            if (empty($frequency_code_entry['high'])) {
                return $this->institution_not_specified_point_frequency($frequency_code_entry);
            } else {
                return $this->institution_not_specified_range_frequency($frequency_code_entry);
            }
        } else {
            if (empty($frequency_code_entry['high'])) {
                return $this->institution_specified_point_frequency($frequency_code_entry);
            } else {
                return $this->institution_specified_range_frequency($frequency_code_entry);
            }
        }
    }

    public function institution_not_specified_point_frequency($frequency_code_entry)
    {
        return "<effectiveTime xsi:type='PIVL_TS' operator='A'>"
            . "<period value='" . $frequency_code_entry['low'] . "' unit='" . $frequency_code_entry['unit'] . "'/>"
            . "</effectiveTime>";
    }

    public function institution_not_specified_range_frequency($frequency_code_entry)
    {
        return "<effectiveTime xsi:type='PIVL_TS' operator='A'>"
            . "<period xsi:type='IVL_PQ'>"
            . "<low value='" . $frequency_code_entry['low'] . "' unit='" . $frequency_code_entry['unit'] . "'/>"
            . "<high value='" . $frequency_code_entry['high'] . "' unit='" . $frequency_code_entry['unit'] . "'/>"
            . "</period>"
            . "</effectiveTime>";
    }
    public function institution_specified_point_frequency($frequency_code_entry)
    {
        return "<effectiveTime xsi:type='PIVL_TS' institutionSpecified='true' operator='A'>"
            . "<period value='" . $frequency_code_entry['low'] . "' unit='" . $frequency_code_entry['unit'] . "'/>"
            . "</effectiveTime>";
    }
    public function institution_specified_range_frequency($frequency_code_entry)
    {
        return "<effectiveTime xsi:type='PIVL_TS' institutionSpecified='true' operator='A'>"
            . "<period xsi:type='IVL_PQ'>"
            . "<low value='" . $frequency_code_entry['low'] . "' unit='" . $frequency_code_entry['unit'] . "'/>"
            . "<high value='" . $frequency_code_entry['high'] . "' unit='" . $frequency_code_entry['unit'] . "'/>"
            . "</period>"
            . "</effectiveTime>";
    }
}
