<?php

// Copyright (C) 2011 Brady Miller <brady.g.miller@gmail.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//


class AMC_302h_Numerator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_302h Numerator";
    }

    public function test(AmcPatient $patient, $beginDate, $endDate)
    {
        $procedure_order_id = $patient->object['procedure_order_id'];
        $sql =  "SELECT count(r.result) as cnt FROM procedure_result r " .
                "INNER JOIN procedure_report pr ON pr.procedure_report_id = r.procedure_report_id " .
                "INNER JOIN procedure_order po ON po.procedure_order_id = pr.procedure_order_id " .
                "WHERE r.result !=  '' " .
                "AND po.procedure_order_id = ?";
        $check = sqlQuery($sql, array($procedure_order_id));
        return $check['cnt'];
    }
}
