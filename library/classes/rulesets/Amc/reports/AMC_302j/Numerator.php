<?php

// Copyright (C) 2011 Brady Miller <brady.g.miller@gmail.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//


class AMC_302j_Numerator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_302j Numerator";
    }

    public function test(AmcPatient $patient, $beginDate, $endDate)
    {
        // Need a medication reconciliation completed.
        //  (so basically the completed element of the object can't be empty
        $sql = "SELECT amc_misc_data.map_id as `encounter`, amc_misc_data.date_completed as `completed`, form_encounter.date as `date` " .
        "FROM `amc_misc_data`, `form_encounter` " .
        "WHERE amc_misc_data.map_id = form_encounter.encounter " .
        "AND amc_misc_data.map_category = 'form_encounter' " .
        "AND amc_misc_data.amc_id = 'med_reconc_amc' " .
        "AND form_encounter.encounter = ?";
        $check = sqlQuery($sql, array($patient->object['encounter']));
        if ($check['completed'] != "") {
            return true;
        } else {
            return false;
        }
    }
}
