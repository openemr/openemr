<?php
/**
 *
 * CQM NQF 0002 Initial Patient Population
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
 
class NFQ_0002_InitialPatientPopulation implements CqmFilterIF
{
    public function getTitle() 
    {
        return "Initial Patient Population";
    }
    
    public function test( CqmPatient $patient, $beginDate, $endDate )
    {
        $age = $patient->calculateAgeOnDate( $beginDate );
        if (  $age >= 2 && $age <= 18  ) {
			//Children 2-18 years of age who had an outpatient or emergency department (ED) visit with a diagnosis of pharyngitis during the measurement period and an antibiotic ordered on or three days after the visit
			$pharyngitisArr = array('ICD9:034', 'ICD9:462', 'ICD9:463', 'ICD10:J02.0', 'ICD10:J02.8', 'ICD10:J02.9', 'ICD10:J03.80', 'ICD10:J03.81', 'ICD10:J03.90', 'ICD10:J03.91');
			$query = "SELECT l.title as drug FROM form_encounter fe ".
					 "INNER JOIN openemr_postcalendar_categories opc ON fe.pc_catid = opc.pc_catid ".
					 "INNER JOIN lists l ON l.type = 'medication' AND fe.pid = l.pid ".
					 "WHERE opc.pc_catname = 'Office Visit' ";
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
			$query .= " AND fe.pid = ? AND (fe.date BETWEEN ? AND ?)";
			$check = sqlQuery( $query, array($patient->id, $beginDate, $endDate) );
			if ($check['drug'] != ""){
				return true;
			}else{
				return false;
			}
        }
        
        return false;
    }
}
