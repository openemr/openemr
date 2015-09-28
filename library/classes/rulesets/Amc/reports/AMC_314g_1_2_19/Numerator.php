<?php
/**
 *
 * AMC 314g_1_2_19 STAGE1 Numerator
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

class AMC_314g_1_2_19_Numerator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_314g_1_2_19 Numerator";
    }
    
    public function test( AmcPatient $patient, $beginDate, $endDate ) 
    {
		//Secure electronic message received by EP using secure electronic messaging function of CEHRT
		$smQry = "SELECT  IF(sm.from_type = 2, sm.from_id, (SELECT pgd.pid from patient_guardian_details pgd where pgd.id = sm.from_id)) as pat_id FROM secure_messages sm ".
				 "INNER JOIN secure_message_details smd ON sm.message_id = smd.message_id AND sm.from_type IN(2,3) AND smd.to_type = 1 ".
				 "WHERE sm.message_time BETWEEN ? AND ? ".
				 "HAVING pat_id = ? ";
		$check = sqlQuery( $smQry, array($beginDate, $endDate, $patient->id) );   
		if (!(empty($check))){
			return true;
		}else{
			return false;
		}
    }
}
?>
