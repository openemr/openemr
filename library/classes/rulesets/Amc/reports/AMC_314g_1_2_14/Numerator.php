<?php
/**
 *
 * AMC 314g_1_2_14 STAGE1 Numerator
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

class AMC_314g_1_2_14_Numerator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_314g_1_2_14 Numerator";
    }
    
    public function test( AmcPatient $patient, $beginDate, $endDate ) 
    {
		//The number of patients in the denominator who have timely (within 4 business days after the information is available to the EP) on-line access to their health information. 
		//Patient Portal has access to done V/D/T
                //
                // AMC MU2 TODO :
                // This needs to be converted to the Z&H solution.
                //
		$portalQry = "SELECT count(*) as cnt FROM patient_data pd ".
					 "INNER JOIN ccda_log cl ON pd.pid = cl.patient_id AND cl.user_type = 2 AND cl.event IN ('patient-record-view', 'patient-record-download', 'patient-record-transmit') ".
					 "WHERE  pd.pid = ? AND cl.date BETWEEN ? AND ?";
		$check = sqlQuery( $portalQry, array($patient->id, $beginDate, $endDate) );  
		if ($check['cnt'] > 0){
			return true;
		}else{
			return false;
		}
    }
}
?>
