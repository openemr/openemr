<?php

// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
class NQF_0028a_InitialPatientPopulation implements CqmFilterIF
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
