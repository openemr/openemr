<?php

/**
 *
 * AMC 304d STAGE2 Numerator
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

class AMC_304d_STG2_Numerator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_304d_STG2 Numerator";
    }

    public function test(AmcPatient $patient, $beginDate, $endDate)
    {
        // Were sent an appropriate reminder during the EHR reporting period
        $result_query = sqlQuery("SELECT * FROM `patient_reminders` WHERE `pid`=? AND `date_sent`>=? AND `date_sent`<=?", array($patient->id,$beginDate,$endDate));
        if (!(empty($result_query))) {
            return true;
        } else {
            return false;
        }
    }
}
