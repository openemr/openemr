<?php

/**
 * patient.inc includes functions for manipulating patient information.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * author sherwingaddis@gmail.com
 */
 
require_once("{$GLOBALS['srcdir']}/sql.inc");

$GLOBALS['form_exit_url'] = $GLOBALS['concurrent_layout'] ?
	"$rootdir/patient_file/encounter/encounter_top.php" :
	"$rootdir/patient_file/encounter/patient_encounter.php";


 function fetchProcedureId($pid, $encounter)
 {
     $sql = "SELECT procedure_order_id FROM procedure_order WHERE patient_id = $pid AND encounter_id = $encounter";
	 $res = sqlQuery($sql);
	 
	 return $res['procedure_order_id'];
	 
 }	
	
 function getProceduresInfo($oid)
 {
     $sql = "SELECT pc.procedure_order_id, pc.procedure_order_seq, pc.procedure_code, pc.procedure_name, pc.diagnoses, 
	                po.provider_id, po.date_collected, po.date_ordered, po.patient_instructions, po.specimen_type, po.specimen_location, po.specimen_volume, po.clinical_hx 
		     FROM procedure_order_code AS pc JOIN procedure_order AS po ON pc.procedure_order_id AND po.procedure_order_id 
			 WHERE pc.procedure_order_id = $oid AND po.procedure_order_id = $oid" ;
			 
	$res = sqlQuery($sql);
	
	return $res;
			 
 
 }
 
 function getNPI($prov_id)
 {
     $sql = "SELECT npi, upin FROM users WHERE id = $prov_id";
	 $res = sqlQuery($sql);
	 return array($res['npi'], $res['upin']);
 }