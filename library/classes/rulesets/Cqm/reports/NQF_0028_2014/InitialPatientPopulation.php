<?php

/**
 *
 * CQM NQF 0028(2014) Initial Patient Population
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

class NQF_0028_2014_InitialPatientPopulation implements CqmFilterIF
{
    public function getTitle()
    {
        return "Initial Patient Population";
    }

    public function test(CqmPatient $patient, $beginDate, $endDate)
    {
        $oneEncounter = array( Encounter::OPTION_ENCOUNTER_COUNT => 1 );
        $twoEncounters = array( Encounter::OPTION_ENCOUNTER_COUNT => 2 );

        if (
            $patient->calculateAgeOnDate($beginDate) >= 18 &&
             ( Helper::check(ClinicalType::ENCOUNTER, Encounter::ENC_OFF_VIS, $patient, $beginDate, $endDate, $twoEncounters) ||
               Helper::check(ClinicalType::ENCOUNTER, Encounter::ENC_OPHTHAL, $patient, $beginDate, $endDate, $twoEncounters) ||
               Helper::check(ClinicalType::ENCOUNTER, Encounter::ENC_HEA_AND_BEH, $patient, $beginDate, $endDate, $twoEncounters) ||
               Helper::check(ClinicalType::ENCOUNTER, Encounter::ENC_OCC_THER, $patient, $beginDate, $endDate, $twoEncounters) ||
               Helper::check(ClinicalType::ENCOUNTER, Encounter::ENC_PSYCH_AND_PSYCH, $patient, $beginDate, $endDate, $twoEncounters) ||
               Helper::check(ClinicalType::ENCOUNTER, Encounter::ENC_PRE_MED_SER_18_OLDER, $patient, $beginDate, $endDate, $oneEncounter) ||
               Helper::check(ClinicalType::ENCOUNTER, Encounter::ENC_PRE_IND_COUNSEL, $patient, $beginDate, $endDate, $oneEncounter) ||
               Helper::check(ClinicalType::ENCOUNTER, Encounter::ENC_PRE_MED_GROUP_COUNSEL, $patient, $beginDate, $endDate, $oneEncounter) ||
               Helper::check(ClinicalType::ENCOUNTER, Encounter::ENC_PRE_MED_OTHER_SERV, $patient, $beginDate, $endDate, $oneEncounter)
             )
        ) {
            return true;
        }

        return false;
    }
}
