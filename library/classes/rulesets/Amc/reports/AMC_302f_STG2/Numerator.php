<?php

/**
 *
 * AMC 302f STAGE2 Numerator
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

class AMC_302f_STG2_Numerator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_302f_STG2 Numerator";
    }

    public function test(AmcPatient $patient, $beginDate, $endDate)
    {
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
