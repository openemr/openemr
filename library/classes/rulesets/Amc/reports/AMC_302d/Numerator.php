<?php

// Copyright (C) 2011 Brady Miller <brady.g.miller@gmail.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//


class AMC_302d_Numerator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_302d Numerator";
    }

    public function test(AmcPatient $patient, $beginDate, $endDate)
    {
        // Have at least one entry or an indication that no medications are known for the
        // patient recorded as structured data.
        //  (true if an entry in lists_touch(medication token) or an active entry in lists)
        //  (In the lists_touch, theres is also a special case entry to support the NewCropRx module
        //   since that module also manages medications. These special case entries use the prescription_erx
        //   token.)
        $firstCheck = sqlQuery("SELECT * FROM `lists_touch` WHERE `pid`=? AND (`type`=? OR type=?)", array($patient->id,'medication','prescription_erx'));
        $secondCheck = sqlQuery("SELECT * FROM `lists` WHERE `activity`='1' AND `pid`=? AND `type`=?", array($patient->id,'medication'));
        if (!(empty($firstCheck)) || !(empty($secondCheck))) {
            return true;
        } else {
            return false;
        }
    }
}
