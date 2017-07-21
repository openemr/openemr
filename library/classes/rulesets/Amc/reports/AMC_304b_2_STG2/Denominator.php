<?php
/**
 *
 * AMC 304b 2 STAGE2 Denominator
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
// Reporting period start and end date
// Prescription written for drugs requiring a prescription in order to be dispensed
// Denominator exclusion:
// Prescription written for controlled substance
// Generate and transmit permissible prescriptions electronically (other than controlled substances) queried for drug formulary).( AMC-2014:170.314(g)(1)/(2)-8 )

class AMC_304b_2_STG2_Denominator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_304b_2_STG2 Denominator";
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
