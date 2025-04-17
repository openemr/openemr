<?php

// Copyright (C) 2011 Brady Miller <brady.g.miller@gmail.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//


class AMC_302f_Numerator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_302f Numerator";
    }

    public function test(AmcPatient $patient, $beginDate, $endDate)
    {
        // Record and chart changes in vital signs
        //  Need height,weight,BP, and BMI.
        if (
            (exist_database_item($patient->id, 'form_vitals', 'height', 'gt', '0', 'ge', 1, '', '', $endDate)) &&
             (exist_database_item($patient->id, 'form_vitals', 'weight', 'gt', '0', 'ge', 1, '', '', $endDate)) &&
             (exist_database_item($patient->id, 'form_vitals', 'bps', '', '', 'ge', 1, '', '', $endDate)) &&
             (exist_database_item($patient->id, 'form_vitals', 'bpd', '', '', 'ge', 1, '', '', $endDate)) &&
             (exist_database_item($patient->id, 'form_vitals', 'BMI', 'gt', '0', 'ge', 1, '', '', $endDate))
        ) {
            return true;
        } else {
            return false;
        }
    }
}
