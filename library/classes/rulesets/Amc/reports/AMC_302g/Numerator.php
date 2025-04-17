<?php

// Copyright (C) 2011 Brady Miller <brady.g.miller@gmail.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//


class AMC_302g_Numerator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_302g Numerator";
    }

    public function test(AmcPatient $patient, $beginDate, $endDate)
    {
        // Is smoking status recorded as structured data before the end date of the report
        if (exist_lifestyle_item($patient->id, 'tobacco', '', $endDate)) {
            return true;
        } else {
            return false;
        }
    }
}
