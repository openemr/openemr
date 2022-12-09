<?php
foreach($_POST as $key => $val) {
	if(is_string($val)) $val = trim($val);
	if(substr($key,0,5) != 'drug_') continue;
	unset($_POST[$key]);
}

// IN THIS INSTANCE SOME DATA ELEMENTS MUST BE LOADED FROM THE drug TABLE
// AND drug_inventory TABLE THAT WILL BE STORED WITH THE INJECTION
if($link_inventory) {
	list($trash, $drug_id) = explode(':', $data['ij1_cpt']);
	$drug = sqlQuery("SELECT * FROM drugs WHERE drug_id=?", array($drug_id));
	$inv  = sqlQuery("SELECT * FROM drug_inventory WHERE inventory_id=?",
			array($data['ij1_inv_id']));
	$dt['ij1_ndc'] = $data['ij1_ndc'] = $drug{'ndc_number'};
	$dt['ij1_dose_unit'] = $data['ij1_dose_unit'] = $drug{'unit'};
	$dt['ij1_lot'] = $data['ij1_lot'] = $inv{'lot_number'};
	$dt['ij1_expire'] = $data['ij1_expire'] = $inv{'expiration'};
	$dt['ij1_manufacturer'] = $data['ij1_manufacturer'] = $inv{'manufacturer'};
} else {
	$drug = array();
	$drug['ndc_number'] = $dt['ij1_ndc'];
}
f($create_billing) {
	$ndc_uom = TranslateNDCUnit($data['ij1_dose_unit']);
}
$imm_code = '';
if($link_immunizations || $link_inventory || $create_billing) {
	if($link_inventory) {
		$code_res = $drug{'related_code'};
	} else {
		$code_res = InjectionImmCodeLook($dt['ij1_cpt'],'Injection_CPT');
	}
	$codes = explode(';', $code_res);
	foreach($codes as $item) {
		list($type, $code) = explode(':', $item);
		// THIS IS TO CATCH THE OLD, OLD STYLE 
		if(is_numeric($type)) $imm_code = $type;
		if(strtoupper($type) == 'CVX') $imm_code = $code;
	}
}

if(!$_POST['form_time']) $_POST['form_time'] = date('H:i');
if(!$_POST['form_priority']) $_POST['form_priority'] = 'u';
?>
