<?php

/**
 *
 * AMC 304i STAGE1 Numerator
 *
 * Copyright (C) 2015 Ensoftek, Inc
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Ensoftek
 * @link    http://www.open-emr.org
 */

class AMC_304i_STG1_Numerator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_304i_STG1 Numerator";
    }

    public function test(AmcPatient $patient, $beginDate, $endDate)
    {
        //The number of transitions of care and referrals in the denominator where a summary of care record was provided.
        //  (so basically an amc element needs to exist)
        $amcElement = amcCollect('send_sum_amc', $patient->id, 'transactions', $patient->object['id']);
        if (!(empty($amcElement))) {
            $no_problems = sqlQuery("select count(*) as cnt from lists_touch where pid = ? and type = 'medical_problem'", array($patient->id));
                $problems    = sqlQuery("select count(*) as cnt from lists where pid = ? and type = 'medical_problem'", array($patient->id));

                $no_allergy     = sqlQuery("select count(*) as cnt from lists_touch where pid = ? and type = 'allergy'", array($patient->id));
                $allergies      = sqlQuery("select count(*) as cnt from lists where pid = ? and type = 'allergy'", array($patient->id));

                $no_medication = sqlQuery("select count(*) as cnt from lists_touch where pid = ? and type = 'medication'", array($patient->id));
                $medications   = sqlQuery("select count(*) as cnt from lists where pid = ? and type = 'medication'", array($patient->id));
                $prescriptions = sqlQuery("select count(*) as cnt from prescriptions where patient_id = ? ", array($patient->id));

            if (($no_problems['cnt'] > 0 || $problems['cnt'] > 0) && ($no_allergy['cnt'] > 0 || $allergies['cnt'] > 0) && ($no_medication['cnt'] > 0 || $medications['cnt'] > 0 || $prescriptions['cnt'] > 0)) {
                    return true;
            }

            return false;
        } else {
            return false;
        }
    }
}
