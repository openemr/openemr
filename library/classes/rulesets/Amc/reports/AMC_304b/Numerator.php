<?php

// Copyright (C) 2011 Brady Miller <brady.g.miller@gmail.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//


class AMC_304b_Numerator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_304b Numerator";
    }

    public function test(AmcPatient $patient, $beginDate, $endDate)
    {
        // Need a prescription escribed.
        //  (so basically an amc element needs to exist)
        $amcElement = amcCollect('e_prescribe_amc', $patient->id, 'prescriptions', $patient->object['id']);
        if (!(empty($amcElement))) {
            return true;
        } else {
            return false;
        }
    }
}
