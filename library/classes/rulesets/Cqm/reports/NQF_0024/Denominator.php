<?php

// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
class NQF_0024_Denominator implements CqmFilterIF
{
    public function getTitle()
    {
        return "Denominator";
    }

    public function test(CqmPatient $patient, $beginDate, $endDate)
    {
        // Same as IPP
        return true ;
    }
}
