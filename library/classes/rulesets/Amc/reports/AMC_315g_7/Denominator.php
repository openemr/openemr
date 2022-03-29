<?php

/**
 *
 * AMC_315g_7_Denominator handles the denominator population calculation for §170.315 (g)(2) Required Test 7 which is
 * all of the referrals or transitions of care records created by an Eligible Clinician (EC) or Eligible Clinician
 * Group (ECG) during the report period.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @copyright 2022 Discover and Change, Inc.
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

class AMC_315g_7_Denominator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_315g_7 Denominator";
    }

    public function test(AmcPatient $patient, $beginDate, $endDate)
    {
        // this filtering is done in AbstractAmcReport::collectObjects().  See the transitions-out option

        //  (basically needs a referral within the report dates,
        //   which are already filtered for, so all the objects are a positive)
        return true;
    }
}
