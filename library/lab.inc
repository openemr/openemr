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
	
 function getProceduresInfo($oid, $encounter)
 {

	 $sql = "SELECT pc.procedure_order_id, pc.procedure_order_seq, pc.procedure_code, pc.procedure_name, 
	 pc.diagnoses, po.provider_id, po.date_collected, po.clinical_hx, po.date_ordered, po.patient_instructions, po.specimen_type, 
	 po.specimen_location, po.specimen_volume
     FROM procedure_order_code AS pc  
     JOIN procedure_order AS po ON pc.procedure_order_id 
	 AND po.procedure_order_id 
     WHERE pc.procedure_order_id = $oid 
	 AND po.encounter_id = $encounter
	 AND po.procedure_order_id = $oid";
	 
	 $fetch = sqlQ($sql);
	 $orders = array();
	while($rows = sqlFetchArray($fetch)){
	    $orders[] = $rows['procedure_order_id'];
	    $orders[] = $rows['procedure_order_seq'];
		$orders[] = $rows['procedure_code'];
		$orders[] = $rows['procedure_name'];
		$orders[] = $rows['diagnoses'];
		$orders[] = $rows['provider_id'];
		$orders[] = $rows['date_collected'];
		$orders[] = $rows['clinical_hx'];
		$orders[] = $rows['date_ordered'];
		$orders[] = $rows['patient_instructions'];
		$orders[] = $rows['specimen_type'];
		$orders[] = $rows['specimen_location'];
		$orders[] = $rows['specimen_volume'];
	  }
	
	
	
	return $orders;
	
 }
 
 function getSelfPay($pid){
     $sql = "SELECT subscriber_relationship FROM insurance_data WHERE pid = $pid";
	 $res = sqlQuery($sql);
	 
	 return $res['subscriber_relationship'];
 
 }
 
 function getNPI($prov_id)
 {
     $sql = "SELECT npi, upin FROM users WHERE id = $prov_id";
	 $res = sqlQuery($sql);
	 return array($res['npi'], $res['upin']);
 }
 
 function getProcedureProviders()
 {
    $sql = "SELECT organization, street, city, state, zip, fax, phonew1 FROM `users` WHERE `valedictory` LIKE 'Lab Orders'";
	$res = sqlStatement($sql);
	$labs = sqlFetchArray($res);
	
	return $labs;
	
 }
 
 function getLabProviders($prov_id) {
    
    $sql = "select fname, lname from users where authorized = 1 and active = 1 and username != '' and id = ?";
    $rez = sqlQuery($sql, $prov_id);

    
    return $rez;
}
 
 /*
 * This is going to be adjusted when there is more than one provider.
 */
 function getLabconfig()
 {
    $sql = "SELECT recv_app_id, recv_fac_id FROM procedure_providers ";
	$res = sqlQuery($sql);
	return $res;
	
 }