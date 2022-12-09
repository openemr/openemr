<?php
/** **************************************************************************
 *	QUEST/QuestAjax.PHP
 *
 *	Copyright (c)2014 - Medical Technology Services (MDTechSvcs.com)
 *
 *	This program is licensed software: licensee is granted a limited nonexclusive
 *  license to install this Software on more than one computer system, as long as all
 *  systems are used to support a single licensee. Licensor is and remains the owner
 *  of all titles, rights, and interests in program.
 *
 *  Licensee will not make copies of this Software or allow copies of this Software 
 *  to be made by others, unless authorized by the licensor. Licensee may make copies 
 *  of the Software for backup purposes only.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT 
 *	ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 *  FOR A PARTICULAR PURPOSE. LICENSOR IS NOT LIABLE TO LICENSEE FOR ANY DAMAGES, 
 *  INCLUDING COMPENSATORY, SPECIAL, INCIDENTAL, EXEMPLARY, PUNITIVE, OR CONSEQUENTIAL 
 *  DAMAGES, CONNECTED WITH OR RESULTING FROM THIS LICENSE AGREEMENT OR LICENSEE'S 
 *  USE OF THIS SOFTWARE.
 *
 *  @package laboratory
 *  @subpackage quest
 *  @version 2.0
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <info@keyfocusmedia.com>
 * 
 *************************************************************************** */

// SANITIZE ALL ESCAPES
$sanitize_all_escapes = true;

// STOP FAKE REGISTER GLOBALS
$fake_register_globals = false;

require_once("../../../interface/globals.php");
require_once("{$GLOBALS['srcdir']}/wmt/wmt.include.php");
require_once("{$GLOBALS['srcdir']}/classes/Document.class.php");

// Get request type
$type = $_REQUEST['type'];

if ($type == 'icd9') {
	$code = strtoupper($_REQUEST['code']);
	$words = explode(' ', $code);

	if ($GLOBALS['wmt_lab_icd10']) {  // doing ICD10 for everything
		$xcode = str_replace('.', '', $code);
		$query = "SELECT CONCAT('ICD10:',formatted_dx_code) AS code, short_desc, long_desc FROM icd10_dx_order_code ";
		$query .= "WHERE active = 1 AND valid_for_coding = 1 AND (formatted_dx_code LIKE '".$code."%' ";
		if (!is_numeric($code)) {
			$short = $long = "";
			foreach ($words AS $word) {
				if ($short) $short .= " AND ";				
				$short .= "short_desc LIKE '%".$word."%' ";
				if ($long) $long .= " AND ";				
				$long .= "long_desc LIKE '%".$word."%' ";
			}
			$query .= "OR ($short) OR ($long) ";
		}
		$query .= ") OR (dx_code IN (SELECT dx_icd10_target FROM icd10_gem_dx_9_10 WHERE dx_icd9_source LIKE '".$xcode."%') ) ";
		$query .= "ORDER BY dx_code";
		$result = sqlStatement($query);
	} else { // old ICD9 still in use
		$query = "SELECT formatted_dx_code AS code, short_desc, long_desc FROM icd9_dx_code ";
		$query .= "WHERE formatted_dx_code LIKE '".$code."%' AND active = 1 ";
		if (!is_numeric($code)) $query .= "OR short_desc LIKE '%".$code."%' ";
		$query .= "ORDER BY dx_code";
		$result = sqlStatement($query);
	}

	// transmit appropriate results
	$count = 1;
	$data = array();
	while ($record = sqlFetchArray($result)) {
		$data[$count++] = array('code'=>$record['code'],'short_desc'=>$record['short_desc'],'long_desc'=>$record['long_desc']);		
	}
	
	echo json_encode($data);
}

if ($type == 'code') {
	$code = strtoupper($_REQUEST['code']);
	$lab_id = $_REQUEST['lab_id'];

	$query = "SELECT procedure_type_id AS id, procedure_type AS type, description, procedure_code AS code, name AS title, lab_id AS provider FROM procedure_type ";
	$query .= "WHERE activity = 1 AND lab_id = ".$lab_id." ";
	$query .= "AND (procedure_type = 'ord' OR procedure_type = 'pro') ";
	$query .= "AND (procedure_code LIKE '%".$code."%' ";
	if (!is_numeric($code)) $query .= "OR name LIKE '%".$code."%'";
	$query .= ") GROUP BY procedure_code ORDER BY procedure_code "; 
	$result = sqlStatement($query);

	$count = 1;
	$data = array();
	while ($record = sqlFetchArray($result)) {
//		$data[$count++] = array('id'=>$record['id'],'code'=>$record['code'],'title'=>$record['title'],'description'=>$record['description'],'provider'=>$record['provider']);
		$data[$count++] = array('id'=>$record['id'],'code'=>$record['code'],'type'=>$record['type'],'title'=>$record['title'],'description'=>$record['description'],'provider'=>$record['provider']);
	}

	echo json_encode($data);
}

if ($type == 'details') {
	$code = strtoupper($_REQUEST['code']);
	$lab_id = $_REQUEST['lab_id'];
	
	// determine the type of test
	$query = "SELECT procedure_code AS code, standard_code AS unit, procedure_type, related_code AS components, specimen, transport FROM procedure_type ";
	$query .= "WHERE activity = 1 AND lab_id = ? AND procedure_code = ?";
	$query .= "AND (procedure_type = 'ord' OR procedure_type = 'pro') ";
	$record = sqlQuery($query,array($lab_id,$code));

	$type = ($record['specimen'])? $record['specimen'] : '';
	$state = ListLook($record['transport'],'Quest_States');
	if (!$state || $state == '* Not Found *') $state = $record['transport'];
	$unit = ($record['unit'])? str_replace('UNIT:', '', $record['unit']) : '';
	
	$type = null;
	if ($record['procedure_type']) $type = $record['procedure_type'];
	
	// retrieve all component test if profile
	$codes = "";
	$profile = array();
	if ($type == 'pro' && $record['components']) {
		$comps = explode("^", $record['components']);
		if (!is_array($comps)) $comps = array($comps); // convert to array if necessary
		foreach ($comps AS $comp) {
			if ($codes) $codes .= ",";
			$codes .= "'UNIT:$comp'"; 	
		}
	}
	
	if ($codes) {
		$query = "SELECT procedure_type_id AS id, procedure_code AS component, description, name AS title FROM procedure_type ";
		$query .= "WHERE activity = 1 AND lab_id = ".$lab_id." AND procedure_type = 'ord' ";
		$query .= "AND standard_code IN ( ".$codes." ) ";
		$query .= "GROUP BY procedure_code ORDER BY procedure_code ";
		$result = sqlStatement($query);
	
		while ($record = sqlFetchArray($result)) {
			$description = ($record['description'])? $record['description'] : $record['title'];
			$profile[$record['component']] = array('code'=>$code,'component'=>$record['component'],'description'=>$description);
		}
	}
	
	// retrieve all AOE questions
	$aoe = array();
	$result = sqlStatement("SELECT question_code, question_text, tips FROM procedure_questions ".
		"WHERE procedure_code = ? AND lab_id = ? AND activity = 1 ORDER BY seq",
			array($code,$lab_id));
	
	while ($record = sqlFetchArray($result)) {
		$aoe[] = array('code'=>$record['question_code'],'question'=>$record['question_text'],'prompt'=>$record['tips']);
	}
	
	$data = array('profile'=>$profile,'aoe'=>$aoe,'type'=>$type,'state'=>$state,'unit'=>$unit);
	echo json_encode($data);
}


if ($type == 'overview') {
	$code = strtoupper($_REQUEST['code']);

	$dos = array();
	
	//$query = "SELECT * FROM labcorp_dos ";
	//$query .= "WHERE test_cd = '".$code."' ";
	//$query .= "LIMIT 1 ";
	//$data = sqlQuery($query);
	
	$query = "SELECT det.name, ord.procedure_code AS code, det.name AS title, det.description, det.notes FROM procedure_type det ";
	$query .= "LEFT JOIN procedure_type ord ON ord.procedure_type_id = det.parent ";
	$query .= "WHERE ord.activity = 1 AND det.procedure_type = 'det' AND ord.procedure_code  = '".$code."' ";
	$query .= "ORDER BY det.seq ";
	$result = sqlStatement($query);
	
//	echo "<div style='width:480px;text-align:center;padding:10px;font-weight:bold;font-size:16px;background-color:#7ABEF3;color:black'>DIRECTORY OF SERVICE INFORMATION</div>\n";
	echo "<div class='wmtLabBar'>DIRECTORY OF SERVICE INFORMATION</div>\n";
	echo "<div style='overflow-y:auto;overflow-x:hidden;height:350px;width:450p;margin-top:10px'>\n";

	$none = true;
	while ($data = sqlFetchArray($result)) {
		if (empty($data['notes'])) continue;
		$none = false;
		echo "<h4 style='margin-bottom:0'>".$data['name']."</h4>\n";
		echo "<div class='wmtOutput' style='padding-right:10px;white-space:pre-wrap;font-family:monospace'>\n";
		echo $data['notes']."\n";
		echo "</div>\n";
	}

	if ($none) {
		echo "<h4 style='margin-bottom:0'>NO DETAILS AVAILABLE</h4>\n";
		echo "<div class='wmtOutput' style='padding-right:10px;white-space:pre-wrap;font-family:monospace'>\n";
		echo "Please contact your Quest Diagnostics representative for information\n";
		echo "about this laboratory test. Additional information may be available\n";
		echo "on the <a href='http://www.questdiagnostics.com/testcenter/TestCenterHome.action' target='_blank'>http://questdiagnostics.com/testcenter</a> website.";
		echo "</div>\n";
	}
	echo "<br/></div>";
}

if ($type == 'dynamic') {
	$code = strtoupper($_REQUEST['code']);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "http://www.interpathlab.com/tests/testfiles/".$code.".htm");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$contents = curl_exec($ch);
	curl_close ($ch);
	
	echo "<div class='body_title' style='width:680px;text-align:center;padding:10px;font-weight:bold;font-size:16px;color:black'>DIRECTORY OF SERVICE INFORMATION</div>\n";
	echo "<div class='dos' style='overflow-y:auto;overflow-x:hidden;height:350px;width:650p;margin-top:10px'>\n";

//	while ($data = sqlFetchArray($result)) {
//		echo "<h4 style='margin-bottom:0'>".$data['name']."</h4>\n";
//		echo "<div class='wmtOutput' style='padding-right:10px;white-space:pre-wrap'>\n";
//		echo "<b>".$data['notes']."</b><br/>\n";
//		echo "</div>\n";
//	}

	$start = stripos($contents, '<table');
	$contents = substr($contents, $start);
	$end = strripos($contents, '</table>');
	$contents = substr($contents, 0, $end);
	
	echo $contents;
	
	echo "</div>";
}

if ($type == 'label') {
	require_once("{$GLOBALS['srcdir']}/wmt/wmt.include.php");

	$address = $_REQUEST['printer'];
	$printer = ($address == 'file')? 'file' : ListLook($address, 'Quest_Label_Printers');
	$order = $_REQUEST['order'];
	$patient = strtoupper($_REQUEST['patient']);
	$client = $_REQUEST['siteid'];
	$pid = $_REQUEST['pid'];
	
	$count = 1;
	if ($_REQUEST['count']) $count = $_REQUEST['count'];
	
//	require_once("{$GLOBALS['srcdir']}/tcpdf/config/lang/eng.php");
	require_once("{$GLOBALS['srcdir']}/tcpdf/tcpdf.php");
	
	// create new PDF document
	$pdf = new TCPDF('L', 'pt', array(54,144), true, 'UTF-8', false);
	
	// remove default header/footer
	$pdf->setPrintHeader(false);
	$pdf->setPrintFooter(false);
	
	//set margins
	$pdf->SetMargins(15,5,20);
	$pdf->SetAutoPageBreak(FALSE, 35);
	
	//set some language-dependent strings
	$pdf->setLanguageArray($l);
	
	// define barcode style
	$style = array(
		'position' => '',
		'align' => 'L',
		'stretch' => true,
		'fitwidth' => false,
		'cellfitalign' => '',
		'border' => false,
		'hpadding' => 4,
		'vpadding' => 2,
		'fgcolor' => array(0,0,0),
		'bgcolor' => false, //array(255,255,255),
		'text' => false,
		'font' => 'helvetica',
		'fontsize' => 8,
		'stretchtext' => 4
	);
	
	// ---------------------------------------------------------
	
	do {
		$pdf->AddPage();
	
		$pdf->SetFont('times', '', 7);
		$pdf->Cell(0,5,'Client #: '.$client,0,1);
		$pdf->Cell(0,5,'Order #: '.$order,0,1);
	
		$pdf->SetFont('times', 'B', 8);
		$pdf->Cell(0,0,$patient,0,1,'','','',1);
	
		$pdf->write1DBarcode($client.'-'.$order, 'C39', '', '', 110, 25, '', $style, 'N');
		
		$count--;
		
	} while ($count > 0);

	// ---------------------------------------------------------
	if ($printer == 'file') {
		$repository = $GLOBALS['oer_config']['documents']['repository'];
		$label_file = $repository . preg_replace("/[^A-Za-z0-9]/","_",$pid) . "/" . $order . "_LABEL.pdf";

		$pdf->Output($label_file, 'F'); // force display download
		
		// register the new document
		$d = new Document();
		$d->name = $order."_LABEL.pdf";
		$d->storagemethod = 0; // only hard disk sorage supported
		$d->url = "file://" .$label_file;
		$d->mimetype = "application/pdf";
		$d->size = filesize($label_file);
		$d->owner = 'quest';
		$d->hash = sha1_file( $label_file );
		$d->type = $d->type_array['file_url'];
		$d->set_foreign_id($pid);
		$d->persist();
		$d->populate();
		
		echo $GLOBALS['web_root'].'/controller.php?document&retrieve&patient_id='.$pid.'&document_id='.$d->get_id();
	}
	else {
		$label = $pdf->Output('label.pdf','S'); // return as variable
		$CMDLINE = "lpr -P $printer ";
		$pipe = popen("$CMDLINE" , 'w' );
		if (!$pipe) {
			echo "Label printing failed...";
		}
		else {
			fputs($pipe, $label);
			pclose($pipe);
			echo "Labels printing at $printer ...";
		}
	}
}

if ($type == 'insurance') {
	$ins1 = $_REQUEST['ins1'];	
	$code1 = strtoupper($_REQUEST['code1']);

	if ($ins1 && $code1) {
		$query = "REPLACE INTO list_options SET option_id = '".$ins1."', title = '".$code1."', list_id = 'LabCorp_Insurance' ";
		sqlStatement($query);
	}
	
	$ins2 = $_REQUEST['ins2'];	
	$code2 = strtoupper($_REQUEST['code2']);
	
	if ($ins2 && $code2) {
		$query = "REPLACE INTO list_options SET option_id = '".$ins2."', title = '".$code2."', list_id = 'LabCorp_Insurance' ";
		sqlStatement($query);
	}
}


?>
