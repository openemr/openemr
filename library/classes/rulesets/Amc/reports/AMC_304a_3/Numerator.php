<?php

/**
 *
 * AMC 304a_3 Numerator
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

class AMC_304a_3_Numerator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_304a_3 Numerator";
    }

    public function test(AmcPatient $patient, $beginDate, $endDate)
    {
        // MEASURE STAGE2: Medication Order(s) Created as CPOE
        //
        // Still TODO
        // AMC MU2 TODO :
        // Note the counter for this is using prescriptions which does not incorporate the cpoe_stat field.
        //
        if (isset($patient->object['cpoe_stat']) && $patient->object['cpoe_stat'] == 1) {
              return true;
        } else {
            return false;
        }
    }
}
