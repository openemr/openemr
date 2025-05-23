<?php

/**
 *
 * AMC 314g_1_2_14 STAGE1 Denominator
 *
 * Copyright (C) 2015 Ensoftek, Inc
 * Copyright (C) 2015 Brady Miller <brady.g.miller@gmail.com>
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
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @link    http://www.open-emr.org
 */

class AMC_314g_1_2_14_Denominator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_314g_1_2_14 Denominator";
    }

    public function test(AmcPatient $patient, $beginDate, $endDate)
    {
        // Seen by the EP
        //  (basically needs an encounter within the report dates)
        $options = array( Encounter::OPTION_ENCOUNTER_COUNT => 1 );
        if (Helper::checkAnyEncounter($patient, $beginDate, $endDate, $options)) {
            return true;
        } else {
            return false;
        }
    }
}
