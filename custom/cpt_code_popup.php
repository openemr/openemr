<?php
// Copyright (C) 2008 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once('../interface/globals.php');
require_once($GLOBALS['srcdir'].'/patient.inc');
require_once($GLOBALS['srcdir'].'/csv_like_join.php');
require_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');
if(file_exists($GLOBALS['srcdir'].'/wmt-v2/diag_favorites.inc')) 
		require_once($GLOBALS['srcdir'].'/wmt-v2/diag_favorites.inc');
require_once('./code_types.inc.php');

use OpenEMR\Core\Header;
use OpenEMR\Common\Acl\AclMain;

$info_msg = '';
$codetype = 'CPT4';
$use_cpt_favorites = true;
if(isset($_REQUEST['codetype'])) $codetype = $_REQUEST['codetype'];
if(isset($_REQUEST['fav'])) {
	if($_REQUEST['fav'] == 'off') $use_cpt_favorites = false;
}
$isDoctor = IsDoctor();
$list_add_allowed = checkSettingMode('wmt::list_popup_add::procedure_category');
if($list_add_allowed) $list_add_allowed = 'true';
if(AclMain::aclCheckCore('admin','super')) $list_add_allowed = 'true';
$allowed_codes = array('CPT4','HCPCS');
$cptfield = '';
$descfield = '';
$feefield = '';
$typefield = '';
$nextfocus = '';
$search_term = '';
$addlfield = '';
if(isset($_GET['thiscpt'])) $cptfield = strip_tags($_GET['thiscpt']);
if(isset($_GET['thisdesc'])) $descfield = strip_tags($_GET['thisdesc']);
if(isset($_GET['thisfee'])) $feefield = strip_tags($_GET['thisfee']);
if(isset($_GET['thistype'])) {
	$typefield = strip_tags($_GET['thistype']);
}
if(isset($_GET['addlcode'])) $addlfield = strip_tags($_GET['addlcode']);
if(isset($_REQUEST['search_term'])) $search_term = $_REQUEST['search_term'];
$form_code_type = $codetype;
if(isset($_POST['form_code_type'])) {
	$form_code_type = $_POST['form_code_type'];
	$codetype = $_POST['form_code_type'];
}

$form_action="cpt_code_popup.php?thiscpt=$cptfield";
if($descfield != '') $form_action .= "&thisdesc=$descfield";
if($feefield != '') $form_action .= "&thisfee=$feefield";
if($nextfocus != '') $form_action .= "&nextfocus=$nextfocus";
if($codetype != '') $form_action .= "&codetype=$codetype";
if($typefield != '') $form_action .= "&thistype=$typefield";
if($addlfield != '') $form_action .= "&addlcode=$addlfield";
$base_action = $form_action;


?>
<html>
<head>

<title><?php xl('Code Finder','e'); ?></title>
<?php Header::setupHeader(['jquery', 'jquery-ui']); ?>

<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>

<style>
td { font-size:10pt; }

div.notification {
  font-size: 0.9em;
  font-weight: bold;
	text-align: center;
	padding: 6px 22px 6px 22px;
	position: fixed;
	border: solid 1px black;
	border-radius: 10px;
	width: 180px;
	z-index: 3000;
	cursor: progress;
	box-shadow: 8px 8px 5px #888888;
}
</style>

<script type="text/javascript">

function selcode(codetype, code, codedesc, fee) {
  if (opener.closed || !opener.set_cpt) {
   alert('The destination form was closed; I cannot act on your selection.');
  } else {
		// alert('Setting CPT [<?php echo $cptfield; ?>] (<?php echo $descfield; ?>) [<?php echo $feefield; ?>] (<?php echo $addlfield; ?>)');
   opener.set_cpt(codetype, code, codedesc, fee, '<?php echo $cptfield; ?>', '<?php echo $descfield; ?>', '<?php echo $feefield; ?>', '<?php echo $typefield; ?>', '<?php echo $addlfield; ?>');
	}
  window.close();
  return false;
}

function hideDiv()
{
	var target = '';
	if(arguments.length > 0) target = arguments[0];
	if(target != '') {
		var div = document.getElementById(target);
		if(div != null) div.style.display = 'none';
	}
	return true;
}

function delayedHideDiv()
{
	var target = 'save-notification';
	var pause = 1500;
	if(arguments.length > 0) target = arguments[0];
	if(arguments.length > 1) pause = arguments[1];
	window.setTimeout("hideDiv('"+target+"')", pause);
	return true;
}

<?php if($use_cpt_favorites) {
include_once($GLOBALS['srcdir'].'/wmt-v2/init_ajax.inc.js'); 
?>

function ajaxAddDiagFavorite(grp) {
	var div = document.getElementById('save-notification');
	var type = document.forms[0].elements['tmp_type'].value;
	var code = document.forms[0].elements['tmp_code'].value;
	var btn = document.forms[0].elements['tmp_btn'].value;
	if(div != null) div.style.display = 'block';
	var output = 'error';
	$.ajax({
		type: "POST",
		url: "<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/diag_favorites.ajax.php",
		datatype: "html",
		data: {
			type: type,
			code: code, 
			group: grp
		},
		success: function(result) {
			if(result['error']) {
				output = '';
				alert('There was a problem saving the favorite\n'+result['error']);
			} else {
				var div = document.getElementById(btn);
				if(div != null) div.style.display = 'none';
				output = result;
			}
		},
		async: false
	});
	return output;
}

function set_item(grp,title) {
	document.forms[0].elements['tmp_grp'].value = grp;
	ajaxAddDiagFavorite(grp); 
	delayedHideDiv();
}

function popGroupSelection(type, code, btn) {
	document.forms[0].elements['tmp_type'].value = type;
	document.forms[0].elements['tmp_code'].value = code;
	document.forms[0].elements['tmp_btn'].value = btn;

	var linkref = '<?php echo $GLOBALS['webroot']; ?>/custom/add_list_entry_popup.php?thisList=Procedure_Categories&choose=true&lbl_type=Category&add=<?php echo $list_add_allowed; ?>&prompt=a Category';
	wmtOpen(linkref, '_blank', 400, 350);
}

<?php } ?>

</script>
</head>

<div id="save-notification" class="notification wmtColorMenu" style="left: 45%; top: 40%; z-index: 850; display: none; ">Saved to Favorites....</div>
<body class="body_top" onLoad='document.forms[0].elements["search_term"].focus();'>
<form method="post" name="theform" action="<?php echo $base_action?>">
<input type="hidden" tabindex="-1" name="tmp_grp" id="tmp_grp" value="">
<input type="hidden" tabindex="-1" name="tmp_type" id="tmp_type" value="">
<input type="hidden" tabindex="-1" name="tmp_code" id="tmp_code" value="">
<input type="hidden" tabindex="-1" name="tmp_btn" id="tmp_btn" value="">
<?php if(isset($_REQUEST['fav'])) { ?>
<input type="hidden" tabindex="-1" name="fav" id="fav" value="<?php echo $_REQUEST['fav']; ?>">
<?php } ?>
<center>

<table border='0' cellpadding='5' cellspacing='0'>

 <tr> <td height="1">&nbsp;</td> </tr>

 <tr bgcolor='#ddddff'>
  <td>
		<?php
		if (isset($allowed_codes)) {
			if (count($allowed_codes) === 1) {
		  echo "<input type='text' name='form_code_type' value='$codetype' size='5' readonly>\n";
			} else {
		?>
		   <select name='form_code_type' class="form-control">
		<?php
				foreach ($allowed_codes as $code) {
					$value = htmlspecialchars($code, ENT_QUOTES);
					// echo "Code:  ($code)  And Type [$form_code_type]<br>\n";
					$selected_attr = ($form_code_type == $code) ? " selected='selected'" : '';
					$text = htmlspecialchars($code, ENT_NOQUOTES);
		?>
		   	<option value='<?php echo $value ?>'<?php echo $selected_attr?>><?php echo $text ?></option>
		<?php
				}
		?>
		   </select>
	</td>
<?php
	}
} else {
  echo "   <select name='form_code_type'>\n";
  foreach ($code_types as $key => $value) {
    echo "    <option value='$key'";
    if ($codetype == $key || $form_code_type == $key) echo " selected";
    echo ">$key</option>\n";
  }
  echo "    <option value='PROD'";
  if ($codetype == 'PROD' || $form_code_type == 'PROD') echo " selected";
  echo ">Product</option>\n";
  echo "   </select>&nbsp;&nbsp;\n";
}
?>
 	<td>
 		<div>
	 		<label class="d-inline-block"><b><?php xl('Search for:','e'); ?></b></label>
	 		<input type='text' class="form-control d-inline-block" name='search_term' id='search_term' size='12' value='<?php echo $search_term; ?>' title='<?php xl('Any part of the desired code or its description','e'); ?>' style='max-width: 145px;' />
 		</div>
 	</td>
 	<td>
   	<input type='submit' class="btn btn-primary mr-1" name='bn_search' value='<?php xl('Search','e'); ?>' />
   	<input type='button' class="btn btn-primary" value='<?php xl('Erase','e'); ?>' onclick="selcode('', '', '', '', '', '', '', '', '<?php echo $typefield; ?>')" />
  </td>
 </tr>

 <tr>
  <td height="1">
  </td>
 </tr>

</table>

<?php if (isset($_REQUEST['bn_search'])) { ?>

<table border='0'>
 <tr>
	<td>&nbsp;</td>
  <td colspan="2"><b><?php xl ('Code','e'); ?></b></td>
	<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
  <td colspan="2"><b><?php xl ('Description','e'); ?></b></td>
	<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
  <td><b><?php xl ('Fee','e'); ?></b></td>
 </tr>
<?php
	// $res = code_set_search($form_code_type,$search_term);
	$search = strtoupper($search_term);
	$words = explode(' ', $search);
	$frow = sqlQuery("SELECT * FROM code_types WHERE ct_key=?",array($codetype));
	$code_type_id = $frow{'ct_id'};
	$query = "SELECT codes.*, pr_price FROM codes ".
		"LEFT JOIN prices ON (id = pr_id) ".
		"WHERE codes.active = 1 AND ".
		"(pr_level = 'standard' OR pr_level = '' OR pr_level IS NULL) AND ";
	$query .= "code_type = $code_type_id AND ";
	$query .= "(code LIKE '%".$search."%' "; 
	if (!is_numeric($search)) {
		$short = $long = '';
		foreach($words as $word) {
			if($short) $short .= ' AND ';
			$short .= "code_text_short LIKE '%".$word."%' ";
			if($long) $long.= ' AND ';
			$long.= "code_text LIKE '%".$word."%' ";
		}
		$query .= "OR ($short) OR ($long)";
	}
	$query .= ") ORDER BY code";
	$res = sqlStatement($query);

  while ($row = sqlFetchArray($res)) {
    $itercode = htmlspecialchars($row['code'],ENT_QUOTES,'UTF-8',false);
    $raw_code = $row['code'];
    $itertext = htmlspecialchars(ucfirst(strtolower(trim($row['code_text']))),ENT_QUOTES,'UTF-8',false);
		$title = $itertext;
		$fee = sprintf("%01.2f", $row['pr_price']);
		if(strlen($itertext) > 90) $itertext = substr($itertext,0,90).'...';
		if(strlen($title) > 30) $itertext = substr($itertext,0,28).'...';
		if($row['code_text_short'] != '') $title = htmlspecialchars(ucfirst(strtolower($row['code_text_short'])),ENT_QUOTES,'',FALSE); 
    $anchor = "<a href='' title='$title' onclick='return ".
				"selcode(\"$form_code_type\", \"$itercode\", \"$title\", \"$fee\")'>";

    echo " <tr>";
    echo "   <td>";
		if($use_cpt_favorites && $isDoctor) {
			$sql = "SELECT id FROM wmt_diag_fav ".
				"WHERE code_type=? AND code=? ".
				"AND list_user=? ORDER BY seq DESC LIMIT 1";
			$binds = array($form_code_type, $row['code'], 
				$_SESSION['authUser']); 
			$dup = sqlQuery($sql,$binds);
			if(!isset($dup{'id'})) $dup{'id'} = 0;
			if(!$dup['id']) echo "<div style='padding: 0px 8px 0px 0px; margin: 2px;' id='btn_$raw_code'><a href='javascript:;' class='css_button_small' onclick=\"popGroupSelection('$form_code_type','".$row['code']."','btn_$raw_code');\"><span>Add&nbsp;Favorite</span></a></div>";
		} else echo '&nbsp;';
		echo "  </td>\n";

    echo "  <td colspan='2'>$anchor$itercode</a></td>\n";
		echo "	<td>&nbsp;</td>\n";
    echo "  <td colspan='2'>$anchor$itertext</a></td>\n";
		echo "	<td>&nbsp;</td>\n";
    echo "  <td>$fee</td>\n";
    echo " </tr>";
  }
?>
</table>

<?php } else if($use_cpt_favorites) { ?>
<table border='0'>
 <tr>
	<td colspan="4" style="text-align: center;"><i>Currently displaying favorites</i></td>
 </tr>
<?php
	$fav = getAllDiagFavorites($form_code_type);
	$last_cat = '~|~|';
	$frow = sqlQuery("SELECT * FROM code_types WHERE ct_key=?",array($codetype));
	$code_type_id = $frow{'ct_id'};
	$fee_sql = 'SELECT pr_price FROM codes LEFT JOIN prices ON (id = pr_id) '.
		'WHERE code_type = ? AND code = ? AND pr_level = "standard"';
	if(count($fav) > 0) {
?>
 <tr>
  <td colspan="2"><b><?php xl ('Code','e'); ?></b></td>
	<td>&nbsp;</td>
  <td><b><?php xl ('Description','e'); ?></b></td>
 </tr>
<?php
		foreach($fav as $xrow) {
			if($xrow['code'] == '') continue;
			if($xrow['grp'] != $last_cat) {
				$cat = ListLook($xrow['grp'], 'Procedure_Categories');
				$last_cat = $xrow['grp'];
				if($last_cat == '') $cat = 'No Category Assigned';
				echo "<tr><td colspan='4'><b><i>$cat</i></b></td></tr>\n";
 			}
			$fee = sprintf("%01.2f", $xrow['pr_price']);
			// FIX!! Add the code to look up the fee here - 
 			$xcode = htmlspecialchars($xrow['code'],ENT_QUOTES,'UTF-8',false);
 			$xtext = htmlspecialchars(ucfirst(strtolower(trim($xrow['title']))),ENT_QUOTES,'UTF-8',false);
			$title = $xtext;
			if(strlen($xtext) > 80) $xtext = substr($xtext,0,80).'...';
 			$anchor = "<a href='' title='$title'  onclick='return ".
					"selcode(\"$form_code_type\", \"$xcode\", \"$title\", \"$fee\")'>";
			echo "<tr>\n";
			echo "<td style='text-align: right; width: 20px;'>&#9733;</td>\n";
			echo "<td style='text-align: right;'>$anchor$xcode</a></td>\n";
			echo "<td style='width: 10px;'>&nbsp;</td>\n";
			echo "<td>$anchor$xtext</a></td>\n";
			echo "</tr>\n";
		}
	} else {
		echo "<tr><td>&nbsp;</td></tr>\n";
		echo "<tr><td colspan='4' style='text-align: center;'><b><i>No favorites are currently defined -<br>They can be defined in the Procedure Favorites heading under Misc</i></b></td></tr>\n";
	}
?>
</table>
<?php } ?>

</center>
</form>
</body>
<script src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtpopup.js" type="text/javascript"></script>
</script>
</html>
