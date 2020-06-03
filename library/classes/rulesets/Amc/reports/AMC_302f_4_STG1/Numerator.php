<?php

/**
 *
 * AMC 302f 4 STAGE1 Numerator
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

class AMC_302f_4_STG1_Numerator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_302f_4_STG1 Numerator";
    }

    public function test(AmcPatient $patient, $beginDate, $endDate)
    {
        //If height/length, weight, and blood pressure (all) within scope of practice (Optional 2013; Required effective 2014):
        //Patients 3 years of age or older in the denominator for whom Height/length, weight, and blood pressure are recorded
        //Patients younger than 3 years of age in the denominator for whom height/length and weight are recorded
        if (
            ( ($patient->calculateAgeOnDate($endDate) >= 3) &&
              (exist_database_item($patient->id, 'form_vitals', 'bps', 'gt', '0', 'ge', 1, '', '', $endDate)) &&
              (exist_database_item($patient->id, 'form_vitals', 'bpd', 'gt', '0', 'ge', 1, '', '', $endDate)) &&
              (exist_database_item($patient->id, 'form_vitals', 'height', 'gt', '0', 'ge', 1, '', '', $endDate)) &&
              (exist_database_item($patient->id, 'form_vitals', 'weight', 'gt', '0', 'ge', 1, '', '', $endDate))
            ) ||
            ( ($patient->calculateAgeOnDate($endDate) < 3) &&
             (exist_database_item($patient->id, 'form_vitals', 'height', 'gt', '0', 'ge', 1, '', '', $endDate)) &&
             (exist_database_item($patient->id, 'form_vitals', 'weight', 'gt', '0', 'ge', 1, '', '', $endDate))
            )
        ) {
            return true;
        } else {
            return false;
        }
    }
}
