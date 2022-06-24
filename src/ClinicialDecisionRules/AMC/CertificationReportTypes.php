<?php

/**
 * CertificationReportTypes holds the constants we use for the Automated Measure Calculation reports.  As there have been
 * various reporting years and methods for AMC this class holds the types of reporting methods and calculations we use
 * for each certification of OpenEMR.  The most current report type we run will be contained in the DEFAULT constant.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\ClinicialDecisionRules\AMC;

class CertificationReportTypes
{
    const ORIGINAL_REPORT = 'amc';
    const MU1_2011 = 'amc_2011';
    const MU2_2014 = 'amc_2014';
    const MU2_2014_STAGE1 = 'amc_2014_stage1';
    const MU2_2014_STAGE2 = 'amc_2014_stage2';
    const MU3_2015 = 'amc_2015';
    const DEFAULT = self::MU3_2015;

    const AMC_METHODS = [self::ORIGINAL_REPORT, self::MU1_2011, self::MU2_2014, self::MU2_2014_STAGE1, self::MU2_2014_STAGE2, self::MU3_2015];

    /**
     * Checks to see if the passed in string is a valid AMC report type or not.
     * @param string $method The method we are checking to see if its an AMC or not
     * @return bool True if the passed in string is an AMC report type, false otherwise
     */
    public static function isAMCReportType(string $method)
    {
        return array_search(strtolower($method), self::AMC_METHODS) !== false;
    }

    /**
     * Returns an array of all of the report types for the Automated Measure Calculations (AMC)
     *
     * ruleset_title is the report title that displays in the report results
     * title is what is displayed in the dropdown list in the AMC report generation screen
     * code_col is the column used in the database table for the AMC rule description
     * the array key is the amc_<system_type>_flag in the database
     *
     * @return array
     */
    public static function getReportTypeRecords()
    {

        $amc_report_types = [
            CertificationReportTypes::DEFAULT => [
                // 2015 AMC measures is our default ones
                'abbr' => xl('AMC-2015')
                , 'title' => xl('Automated Measure Calculations (AMC)')
                , 'ruleset_title' => xl('Automated Measure Calculations (AMC) - 2015')
                , 'code_col' => 'amc_code_2015'
            ],
            // we have to support the original report settings
            CertificationReportTypes::ORIGINAL_REPORT => [
                // 2015 AMC measures is our default ones
                'abbr' => xl('AMC')
                , 'title' => xl('Automated Measure Calculations (AMC)')
                , 'ruleset_title' => xl('Automated Measure Calculations (AMC)')
                , 'code_col' => 'amc_code'
            ]
            ,CertificationReportTypes::MU1_2011 => [
                'abbr' => xl('AMC-2011')
                , 'title' => xl('Automated Measure Calculations (AMC) - 2011')
                , 'ruleset_title' => xl('Automated Measure Calculations (AMC) - 2011')
                , 'code_col' => 'amc_code'
            ]
            ,CertificationReportTypes::MU2_2014_STAGE1 => [
                'abbr' => xl('AMC-2014')
                , 'title' => xl('Automated Measure Calculations (AMC) - 2014 Stage I')
                , 'ruleset_title' => xl('Automated Measure Calculations (AMC) - 2014 Stage I')
                , 'code_col' => 'amc_code_2014'
            ]
            ,CertificationReportTypes::MU2_2014_STAGE2 => [
                'abbr' => xl('AMC-2014')
                , 'title' => xl('Automated Measure Calculations (AMC) - 2014 Stage II')
                , 'ruleset_title' => xl('Automated Measure Calculations (AMC) - 2014 Stage II')
                , 'code_col' => 'amc_code_2014'
            ]
        ];
        return $amc_report_types;
    }
}
