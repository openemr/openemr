<?php

// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
class NQF_0064_Numerator2 implements CqmFilterIF
{
    public function getTitle()
    {
        return "Numerator 2";
    }

    public function test(CqmPatient $patient, $beginDate, $endDate)
    {
        $range = new Range(Range::NEG_INF, 100);
        $options = array( LabResult::OPTION_RANGE => $range );
        if (Helper::checkLab(LabResult::LDL_TEST, $patient, $beginDate, $endDate, $options)) {
            return true;
        }

        return false;
    }
}
