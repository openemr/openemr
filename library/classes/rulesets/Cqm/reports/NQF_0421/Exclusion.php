<?php

// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
class NQF_0421_Exclusion implements CqmFilterIF
{
    public function getTitle()
    {
        return "Exclusion";
    }

    public function test(CqmPatient $patient, $beginDate, $endDate)
    {
        // Check for terminal illness within 6 months of encounter
        $dates = Helper::fetchEncounterDates(Encounter::ENC_OUTPATIENT, $patient, $beginDate, $endDate);
        foreach ($dates as $date) {
            $dateMinusSixMonths = strtotime('-6 month', strtotime($date));
            $dateMinusSixMonths = date('Y-m-d 00:00:00', $dateMinusSixMonths);
            if (Helper::check(ClinicalType::CHARACTERISTIC, Characteristic::TERMINAL_ILLNESS, $patient, $dateMinusSixMonths, $date)) {
                return true;
            }
        }

        if (
            Helper::check(ClinicalType::DIAGNOSIS, Diagnosis::PREGNANCY, $patient, $beginDate, $endDate) ||
            Helper::check(ClinicalType::PHYSICAL_EXAM, PhysicalExam::NOT_DONE_PATIENT, $patient, $beginDate, $endDate) ||
            Helper::check(ClinicalType::PHYSICAL_EXAM, PhysicalExam::NOT_DONE_MEDICAL, $patient, $beginDate, $endDate) ||
            Helper::check(ClinicalType::PHYSICAL_EXAM, PhysicalExam::NOT_DONE_SYSTEM, $patient, $beginDate, $endDate)
        ) {
            return true;
        }

        return false;
    }
}
