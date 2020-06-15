<?php

/**
 *
 * AMC 314g_1_2_14 STAGE2 Numerator
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

class AMC_314g_1_2_14_STG2_Numerator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_314g_1_2_14_STG2 Numerator";
    }

    public function test(AmcPatient $patient, $beginDate, $endDate)
    {
        // Need to meet following criteria:
        //  -Offsite patient portal is turned on.
        //  -Patient permits having access to the patient portal.
        //  -Patient has an account on the offsite patient portal.

        // This now always returns false since there is no offsite patient portal
        //  TODO - For MU3, will need to use onsite patient portal mechanism
        return false;

        $portal_permission = sqlQuery("SELECT `allow_patient_portal` FROM `patient_data` WHERE pid = ?", array($patient->id));
        if ($portal_permission['allow_patient_portal'] != "YES") {
            return false;
        }

        // Note below query will break if run since patient_access_offsite no longer exists
        //  (will never get here since returning false above)
        $portalQry = "SELECT count(*) as cnt FROM `patient_access_offsite` WHERE pid=?";
        $check = sqlQuery($portalQry, array($patient->id));
        if ($check['cnt'] > 0) {
            return true;
        } else {
            return false;
        }
    }
}
