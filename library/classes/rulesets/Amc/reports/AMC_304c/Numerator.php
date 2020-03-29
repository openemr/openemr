<?php
// Copyright (C) 2011 Brady Miller <brady.g.miller@gmail.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//


class AMC_304c_Numerator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_304c Numerator";
    }
    
    public function test(AmcPatient $patient, $beginDate, $endDate)
    {
        // have demographics recorded as structured data
        //  Need preferred language, gender, race, ethnicity, date of birth.
        if ((exist_database_item($patient->id, 'patient_data', '', 'ge', 1, 'language', '')) &&
             (exist_database_item($patient->id, 'patient_data', '', 'ge', 1, 'sex', '')) &&
             (exist_database_item($patient->id, 'patient_data', '', 'ge', 1, 'race', '')) &&
             (exist_database_item($patient->id, 'patient_data', '', 'ge', 1, 'ethnicity', '')) &&
             (exist_database_item($patient->id, 'patient_data', '', 'ge', 1, 'DOB', ''))) {
            return true;
        } else {
            return false;
        }
    }
}
