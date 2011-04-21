<?php
/********************************************************************************\
 * Copyright (C) ViCarePlus, Visolve (vicareplus_engg@visolve.com)              *
 *                                                                              *
 * This program is free software; you can redistribute it and/or                *
 * modify it under the terms of the GNU General Public License                  *
 * as published by the Free Software Foundation; either version 2               *
 * of the License, or (at your option) any later version.                       *
 *                                                                              *
 * This program is distributed in the hope that it will be useful,              *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of               *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                *
 * GNU General Public License for more details.                                 *
 *                                                                              *
 * You should have received a copy of the GNU General Public License            *
 * along with this program; if not, write to the Free Software                  *
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.  *
 \********************************************************************************/
require_once("../globals.php");
require_once("$srcdir/lists.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/translation.inc.php");
require_once("../../library/sqlconf.php");
?>
<?php
/*executes the De Identification process, using the parameters chosen from the
de_identification_screen1.php  */
$begin_date = $_POST["begin_date"];
$end_date = $_POST["end_date"];

if ($_POST["unstructured"])
$include_unstructured = 1;
else
$include_unstructured = 0;

if ($_POST["all"])
$include_tables = "all";
else
{
if ($_POST["history_data"])
$include_tables = $include_tables . $_POST["history_data"]. "#";
if ($_POST["prescriptions"])
$include_tables = $include_tables . $_POST["prescriptions"]. "#";
if ($_POST["immunization"])
$include_tables = $include_tables . $_POST["immunization"]. "#";
if ($_POST["lists"])
$include_tables = $include_tables . $_POST["lists"]. "#";
if ($_POST["transactions"])
$include_tables = $include_tables . $_POST["transactions"]. "#";
if ($_POST["insurance_data"])
$include_tables = $include_tables . $_POST["insurance_data"]. "#";
if ($_POST["billing_data"])
$include_tables = $include_tables . "billing#payments";
}

$diagnosis_text = $_POST["diagnosis_text"];
$drug_text = $_POST["drug_text"];
$immunization_text = $_POST["immunization_text"];

$query = "select status from de_identification_status";
$res = sqlStatement($query);
if ($row = sqlFetchArray($res))
{
	$deIdentificationStatus = addslashes($row['status']);
 /* $deIdentificationStatus:
 *  0 - There is no De Identification in progress. (start new De Identification process)
 *  1 - A De Identification process is currently in progress.
 *  2 - The De Identification process completed and xls file is ready to download
 *  3 - The De Identification process completed with error
 */  
}

if($deIdentificationStatus == 0)
{
 //0 - There is no De Identification in progress. (start new De Identification process)
		?>
<html>
<head>
<title>De Identification</title>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
<link rel="stylesheet"
	href='<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.css'
	type='text/css'>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js"></script>
<style type="text/css">
.style1 {
	text-align: center;
}
</style>
</head>
<body class="body_top">
<strong>De Identification</strong>
<form name="De Identification1" id="De Identification1" method="post"><br />
	<?php

	$query = "SELECT count(*) as count FROM metadata_de_identification";
	$res = sqlStatement($query);
	if ($row = sqlFetchArray($res))
	{
		$no_of_items = addslashes($row['count']);
		if($no_of_items == 0)
		{
			$cmd="cp ".$GLOBALS['webserver_root']."/sql/metadata_de_identification.txt ".$GLOBALS['temporary_files_dir']."/metadata_de_identification.txt";
			$output3=shell_exec($cmd);
			$query = "LOAD DATA INFILE '".$GLOBALS['temporary_files_dir']."/metadata_de_identification.txt' INTO TABLE metadata_de_identification FIELDS TERMINATED BY ','  LINES TERMINATED BY '\n'";
			$res = sqlStatement($query);
		}
	}
	//create transaction tables
	$query = "call create_transaction_tables()";
	$res = sqlStatement($query);

	//write input to data base
	$query = "delete from param_include_tables";
	$res = sqlStatement($query);

	$query = "insert into param_include_tables values ('$include_tables','$include_unstructured')";
	$res = sqlStatement($query);

	$query = "delete from param_filter_pid";
	$res = sqlStatement($query);

	$query = "insert into param_filter_pid values ('$begin_date', '$end_date', '$diagnosis_text', '$drug_text', '$immunization_text')";
	$res = sqlStatement($query);

	//process running
	$query = "update de_identification_status set status = 1";
	$res = sqlStatement($query);

	try
	{
		//call procedure - execute in background
		$sh_cmd='./de_identification_procedure.sh '.$sqlconf["host"].' '.$sqlconf["login"].' '.$sqlconf["pass"].' '.$sqlconf["dbase"].' &';
		system ($sh_cmd);


		$query = "SELECT status FROM de_identification_status ";
		$res = sqlStatement($query);
		if ($row = sqlFetchArray($res))
		{
			$de_identification_status = addslashes($row['status']);
			if($de_identification_status == 2 || $de_identification_status == 3)
			{
			 //2 - The De Identification process completed and xls file is ready to download
			 //3 - The De Identification process completed with error
				$query = "SELECT count(*) as count FROM de_identified_data ";
				$res = sqlStatement($query);
				if ($row = sqlFetchArray($res))
				{
					$no_of_items = addslashes($row['count']);
					if($no_of_items <= 1)
					{
						?>
	<table>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	</table>
	<table class="de_identification_status_message" align="center">
	<tr valign="top">

		<td>&nbsp;</td>
		<td rowspan="3"><br>
		<?php echo xl('No Patient record found for given Selection criteria');
		echo "</br></br>";
		echo xl('Please start new De Identification process');
		echo "</br>";	?> </br>
		</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	</table>
	<table align="center">
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	</table>

		<?php
					}
					else
					{	//delete old de_identified_data.xls file
						$timestamp=0;
						$query = "select now() as timestamp";
						$res = sqlStatement($query);
						if ($row = sqlFetchArray($res))
						{
							$timestamp = addslashes($row['timestamp']);
						}
						$timestamp = str_replace(" ","_",$timestamp);
						$de_identified_file = $GLOBALS['temporary_files_dir']."/de_identified_data".$timestamp.".xls";
						$query = "update de_identification_status set last_available_de_identified_data_file = '" . $de_identified_file . "'";
						$res = sqlStatement($query);
						$query = "select * from de_identified_data into outfile '$de_identified_file' ";
						$res = sqlStatement($query);
						?>
	<table>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	</table>
	<table class="de_identification_status_message" align="center">
	<tr valign="top">
		<td>&nbsp;</td>
		<td rowspan="3"><br>
		<?php echo xl('De Identification Process is ongoing');
		echo "</br></br>";
		echo xl('Please visit De Identification screen after some time');
		echo "</br>";	?> </br>
		</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	</table>
	<table align="center">
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	</table>
		<?php
					}
				}
			}
		}			
	}
	catch (Exception $e)
	{
		//error status
		$query = "update de_identification_status set status = 3";
		$res = sqlStatement($query);
	}
}
else if($deIdentificationStatus == 2 or $deIdentificationStatus == 3)
{
 //2 - The De Identification process completed and xls file is ready to download
 //3 - The De Identification process completed with error
	$query = "select last_available_de_identified_data_file from de_identification_status";
	$res = sqlStatement($query);
	if ($row = sqlFetchArray($res))
	{
		$filename = addslashes($row['last_available_de_identified_data_file']);
	}
	ob_end_clean();
	if (file_exists($filename)) {

		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.basename($filename));
		header('Content-Transfer-Encoding: binary');
		header('Content-Type: application/vnd.ms-excel;');                 // This should work for IE & Opera
		header("Content-type: application/x-msexcel");                    // This should work for the rest
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($filename));
		ob_clean();
		flush();
		readfile($filename);

	}

	//xls file downloaded complete
	$query = "update de_identification_status set status = 0";
	$res = sqlStatement($query);
}
?>
</body>
</html>

