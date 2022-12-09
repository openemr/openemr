<?php

if($link_immunizations) {
	if($imm_code) AddImmunization($pid,$imm_code,$data);
}

if($link_inventory) {
	$sale = array(
		'drug_id' => $drug{'drug_id'},
		'inventory_id' => $data['ij1_inv_id'],
		'pid' => $pid,
		'encounter' => '',
		'sale_dt' => substr($data['form_dt'],0,10),
		'quantity' => $data['ij1_dose'],
		'fee' => '0.00',
		'billed' => 0,
		'notes' => $data['ij1_observation']);
	AddDrugSales($sale);
}

if($create_billing) {
	foreach($codes as $item) {
		list($type, $code) = explode(':', $item);
		if($code_types[$type]['claim']) {
			$desc = lookup_code_descriptions($item);
			$fee = 0;
			$ndc_info = '';
			if($type == 'HCPCS') $ndc_info = 'N4' .$drug{'ndc_number'} .
				'   ' . $ndc_uom . $data['ij1_dose'];
		
			if($code_types[$type]['fee']) {
				$fee = getFee($type, $code, $patient->pricelevel);
			}
			$_auth = $_SESSION['userauthorized'];
			$_auth = 1;
			addBilling($encounter, $type, $code, $desc, $pid, 
				$_auth, $visit->provider_id, '', '', $fee, $ndc_info);
		}
	}
}
?>
