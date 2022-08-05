<?php

/**
 * patient_data_ajax.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2022 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . "/../../globals.php");
require_once($GLOBALS['srcdir'] . "/options.inc.php");
require_once($GLOBALS['srcdir'] . "/column_views.inc.php");

$pid = $_GET['pid'];

$query = "SELECT * FROM patient_data WHERE pid = ?";
$result = sqlStatement($query, array($pid));
$out = array();

$row = sqlFetchArray($result);

foreach($row as $column_name => $value) {
    $out[$column_name] = attr($value);
}

$out = array_merge($out, all_column_views($row));

// Dump the output array as JSON.
//
// Encoding with options for escaping a special chars - JSON_HEX_TAG (<)(>), JSON_HEX_AMP(&), JSON_HEX_APOS('), JSON_HEX_QUOT(").
//$json_out = json_encode($out, 15);
//echo $json_out;

$formType = "LBF_PATIENTLIST_DETAIL";
ob_start();
display_layout_tabs_data($formType, $out);
$patient_detail_HTML = ob_get_clean();
$json_patient_detail_HTML = json_encode($patient_detail_HTML, 15);

echo $json_patient_detail_HTML;