<?php
/**
 *
 * AMC 302f 3 STAGE1 Numerator
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

class AMC_302f_3_STG1_Numerator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_302f_3_STG1 Numerator";
    }
    
    public function test(AmcPatient $patient, $beginDate, $endDate)
    {
        //The number of patients in the denominator who have entries of BP as structured data (Effective 2013 onward for providers for whom height and weight is out of scope of practice)
        if ((exist_database_item($patient->id, 'form_vitals', 'gt', 'ge', 1, 'bps', '0', '', '', $endDate)) &&
              (exist_database_item($patient->id, 'form_vitals', 'gt', 'ge', 1, 'bpd', '0', '', '', $endDate))
           ) {
            return true;
        } else {
            return false;
        }
    }
}
