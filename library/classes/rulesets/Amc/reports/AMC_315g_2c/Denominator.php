<?php

/**
 *
 * AMC_315g_2c_Denominator handles the denominator population calculation for §170.315 (g)(2) Required Test 2c which is
 * all of the patients seen by a Eligible Clinician (EC) or Eligible Clinician Group (ECG) during the
 * report period.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @copyright 2022 Discover and Change, Inc.
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

class AMC_315g_2c_Denominator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_315g_2c Denominator";
    }

    public function test(AmcPatient $patient, $beginDate, $endDate)
    {
        //  (basically needs a patient within the report dates,
        //   which are already filtered for, so all the objects are a positive)
        return true;
    }
}