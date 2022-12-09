<?php

function LoadDrugs($mode='all', $order='name') {
	$filter = '1 ';
	if($mode == 'active') $filter .= ' AND active = 1 ';
	if($mode == 'inactive') $filter .= ' AND active = 0 ';
  $rlist= sqlStatementNoLog("SELECT * FROM drugs WHERE ".
		"$filter ORDER BY $order");
	$all = array();
	$iter = 0;
  while($row = sqlFetchArray($rlist)) {
		$row['detail'] = GetInventoryDetail($row{'drug_id'});
  	$all[$row{'drug_id'}] = $row;
		$iter++;
	}
	return $all;
}

function LoadDrugUnits() {
  $rlist= sqlStatementNoLog("SELECT option_id, title FROM list_options WHERE ".
		"list_id = 'drug_units' ORDER BY option_id");
	$all = array();
	$iter = 0;
  while($row = sqlFetchArray($rlist)) {
  	$all[$row{'option_id'}] = $row;
		$iter++;
	}
	return $all;
}

function GetDrugName($drug_id='') {
	if(!$drug_id) return '';
	if(strtoupper(substr($drug_id,0,4)) == 'INV:') {
		list($trash, $drug_id) = explode(':', $drug_id);
	}
	$sql = "SELECT drug_id, name FROM drugs WHERE drug_id=?";
	$rrow = sqlQuery($sql, array($drug_id));
	return htmlspecialchars($rrow{'name'}, ENT_QUOTES, '', FALSE);
}

function GetInventoryDetail($drug='') {
	if(!$drug) return '';
	$dtl = array();
	$rlist = sqlStatementNoLog('SELECT inventory_id, lot_number, expiration,'.
		' manufacturer, on_hand FROM drug_inventory WHERE drug_id=? AND'.
		' on_hand > 0 ORDER BY lot_number ASC', array($drug));
	$iter = 0;
	while($row = sqlFetchArray($rlist)) {
		$all[$iter] = $row;
		$iter++;
	}
	return $dtl;	
}

function AddDrugSales($sale) {
	if(!$sale['drug_id']) return false;
	if(!$sale['inventory_id']) return false;
	if(!$sale['pid']) return false;
	// if(!$sale['encounter']) return false;
	if(!$sale['sale_date'] || strtoupper($sale['sale_date']) == 'YYYY-MM-DD')
			$sale['sale_date'] = date('Y-m-d');
	$sql = "INSERT INTO drug_sales (drug_id, inventory_id, pid, encounter, ".
		"user, sale_date, quantity, fee, billed, notes) VALUES (?, ?, ?, ?, ?, ".
		"?, ?, ?, ?, ?)";
	$binds = array($sale['drug_id'], $sale['inventory_id'], $sale['pid'], 
		$sale['encounter'], $_SESSION['authUser'], $sale['sale_dt'], 
		$sale['quantity'], $sale['fee'], $sale['billed'], $sale['notes']);
	sqlInsert($sql, $binds);
	$sql = "UPDATE drug_inventory SET on_hand = (on_hand - ?) WHERE ".
		"inventory_id=?";
	unset($binds);
	$binds = array($sale['quantity'], $sale['inventory_id']);
	sqlStatement($sql, $binds);
}

function InventorySel($thisField='',$mode='all',$order='name',$empty_allowed=false,$empty_label='') {
	if($empty_allowed) {
  	echo '<option value=""';
  	if($thisField == '') echo ' selected="selected"';
  	echo '>'.$empty_label.'&nbsp;</option>';
	}
	$filter = '1 ';
	if($mode == 'active') $filter .= ' AND active = 1 ';
	if($mode == 'inactive') $filter .= ' AND active = 0 ';
  $rlist= sqlStatementNoLog("SELECT * FROM drugs WHERE ".
		"$filter ORDER BY $order");
  while ($rrow= sqlFetchArray($rlist)) {
    echo '<option value="INV:' . $rrow{'drug_id'} . '"';
    if($thisField == 'INV:'.$rrow{'drug_id'}) {
			echo ' selected="selected"';
		}
    echo ">" . htmlspecialchars($rrow{'name'}, ENT_QUOTES, '', FALSE);
    echo "</option>";
  }
}

function DrugLotSel($thisField='',$drug='',$mode='all',$order='lot_number',$empty_allowed=false,$empty_label='') {
	if(!$drug) return false;
	if($empty_allowed) {
  	echo '<option value=""';
  	if($thisField == '') echo ' selected="selected"';
  	echo '>'.$empty_label.'&nbsp;</option>';
	}
	$filter = 'drug_id = ? ';
	if($mode == 'active') $filter .= ' AND on_hand > 0 ';
	if($mode == 'inactive') $filter .= ' AND on_hand = 0 ';
  $rlist= sqlStatementNoLog("SELECT * FROM drug_inventory WHERE ".
		"$filter ORDER BY $order", array($thisField));
  while ($rrow= sqlFetchArray($rlist)) {
    echo '<option value="' . $rrow{'inventory_id'} . '"';
    if($thisField == $rrow{'inventory_id'}) {
			echo ' selected="selected"';
		}
    echo ">" . htmlspecialchars($rrow{'lot_number'}, ENT_QUOTES, '', FALSE) .
			' - ('. htmlspecialchars($rrow['on_hand'], ENT_QUOTES, '', FALSE) . 
			' On Hand)';
    echo "</option>";
  }
}

?>
