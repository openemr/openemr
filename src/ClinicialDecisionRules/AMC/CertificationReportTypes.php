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
}
