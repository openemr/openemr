<?php
// +-----------------------------------------------------------------------+
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// A copy of the GNU General Public License is included along with this 
// program:  openemr/interface/login/GnuGPL.html
// For more information write to the Free Software Foundation, Inc.
// 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
//
// +-----------------------------------------------------------------------+

// SANITIZE ALL ESCAPES
$sanitize_all_escapes = true;

// STOP FAKE REGISTER GLOBALS
$fake_register_globals = false;

require_once('../../../interface/globals.php');
require_once($GLOBALS['srcdir'].'/auth.inc');
require_once($GLOBALS['srcdir'].'/pnotes.inc');
require_once($GLOBALS['srcdir'].'/amc.php');
require_once($GLOBALS['srcdir'].'/wmt-v2/amc_ed.php');

$pid = $_SESSION['pid'];
if(isset($_REQUEST['pid'])) $pid = strip_tags($_REQUEST['pid']);
if(!isset($_REQUEST['mode'])) $_REQUEST['mode'] = '';
if(!isset($_REQUEST['link_type'])) $_REQUEST['link_type'] = '';
if(!isset($_REQUEST['link_id'])) $_REQUEST['link_id'] = '';
if(!isset($_REQUEST['ed_type'])) $_REQUEST['ed_type'] = 'patient_edu_amc';
if(!isset($_REQUEST['ed_code'])) $_REQUEST['ed_code'] = '';
if(!isset($_REQUEST['method'])) $_REQUEST['method'] = '';
if(!isset($_REQUEST['ref'])) $_REQUEST['ref'] = '';
$ref = $_REQUEST['ref'];
if(!isset($_REQUEST['language'])) $_REQUEST['language'] = '';
$language = strtolower($_REQUEST['language']);
$c = array();
if($_REQUEST['ed_code']) $c = explode(':', $_REQUEST['ed_code']);

if(!$pid || !$_REQUEST['ed_code'] || !$_REQUEST['mode']) {
	echo 'Nothing to Do';
	exit;
}

if($_REQUEST['mode'] == 'add') {
	amcAdd($_REQUEST['ed_type'], TRUE, $pid, $_REQUEST['link_type'],
			$_REQUEST['link_id']);
}

// BUILD THE REFERENCE LINK FOR CODE TYPES
if(count($c) > 0 && !$ref) {
	$codetype = $c[0];
	$codevalue = $c[1];
	// MedlinePlus Connect Web Application.  See:
	// http://www.nlm.nih.gov/medlineplus/connect/application.html
	$url = 'http://apps.nlm.nih.gov/medlineplus/services/mpconnect.cfm';
	// Set code type in URL.
	$url .= '?mainSearchCriteria.v.cs=';
	if ('ICD9'   == $codetype) $url .= '2.16.840.1.113883.6.103'; else
	if ('ICD10'  == $codetype) $url .= '2.16.840.1.113883.6.90' ; else
	if ('SNOMED' == $codetype) $url .= '2.16.840.1.113883.6.96' ; else
	if ('RXCUI'  == $codetype) $url .= '2.16.840.1.113883.6.88' ; else
	if ('NDC'    == $codetype) $url .= '2.16.840.1.113883.6.69' ; else
	if ('LOINC'  == $codetype) $url .= '2.16.840.1.113883.6.1'  ;
	// Set code value in URL.
	$url .= '&mainSearchCriteria.v.c=' . urlencode($codevalue);
	// Set language in URL if relevant. 
	// MedlinePlus supports only English or Spanish.
	if ($language == 'es' || $language == 'spanish') {
		$url .= '&informationRecipient.languageCode.c=es';
	}
	$ref = $url;
}

if($_REQUEST['mode'] == 'add') {
	if($_REQUEST['method'] == 'portal') {
		$txt = 'The following link has educational material ';
		if($c[0] == 'ICD10') $txt .= 'pertaining to the diagnosis ' . $c[1];
		$ref = '<a target="_blank" href="' . $ref;
		$ref .= '">Click To Review Educational Material</a>';
		$txt .= "\r" . $ref;
		if(!amcEdExists($pid, $_REQUEST['link_type'], $_REQUEST['link_id'],
			$_REQUEST['ed_type'], $_REQUEST['ed_code'], $_REQUEST['method'])) {
				addPnote($pid,$txt,$_SESSION['userauthorized'],'1','Patient Education',
					'-patient-');
				echo "Sent To Portal<br>\n";
		}
	}
	amcEdAdd($pid, TRUE, $_REQUEST['link_type'], $_REQUEST['link_id'],
		$_REQUEST['ed_type'], $_REQUEST['ed_code'], '', '', $_REQUEST['method'], 
		$ref);
	echo 'Amc Education Logged';
} else if($_REQUEST['mode'] == 'remove') {
	$item = amcEdCollect($pid, $_REQUEST['link_type'], $_REQUEST['link_id'],
		$_REQUEST['ed_type'], $_REQUEST['ed_code'], '', '', $_REQUEST['method']); 
	if($item) amcEdUncomplete($item);
	echo 'Amc Education Removed';
}


exit;

?>
