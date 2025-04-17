<?php

/**
 *
 * AMC 302f STAGE2 Denominator
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

class AMC_302f_STG2_Denominator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_302f_STG2 Denominator";
    }

    public function test(AmcPatient $patient, $beginDate, $endDate)
    {
        // If height/length, weight, and blood pressure (all) within scope of practice:
        // Number of unique patients seen by the EP during the EHR reporting period
        if (
            (exist_database_item($patient->id, 'form_vitals', 'height', 'gt', '0', 'ge', 1, '', '', $endDate)) &&
             (exist_database_item($patient->id, 'form_vitals', 'weight', 'gt', '0', 'ge', 1, '', '', $endDate)) &&
             (exist_database_item($patient->id, 'form_vitals', 'bps', 'gt', '0', 'ge', 1, '', '', $endDate)) &&
             (exist_database_item($patient->id, 'form_vitals', 'bpd', 'gt', '0', 'ge', 1, '', '', $endDate))
        ) {
            $options = array( Encounter::OPTION_ENCOUNTER_COUNT => 1 );
            if (Helper::checkAnyEncounter($patient, $beginDate, $endDate, $options)) {
                return true;
            } else {
                return false;
            }
        } elseif (
            (exist_database_item($patient->id, 'form_vitals', 'height', 'gt', '0', 'ge', 1, '', '', $endDate)) &&
                (exist_database_item($patient->id, 'form_vitals', 'weight', 'gt', '0', 'ge', 1, '', '', $endDate))
        ) {
            // If height/length and weight (only) within scope of practice:
            // Number of unique patients seen by the EP during the EHR reporting period
            $options = array( Encounter::OPTION_ENCOUNTER_COUNT => 1 );
            if (Helper::checkAnyEncounter($patient, $beginDate, $endDate, $options)) {
                return true;
            } else {
                return false;
            }
        } elseif (
            (exist_database_item($patient->id, 'form_vitals', 'bps', 'gt', '0', 'ge', 1, '', '', $endDate)) &&
                 (exist_database_item($patient->id, 'form_vitals', 'bpd', 'gt', '0', 'ge', 1, '', '', $endDate))
        ) {
            // If blood pressure (only) within scope of practice:
            // Number of unique patients 3 years of age or older seen by the EP during the EHR reporting period.
            $options = array( Encounter::OPTION_ENCOUNTER_COUNT => 1 );
            if (
                (Helper::checkAnyEncounter($patient, $beginDate, $endDate, $options)) &&
                 ($patient->calculateAgeOnDate($endDate) >= 3)
            ) {
                return true;
            } else {
                return false;
            }
        }
    }
}
