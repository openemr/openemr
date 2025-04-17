<?php

/**
 *
 * AMC 304b STAGE1 Denominator
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
// Denominator:
// Number of prescriptions written for drugs requiring a prescription in order to be
// dispensed other than controlled substances during the EHR reporting period

class AMC_304b_STG1_Denominator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_304b_STG1 Denominator";
    }

    public function test(AmcPatient $patient, $beginDate, $endDate)
    {
        // Check if prescription is for a controlled substance
        $controlledSubstanceCheck = amcCollect('e_prescribe_cont_subst_amc', $patient->id, 'prescriptions', $patient->object['id']);
        // Exclude controlled substances
        if (empty($controlledSubstanceCheck)) {
            // Not a controlled substance, so include in denominator.
            return true;
        } else {
            // Is a controlled substance, so exclude from denominator.
            return false;
        }
    }
}
