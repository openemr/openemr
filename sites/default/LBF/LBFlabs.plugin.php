<?php
// Copyright (C) 2015 Sherwin Gaddis sherwingaddis@gmail.com
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.




// the purpose of this javascript is to populate the lab for with patient information
//


function LBFlabs_javascript_onload() {
    global $pid, $encounter;
   
	$sql = "SELECT plan_name, policy_number FROM insurance_data WHERE pid = $pid AND type = 'primary' ";
	$isu = sqlStatement($sql);
	$isd = sqlFetchArray($isu);
	 
	$jinsur_id = $isd['policy_number'];
	$jinsur_name = $isd['plan_name'];          
	
	$sql = "SELECT diag_1, diag_2, diag_3, diag_4, diag_5 FROM care_plan WHERE pid = $pid AND encounter = $encounter";
	$c = sqlStatement($sql);
	$cl = sqlFetchArray($c);
	$cl1 = $cl['diag_1']; $cl2 = $cl['diag_2']; $cl3 = $cl['diag_3']; $cl4 = $cl['diag_4']; $cl5 = $cl['diag_5'];
		echo "var insur_id = '$jinsur_id';
			  var icds = '$cl1 | $cl2 | $cl3 | $cl4 | $cl5';
			  var insur_name = '$jinsur_name';
			  document.getElementById('form_insur_id').value = insur_id;
			  document.getElementById('form_related_code').value = icds; 
			  document.getElementById('form_insur').value = insur_name;
		 ";
 
 }