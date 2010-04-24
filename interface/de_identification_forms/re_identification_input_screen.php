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
?>
<html>
<head>
<title><?php xl('Re Identification','e'); ?></title>
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
<script language="JavaScript">
function form_validate()
{
 
 if(document.forms[0].re_id_code.value == "undefined" || document.forms[0].re_id_code.value == "")
 { 
  alert("<?php echo xl('Enter the Re Identification code');?>");
  return false;
 }
 top.restoreSession();
 return true;
}

function download_file()
{
 alert("<?php echo xl('Re-identification files will be saved in'); echo ' `'.$GLOBALS['temporary_files_dir'].'` '; echo xl('location of the openemr machine and may contain sensitive data, so it is recommended to manually delete the files after its use');?>");
 document.re_identification.submit();
}

</script>
</head>
<body class="body_top">
<strong><?php xl('Re Identification','e');  ?></strong>
<div id="overDiv"
	style="position: absolute; visibility: hidden; z-index: 1000;"></div>
<form name="re_identification" enctype="Re_identification_ip_single_code"
	action="re_identification_op_single_patient.php" method="POST" onsubmit="return form_validate();"><?php 
 $row = sqlQuery("SHOW TABLES LIKE 'de_identification_status'");
 if (empty($row))
 {
  ?>
   <table>  <tr> 	<td>&nbsp;</td> <td>&nbsp;</td> </tr>
	      <tr>  <td>&nbsp;</td> <td>&nbsp;</td> </tr> 
 </table>
 <table class="de_identification_status_message" align="center" >
	<tr valign="top">
		<td>&nbsp;</td>
		<td rowspan="3">
		<br>
        <?php echo xl('Please upgrade OpenEMR Database to include De Identification procedures, function, tables'); ?>
	</br></br><a  target="Blank" href="../../contrib/util/de_identification_upgrade.php"><?php echo xl('Click here');?></a>
	<?php echo xl('to run'); 
    	echo " de_identification_upgrade.php</br>";?><br>
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
  <?php  
  }
 else {
	$query = "select status from re_identification_status";
	$res = sqlStatement($query);
	if ($row = sqlFetchArray($res))
	{
		$reIdentificationStatus = addslashes($row['status']);
	/* $reIdentificationStatus:
	*  0 - There is no Re Identification in progress. (start new Re Identification process)
	*  1 - A Re Identification process is currently in progress.
	*  2 - The Re Identification process completed and xls file is ready to download
	*/  

	}
	if($reIdentificationStatus == 1)
	{
	 //1 - A Re Identification process is currently in progress
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
		<?php echo xl('Re Identification Process is ongoing');
		echo "</br></br>";
		echo xl('Please visit Re Identification screen after some time');
		echo "</br>";	?> <br>
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
		<?php
	}
	else if($reIdentificationStatus == 0)
	{
	  //0 - There is no Re Identification in progress. (start new Re Identification process)
		?>
	<center></br>
	</br>
		<?php xl('Enter the Re Identification code','e'); ?> <input
	type='text' size='50' name='re_id_code' id='re_id_code'
	title='<?php xl('Enter the Re Identification code','e'); ?>' /> </br>
	</br>
	<Input type="Submit" Name="Submit" Value=<?php echo xl("submit");?>></center>
		<?php
	}
	else if($reIdentificationStatus == 2)
	{
	 //2 - The Re Identification process completed and xls file is ready to download
		$query = "SELECT count(*) as count FROM re_identified_data ";
		$res = sqlStatement($query);
		if ($row = sqlFetchArray($res))
		{
			$no_of_items = addslashes($row['count']);
		}
		if($no_of_items <= 1)
		{
			//start new search - no patient record fount
			$query = "update re_identification_status set status = 0";
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
		<?php echo xl('No Patient record found for the given Re Identification code');
		echo "</br></br>";
		echo xl('Please enter the correct Re Identification code');
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
		else {
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
	<table class="de_identification_status_message"" align="center">
	<tr valign="top">
		<td>&nbsp;</td>
		<td rowspan="3"><br>
		<?php echo xl('Re Identification Process is completed');
		echo "</br></br>";
		echo xl('Please Click download button to download the Re Identified data');
		echo "</br>";	?> <br>
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
	<tr>
		<td colspan="2" class="style1"><input type="button" name="Download"
			value=<?php echo xl("Download"); ?> onclick="download_file()" ></td>
	</tr>
	</table>
		<?php
		}
	}
      }
    
	?>
</form>
</body>
</html>

