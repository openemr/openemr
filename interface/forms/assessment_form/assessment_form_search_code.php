<?php //Templates List
	require_once("../../globals.php");
	require_once("$srcdir/options.inc.php");
	//$return = $row['formatted_dx_code'];
	if(isset($_GET['search_code']) && $_GET['search_code'] != ""){
		if($_GET['codetype'] == 'icd'){
			$sql = "SELECT * FROM icd10_dx_order_code WHERE active = 1 AND valid_for_coding = 1 AND ((formatted_dx_code LIKE '%".$_GET['search_code']."%') OR (short_desc LIKE '%".$_GET['search_code']."%')) LIMIT 20";
		}
		else if($_GET['codetype'] == 'cpt'){
			$sql = "SELECT code_text as short_desc, code as formatted_dx_code FROM codes WHERE code_type=1 AND ((code LIKE '%".$_GET['search_code']."%') OR (code_text LIKE '%".$_GET['search_code']."%'))  LIMIT 20";	
		}
		$results = sqlStatement($sql);
		while ($row = sqlFetchArray($results)) {
			if(isset($_GET['search_code']) && $_GET['search_code'] != ""){
				if($_GET['codetype'] == "cpt"){
					$description = $row['short_desc']." [CPT4]";
				}
				else{
					$description = $row['short_desc'];
				}			
				$return[] = Array(
					"code"=> $row['formatted_dx_code'],
					"code_text"=> $description,
				);
			}else{				
				$return[] = $row['short_desc']." (".$row['formatted_dx_code'].")";
			}
		  // $code         = $row['code'];
		  // $code_text    = $row['code_text'];
		  // $code_type    = $row['code_type'];
		  // $modifier     = $row['modifier'];
		  // $units        = $row['units'];
		  // $superbill    = $row['superbill'];
		  // $related_code = $row['related_code'];
		  // $cyp_factor   = $row['cyp_factor'];
		  // $taxrates     = $row['taxrates'];
		  // $active       = 0 + $row['active'];
		  // $reportable   = 0 + $row['reportable'];
		  // $financial_reporting  = 0 + $row['financial_reporting'];
		}
	}
	
	echo json_encode($return, true);
?>