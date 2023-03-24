<?php
/**
 * Administrative loader for lab compendium data.
 *
 * Supports loading of lab order codes and related order entry questions from CSV
 * format into the procedure_order and procedure_questions tables, respectively.
 *
 * Copyright (C) 2012 Rod Roark <rod@sunsetsystems.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://opensource.org/licenses/gpl-license.php>.
 *
 * @package   OpenEMR
 * @author    Rod Roark <rod@sunsetsystems.com>
 * 
 * Adapted for use with the dedicated laboratory interfaces developed
 * for Williams Medical Technologies, Inc.
 * 
 * @since		2014-06-15
 * @author		Ron Criswell <ron.criswell@MDTechSvcs.com>
 */

set_time_limit(0);
ini_set('max_execution_time', 0);
ini_set('post_max_size', '64M');
ini_set('upload_max_filesize', '64M');
ini_set('session.gc_maxlifetime', 7200);

$sanitize_all_escapes  = true;
$fake_register_globals = false;

/* Turn off output buffering */
ini_set('output_buffering', 'off');
// Turn off PHP output compression
ini_set('zlib.output_compression', false);
// Implicitly flush the buffer(s)
ini_set('implicit_flush', true);
ob_implicit_flush(true);
// Disable apache output buffering/compression
if (function_exists('apache_setenv')) {
	apache_setenv('no-gzip', '1');
	apache_setenv('dont-vary', '1');
}

session_set_cookie_params(7200);

require_once("../globals.php");

use OpenEMR\Common\Acl\AclMain;

// This array is an important reference for the supported labs and their NPI
// numbers as known to this program.  The clinic must define at least one
// address book entry for a lab that has a supported NPI number.
//
$lab_npi = array(
		'BIOREF' 	 => 'BioReference Laboratories',
		'BBPL' 		 => 'Boyce and Bynum Pathology',
		'CERNER' 	 => 'Cerner-Tuality Laboratory',
		'CPL' 		 => 'Clinical Pathology Laboratories',
		'INTERPATH'  => 'Interpath Laboratory',
		'LABCORP' 	 => 'LabCorp Laboratory',
		'LABTRAK' 	 => 'Labtrak Laboratory',
		'PATHGROUP'  => 'Pathgroup Labs',
		'SHIEL' 	 => 'Shiel Medical Labs',
		'SUNRISE' 	 => 'Sunrise Medical Labs',
		'QUEST' 	 => 'Quest Diagnostics',
		'MERCY' 	 => 'Mercy Diagnostics',
		'PROGENITY'	 => 'Progenity Laboratory',
		'NATERA'	 => 'Natera Laboratory',
		'COMPUNET'	 => 'CompuNet Laboratory',
		'NWMED'		 => 'Northwestern Regional'
);

/**
 * Get lab's ID from the users table given its NPI.  If none return 0.
 *
 * @param  string  $npi           The lab's NPI number as known to the system
 * @return integer                The numeric value of the lab's address book entry
 */
$lab_id = false;
function getLabID($id) {
	$lrow = sqlQuery("SELECT lab_id FROM procedure_type WHERE procedure_type_id = ? LIMIT 1", array($id));
	if (empty($lrow['lab_id'])) return false;
	return $lrow['lab_id'];
}

function getActive($npi) {
	$lrow = sqlQuery("SELECT ppid FROM procedure_providers WHERE npi = ? ORDER BY ppid LIMIT 1", array($npi));
	if (empty($lrow['ppid'])) return false;
	return true;
}

if (!AclMain::aclCheckCore('admin', 'super')) die(xlt('Not authorized','','','!'));

$form_step   = isset($_POST['form_step']) ? trim($_POST['form_step']) : '0';
$form_status = isset($_POST['form_status' ]) ? trim($_POST['form_status' ]) : '';

if (!empty($_POST['form_import'])) $form_step = 1;

// When true the current form will submit itself after a brief pause.
$auto_continue = false;

?>
<html>

<head>
	<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
	<title><?php echo xlt('Load Lab Configuration'); ?></title>
</head>

<body class="body_top">
		&nbsp;<br />
		<form method='post' action='load_compendium.php'
			enctype='multipart/form-data'>

			<table>

<?php
	if ($form_step == 0) {
		echo " <tr>\n";
		echo "  <td style='width:5%;text-align:right' nowrap>" . xlt('Vendor') . "</td>\n";
		echo "  <td><select name='vendor'>";
		foreach ($lab_npi as $key => $value) {
			echo "<option value='" . attr($key) . "'";
			if (!getActive($key)) {
				// Entries with no matching address book entry will be disabled.
				echo " disabled";
			}
			echo ">" . text($key) . ": " . text($value) . "</option>";
		}
		echo "</td>\n";
		echo " </tr>\n";

		echo " <tr>\n";
		echo "  <td style='text-align:right' nowrap>" . xlt('Action') . "</td>\n";
		echo "  <td><select name='action'>";
		echo "<option value='1'>" . xlt('Load Order Definitions'    ) . "</option>";
		echo "<option value='4'>" . xlt('Load Profile Definitions'    ) . "</option>";
		echo "<option value='2'>" . xlt('Load Order Entry Questions') . "</option>";
		echo "<option value='3'>" . xlt('Load Question Options'  ) . "</option>";
		echo "</td>\n";
		echo " </tr>\n";

		echo " <tr>\n";
		echo "  <td nowrap style='text-align:right'>" . xlt('Lab Name') . "</td>\n";
		echo "  <td><select name='group'>";
		$gres = sqlStatement("SELECT procedure_type_id, name FROM procedure_type " .
			"WHERE procedure_type = 'grp' AND parent = 0 ORDER BY name, procedure_type_id");
		while ($grow = sqlFetchArray($gres)) {
			echo "<option value='" . attr($grow['procedure_type_id']) . "'>" .
					text($grow['name']) . "</option>";
		}
		echo "</td>\n";
		echo " </tr>\n";

		echo " <tr>\n";
		echo "  <td nowrap style='text-align:right'>" . xlt('Parameter File') . "</td>\n";
		echo "<td><input type='hidden' name='MAX_FILE_SIZE' value='30000000' />";
		echo "<input type='file' name='userfile' /></td>\n";
		echo " </tr>\n";

		echo " <tr>\n";
		echo "  <td nowrap>&nbsp;</td>\n";
		echo "  <td><input type='submit' value='" . xla('Submit') . "' /></td>\n";
		echo " </tr>\n";
	}

	echo " <tr>\n";
	echo "  <td colspan='2'>\n";

	if ($form_step == 1) {
		// Process uploaded config file.
		if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
			$form_vendor = $_POST['vendor'];
			$form_action = intval($_POST['action']);
			$form_group  = intval($_POST['group']);
			$lab_id = getLabID($form_group);

			$form_status .= xlt('Applying') . "...<br />";
			echo nl2br($form_status);

			$fhcsv = fopen($_FILES['userfile']['tmp_name'], "r");

			if ($fhcsv) {
				// load the correct vendor import module
				switch ($form_vendor) {
					case '1235186800':
					case 'PATHGROUP':
						require_once('pathgroup.inc.php');
						break;
						
					case '1598760985':
					case 'YOSEMITE':
						require_once('yosemite.inc.php');
						break;
						
					case '1194769497':
					case 'CPL':
						require_once('cpl.inc.php');
						break;
						
					case '1548208440':
					case 'INTERPATH':
						require_once('interpath.inc.php');
						break;
						
					case '1134277494':
					case 'BIOREF':
						require_once('bioref.inc.php');
						break;
						
					case 'QUEST':
						require_once('quest.inc.php');
						break;
						
					case 'LABCORP':
						require_once('labcorp.inc.php');
						break;
						
					case 'CERNER':
						require_once('cerner.inc.php');
						break;
						
					case 'SHIEL':
						require_once('shiel.inc.php');
						break;
						
					case 'SUNRISE':
						require_once('sunrise.inc.php');
						break;
						
					case 'LABTRAK':
						require_once('labtrak.inc.php');
						break;
						
					case 'BBPL':
						require_once('boycebynum.inc.php');
						break;
						
					case 'MERCY':
						require_once('mercy.inc.php');
						break;
						
					case 'PROGENITY':
						require_once('progenity.inc.php');
						break;
						
					case 'NATERA':
						require_once('natera.inc.php');
						break;
						
					case 'COMPUNET':
						require_once('compunet.inc.php');
						break;
						
					case 'NWMED':
						require_once('nwmed.inc.php');
						break;
						
					default:
						echo xlt('No import module available for this lab!');
						$form_step = -1;
												
				}
				
				// end of import processing
				fclose($fhcsv);
			}
			else {
				echo xlt('Internal error accessing uploaded file!');
				$form_step = -1;
			}
		}
		else {
			echo xlt('Upload failed!');
			$form_step = -1;
		}
		$auto_continue = true;
	}

	if ($form_step == 2) {
		$form_status = xlt('Done') . ".";
		echo nl2br($form_status);
	}

	++$form_step;
?>

					</td>
				</tr>
			</table>

			<input type='hidden' name='form_step'
				value='<?php echo attr($form_step); ?>' /> <input type='hidden'
				name='form_status' value='<?php echo $form_status; ?>' />

		</form>

<?php if ($auto_continue) { ?>
	<script>
		setTimeout("document.forms[0].submit();", 3000);
	</script>
<?php } ?>

</body>
</html>

