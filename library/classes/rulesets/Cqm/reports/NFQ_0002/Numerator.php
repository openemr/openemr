<?php
/**
 *
 * CQM NQF 0002 Numerator
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
 
class NFQ_0002_Numerator implements CqmFilterIF
{
    public function getTitle()
    {
        return "Numerator";
    }

    public function test( CqmPatient $patient, $beginDate, $endDate )
    {
		//Pharyngitis Array
		$pharyngitisArr = array('ICD9:034', 'ICD9:462', 'ICD9:463', 'ICD10:J02.0',  'ICD10:J02.8', 'ICD10:J02.9', 'ICD10:J03.80', 'ICD10:J03.81', 'ICD10:J03.90', 'ICD10:J03.91');
		//Group A Streptococcus Test Array 
		$streptococcusArr = array('11268-0', '17656-0', '18481-2', '31971-5', '49610-9');
		
		//Patients who were tested for Streptococcus A during the same encounter that the antibiotic was prescribed, Encounter category should be office visit.
		$query = "SELECT count(*) as cnt FROM form_encounter fe ".
				 "INNER JOIN openemr_postcalendar_categories opc ON fe.pc_catid = opc.pc_catid ".
				 "INNER JOIN lists l ON l.type = 'medication' AND fe.pid = l.pid ".
				 "INNER JOIN procedure_order po ON po.encounter_id = fe.encounter ".
				 "INNER JOIN procedure_order_code pc ON po.procedure_order_id = pc.procedure_order_id ".
				 "WHERE opc.pc_catname = 'Office Visit' ";
		
		//Pharyngitis Check
		$pharyngitisStr = "(";
		$cnt = 0;
		foreach($pharyngitisArr as $pharyngitisCode){
			if($cnt == 0)
				$pharyngitisStr .= " l.diagnosis LIKE '%".$pharyngitisCode."%' ";
			else
				$pharyngitisStr .= " OR l.diagnosis LIKE '%".$pharyngitisCode."%' ";
			$cnt++;
		}
		$pharyngitisStr .= ")";
		$query .= " AND ".$pharyngitisStr;
		
		//Group A Streptococcus Check
		$streptococcusStr = "(";
		$cnt = 0;
		foreach($streptococcusArr as $streproCode){
			if($cnt == 0)
				$streptococcusStr .= " pc.procedure_code = '".$streproCode."' ";
			else
				$streptococcusStr .= " OR pc.procedure_code =  '".$streproCode."' ";
			$cnt++;
		}
		$streptococcusStr .= ")";
		$query .= " AND ".$streptococcusStr;
		$query .= " AND fe.pid = ? AND (fe.date BETWEEN ? AND ?)";
		
		$check = sqlQuery( $query, array($patient->id, $beginDate, $endDate) );   
		if ($check['cnt'] > 0){
			return true;
		}else{
			return false;
		}
    }
}
