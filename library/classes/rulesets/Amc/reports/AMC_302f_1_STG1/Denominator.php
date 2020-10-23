<?php

/**
 *
 * AMC 302f 1 STAGE1 Denominator
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

class AMC_302f_1_STG1_Denominator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_302f_1_STG1 Denominator";
    }

    public function test(AmcPatient $patient, $beginDate, $endDate)
    {
        //Number of unique patients 2 years of age or older seen by the EP during the EHR reporting period (Effective through 2013 only)
        $options = array( Encounter::OPTION_ENCOUNTER_COUNT => 1 );
        if (
            (Helper::checkAnyEncounter($patient, $beginDate, $endDate, $options)) &&
             ($patient->calculateAgeOnDate($endDate) >= 2)
        ) {
            return true;
        } else {
            return false;
        }
    }
}
