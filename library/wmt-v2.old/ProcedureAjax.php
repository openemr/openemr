<?php
/** **************************************************************************
 *	ProceudreAjax.PHP
 *
 *	Copyright (c)2013 - Williams Medical Technology, Inc.
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
 *  @package OEMR
 *  @subpackage procedure
 *  @version 1.0
 *  @copyright Williams Medical Technologies, Inc.
 *  @author Rich Genandt <rgenandt@gmail.com>
 * 
 *************************************************************************** */

// SANITIZE ALL ESCAPES
$sanitize_all_escapes = true;

// STOP FAKE REGISTER GLOBALS
$fake_register_globals = false;

require_once("../../interface/globals.php");

// Get request type
$type = $_REQUEST['type'];

if ($type == 'icd9') {
	$code = strtoupper($_REQUEST['code']);

	$query = "SELECT formatted_dx_code AS code, short_desc, long_desc FROM icd9_dx_code ";
	$query .= "WHERE formatted_dx_code LIKE '".$code."%' ";
	if (!is_numeric($code)) $query .= "OR short_desc LIKE '%".$code."%' ";
	$query .= "ORDER BY dx_code";
	$result = sqlStatement($query);

	$count = 1;
	$data = array();
	while ($record = sqlFetchArray($result)) {
		$data[$count++] = array('code'=>$record['code'],'short_desc'=>$record['short_desc'],'long_desc'=>$record['long_desc']);		
	}
	
	echo json_encode($data);
}

if ($type == 'cpt4') {
	$code = strtoupper($_REQUEST['code']);

	$query = "SELECT code, code_text_short, code_text FROM codes ";
	$query .= "WHERE code_type = 1 AND code LIKE '".$code."%' ";
	if (!is_numeric($code)) $query .= "OR code_text LIKE '%".$code."%' ";
	$query .= "ORDER BY code";
	$result = sqlStatement($query);

	$count = 1;
	$data = array();
	while ($record = sqlFetchArray($result)) {
		$data[$count++] = array('code'=>$record['code'],'short_desc'=>$record['code_text_short'],'long_desc'=>$record['code_text']);		
	}

	echo json_encode($data);
}


?>
