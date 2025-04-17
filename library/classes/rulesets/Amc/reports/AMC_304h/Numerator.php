<?php

/**
 * class AMC_304h_Numerator
 *
 * Copyright (C) 2011-2015 Brady Miller <brady.g.miller@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @link    http://www.open-emr.org
 */

class AMC_304h_Numerator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_304h Numerator";
    }

    public function test(AmcPatient $patient, $beginDate, $endDate)
    {
        // Need patient summary given/sent to patient within 3 business days of each encounter.
        $amcElement = amcCollect('provide_sum_pat_amc', $patient->id, 'form_encounter', $patient->object['encounter']);
        if (!(empty($amcElement))) {
            $daysDifference = businessDaysDifference(date("Y-m-d", strtotime($patient->object['date'])), date("Y-m-d", strtotime($amcElement['date_completed'])));
            if ($daysDifference < 4) {
                return true;
            }
        }

        return false;
    }
}
