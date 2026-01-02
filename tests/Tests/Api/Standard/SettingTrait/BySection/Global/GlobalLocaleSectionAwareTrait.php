<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Api\Standard\SettingTrait\BySection\Global;

trait GlobalLocaleSectionAwareTrait
{
    protected static function getGlobalLocaleDataProviderChunks(): iterable
    {
        yield [
            'locale',
            '{
                "language_default": "English (Standard)",
                "language_menu_showall": true,
                "language_menu_other": [
                    "Ukrainian",
                    "English (Standard)",
                    "Spanish (Spain)"
                ],
                "allow_debug_language": false,
                "translate_no_safe_apostrophe": true,
                "translate_layout": true,
                "translate_lists": true,
                "translate_gacl_groups": true,
                "translate_form_titles": true,
                "translate_document_categories": true,
                "translate_appt_categories": true,
                "units_of_measurement": 1,
                "us_weight_format": 1,
                "phone_country_code": 1,
                "date_display_format": "0",
                "time_display_format": "0",
                "gbl_time_zone": "",
                "currency_decimals": "2",
                "currency_dec_point": ".",
                "currency_thousands_sep": ",",
                "gbl_currency_symbol": "$",
                "age_display_format": 0,
                "age_display_limit": 3,
                "weekend_days": "6,0"
            }'
        ];
    }
}
