<?php

/**
 *
 * AMC 314g_1_2_20 STAGE1 Numerator
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

class AMC_314g_1_2_20_Numerator implements AmcFilterIF
{

    // Still TODO
    // AMC MU2 TODO :
    // 1. Remove the $patArr stuff.
    //
    public $patArr = array();
    public function getTitle()
    {
        return "AMC_314g_1_2_20 Numerator";
    }

    public function test(AmcPatient $patient, $beginDate, $endDate)
    {
        if (!in_array($patient->id, $this->patArr)) {
            $this->patArr[] = $patient->id;
                        //
                        // Still TODO
                        // AMC MU2 TODO :
                        // These mechanism seems really odd. Will need to research this a bit.
                        //
            $docLabQry = "SELECT count(*) as cnt FROM documents d " .
                         "INNER JOIN categories_to_documents cd ON d.id = cd.document_id " .
                         "INNER JOIN categories dlc ON cd.category_id = dlc.id AND dlc.name = 'Lab Report' " .
                         "INNER JOIN patient_data pd ON pd.pid = d.foreign_id " .
                         "WHERE d.foreign_id = ? AND (d.date BETWEEN ? AND ?) ";
            $check = sqlQuery($docLabQry, array($patient->id, $beginDate, $endDate));
            if ($check['cnt'] > 0) {
                return true;
            } else {
                return false;
            }
        }
    }
}
