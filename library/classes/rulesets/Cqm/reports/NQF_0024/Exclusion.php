<?php

// Copyright (C) 2015 Ensoftek Inc
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

class NQF_0024_Exclusion implements CqmFilterIF
{
    public function getTitle()
    {
        return "Exclusion";
    }

    public function test(CqmPatient $patient, $beginDate, $endDate)
    {
        //Also exclude patients with a diagnosis of pregnancy during the measurement period.
        if (Helper::check(ClinicalType::DIAGNOSIS, Diagnosis::PREGNANCY, $patient, $beginDate, $endDate)) {
            return true;
        }

        return false;
    }
}
