<?php

/**
 *
 * CQM NQF 0384 Numerator
 *
 * Copyright (C) 2015 Ensoftek, Inc
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Ensoftek
 * @link    http://www.open-emr.org
 */

class NQF_0384_Numerator implements CqmFilterIF
{
    public function getTitle()
    {
        return "Numerator";
    }

    public function test(CqmPatient $patient, $beginDate, $endDate)
    {
        //Patient visits in which pain intensity is quantified
        $riskCatAssessQry = "SELECT count(*) as cnt FROM form_encounter fe " .
                            "INNER JOIN openemr_postcalendar_categories opc ON fe.pc_catid = opc.pc_catid " .
                            "INNER JOIN procedure_order pr ON  fe.encounter = pr.encounter_id " .
                            "INNER JOIN procedure_order_code prc ON pr.procedure_order_id = prc.procedure_order_id " .
                            "WHERE opc.pc_catname = 'Office Visit' " .
                            "AND (fe.date BETWEEN ? AND ?) " .
                            "AND fe.pid = ? " .
                            "AND ( prc.procedure_code = '38208-5') " .
                            "AND prc.procedure_order_title = 'Risk Category Assessment'";

        $check = sqlQuery($riskCatAssessQry, array($beginDate, $endDate, $patient->id));
        if ($check['cnt'] > 0) {
            return true;
        } else {
            return false;
        }
    }
}
