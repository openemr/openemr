<?php
/**
 * Print Amendments
 *
 * Copyright (C) 2014 Ensoftek
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Hema Bandaru <hemab@drcloudemr.com>
 * @link    http://www.open-emr.org
 */

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

include_once("../../globals.php");
include_once("$srcdir/sql.inc");
include_once("$srcdir/options.inc.php");

$amendments = add_escape_custom($_REQUEST["ids"]);
$amendments = rtrim($amendments,",");
$amendmentsList = explode(",",$amendments);

$patientDetails = getPatientData($pid,"fname,lname");
$patientName = $patientDetails['lname'] . ", " . $patientDetails['fname'];

function printAmendment($amendmentID,$lastAmendment) {
	$query = "SELECT lo.title AS 'amendmentFrom', lo1.title AS 'amendmentStatus',a.* FROM amendments a 
		LEFT JOIN list_options lo ON a.amendment_by = lo.option_id AND lo.list_id='amendment_from' 
		LEFT JOIN list_options lo1 ON a.amendment_status = lo1.option_id AND lo1.list_id = 'amendment_status'
		WHERE a.amendment_id = ?";
	$resultSet = sqlQuery($query,array($amendmentID));
	echo "<table>";
	echo "<tr class=text>";
	echo "<td class=bold>" . xlt("Requested Date") . ":"  . "</td>";
	echo "<td>". oeFormatShortDate($resultSet['amendment_date']) . "</td>";
	echo "</tr>";

	echo "<tr class=text>";
	echo "<td class=bold>" . xlt("Requested By") . ":"  . "</td>";
	echo "<td>". generate_display_field(array('data_type'=>'1','list_id'=>'amendment_from'),$resultSet['amendment_by']) . "</td>";
	echo "</tr>";

	echo "<tr class=text>";
	echo "<td class=bold>" . xlt("Request Status") . ":"  . "</td>";
	echo "<td>". generate_display_field(array('data_type'=>'1','list_id'=>'amendment_status'),$resultSet['amendment_status']) . "</td>";
	echo "</tr>";

	echo "<tr class=text>";
	echo "<td class=bold>" . xlt("Request Description") . ":"  . "</td>";
	echo "<td>". text($resultSet['amendment_desc']) . "</td>";
	echo "</tr>";	

	echo "</table>";
	
	echo "<hr>";
	echo "<span class='bold'>" . xlt("History") . "</span><br>";
	$pageBreak = ( $lastAmendment ) ? "" : "page-break-after:always";
	echo "<table border='1' cellspacing=0 cellpadding=3 style='width:75%;margin-top:10px;margin-bottom:20px;" . $pageBreak . "'>";
	echo "<tr class='text bold'>";
	echo "<th align=left style='width:10%'>" . xlt("Date") . "</th>";
	echo "<th align=left style='width:20%'>" . xlt("By") . "</th>";
	echo "<th align=left >" . xlt("Comments") . "</th>";
	echo "</tr>";
	
	$query = "SELECT u.fname,u.lname,ah.* FROM amendments_history ah INNER JOIN users u ON ah.created_by = u.id WHERE ah.amendment_id = ?";
	$resultSet = sqlStatement($query,array($amendmentID));
	while( $row = sqlFetchArray($resultSet)) {
		echo "<tr class=text>";
		$created_date = date('Y-m-d', strtotime($row['created_time']));
		echo "<td>" . oeFormatShortDate($created_date) . "</td>";
		echo "<td>" . text($row['lname']) . ", " . text($row['fname']) . "</td>";
		echo "<td>" . text($row['amendment_note']) . "</td>";
		echo "</tr>";
	}
	echo "</table>";
}

?>

<html>
<head>
<?php html_header_show();?>

<!-- supporting javascript code -->
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/textformat.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js"></script>


<!-- page styles -->
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

</head>

<body class="body_top">
	<span class='title'><?php echo xlt("Amendments for ") . text($patientName); ?></span>
	<p></p>

	<?php
		for ( $i = 0 ; $i < count($amendmentsList) ; $i++ ) {
			$lastAmendment = ( $i == count($amendmentsList) - 1 ) ? true : false;
			printAmendment($amendmentsList[$i],$lastAmendment);
		}
	?>

<script language='JavaScript'>
	window.print();
</script>
	
</body>

</html>