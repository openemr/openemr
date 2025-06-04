<?php

/**
 *
 * AMC 314g_1_2_14 STAGE1 Numerator
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

class AMC_314g_1_2_14_Numerator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_314g_1_2_14 Numerator";
    }

    public function test(AmcPatient $patient, $beginDate, $endDate)
    {
        // The number of unique patients (or their authorized representatives) in
                // the denominator who have viewed online, downloaded, or transmitted to a
                // third party the patient's health information.
                //
                // Still TODO
                // AMC MU2 TODO :
                // This needs to be converted to the Z&H offsite portal solution.

                $check = sqlQuery('select count(id) as count from ccda where pid = ? and (view = 1 or emr_transfer = 1) and user_id is null and updated_date >= ? and updated_date <= ?', array($patient->id,$beginDate,$endDate));
        if ($check['count'] > 0) {
            return true;
        } else {
            return false;
        }
    }
}
