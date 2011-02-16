<?
 // Copyright (C) 2011 Ensoftek 
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 // This program exports report to PQRI 2009 XML format.

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//


include_once("../interface/globals.php");
include_once("../library/patient.inc");
require_once "../library/options.inc.php";
require_once("../library/clinical_rules.php");
require_once("../library/classes/PQRIXml.class.php");

function getMeasureNumber($row) {
       if (!empty($row['cqm_pqri_code']) || !empty($row['cqm_nqf_code']) ) {
         if (!empty($row['cqm_pqri_code'])) {
         	return $row['cqm_pqri_code'];
         }
         if (!empty($row['cqm_nqf_code'])) {
         	return $row['cqm_nqf_code'];
         }
       }
       else 
       {
       	 return "";
       }
}


// Collect parameters (set defaults if empty)
$target_date = (isset($_GET['target_date'])) ? trim($_GET['target_date']) : date('Y-m-d H:i:s');
$nested = (isset($_GET['nested'])) ? trim($_GET['nested']) : 'false';
$xml = new PQRIXml();

// Add the XML parent tag.
$xml->open_submission();

// Add the file audit data
$xml->add_file_audit_data();

// Add the registry entries
if ( $nested == 'false') {
	$xml->add_registry('A');
}	
else {
	$xml->add_registry('E');
}


// Add the measure groups.
if ( $nested == 'false' ) {
	$dataSheet = test_rules_clinic('collate_outer','cqm',$target_date,'report','','','');
}
else {
	$dataSheet = test_rules_clinic('collate_inner','cqm',$target_date,'report','','cqm','plans');
}

$firstProviderFlag = TRUE;
$firstPlanFlag = TRUE;
$existProvider = FALSE;

if ( $nested == 'false' ){
     $xml->open_measure_group('X');
}

foreach ($dataSheet as $row) {
	//print_r($row);
 	if (isset($row['is_main']) || isset($row['is_sub'])) {
		if (isset($row['is_main'])) {
			// Add PQRI measures
 			$pqri_measures = array();
			$pqri_measures['pqri-measure-number'] =  getMeasureNumber($row);
			$pqri_measures['eligible-instances'] = $row['pass_filter'];
	       	$pqri_measures['meets-performance-instances'] = $row['pass_target'];
		    $pqri_measures['performance-exclusion-instances'] =  $row['excluded'];
		    $performance_not_met_instances = (int)$row['pass_filter'] - (int)$row['pass_target'] - (int)$row['excluded'];
            $pqri_measures['performance-not-met-instances'] = (string)$performance-not-met-instances;
			$pqri_measures['performance-rate'] = $row['percentage'];
	        $pqri_measures['reporting-rate'] = '';
	        $xml->add_pqri_measures($pqri_measures);	
		}
		else { // $row[0] == "sub"
				
		}
 	}
    else if (isset($row['is_provider'])) {
    	if ( $firstProviderFlag == FALSE ){
		     $xml->close_provider();
    	}
      	 // Add the provider
 		$physician_ids = array();
    	if (!empty($row['npi']) || !empty($row['federaltaxid'])) {
	       if (!empty($row['npi'])) {
	          $physician_ids['npi'] = $row['npi'];
	       }
	       if (!empty($row['federaltaxid'])) {
	           $physician_ids['tin'] = $row['federaltaxid'];
	       }
	     }
    	
       	 $xml->open_provider($physician_ids);
	     $firstProviderFlag = FALSE;
	     $existProvider = TRUE;
   }
   else { // isset($row['is_plan'])
   	
   	    if ( $firstPlanFlag == FALSE ) {
   	    	if ( $firstProviderFlag == FALSE ) {
		    	$xml->close_provider();
   	    	}
    	 	if ( $nested == 'true' ) {
    	 		$xml->close_measure_group();
    	 	}
    	}
   	
    	 if ( $nested == 'true' ){
    	 	$xml->open_measure_group($row['cqm_measure_group']);
    	 }
	     $firstPlanFlag = FALSE;
       	 $firstProviderFlag = TRUE; // Reset the provider flag
   }
 	 	
}

if ( $existProvider == TRUE ){
   	$xml->close_provider();
	$xml->close_measure_group();
}

$xml->close_submission();
 
?>

<html>
<head>
<? html_header_show();?>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<title><?php echo htmlspecialchars( xl('Export PQRI Report'), ENT_NOQUOTES); ?></title>
</head>
<body>

<p><?php echo htmlspecialchars( xl('The exported data appears in the text area below. You can copy and paste this into an email or to any other desired destination.'), ENT_NOQUOTES); ?></p>

<center>
<form>

<textarea rows='50' cols='500' style='width:95%' readonly>
<? echo $xml->getXml(); ?>
</textarea>

<p><input type='button' value='<?php echo htmlspecialchars( xl('OK'), ENT_QUOTES); ?>' onclick='window.close()' /></p>
</form>
</center>

</body>
</html>
