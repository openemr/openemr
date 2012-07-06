<?php
// Copyright (C) 2007-2011 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/formdata.inc.php");
require_once("../../custom/code_types.inc.php");

$list_id = empty($_REQUEST['list_id']) ? 'language' : $_REQUEST['list_id'];

// Check authorization.
$thisauth = acl_check('admin', 'super');
if (!$thisauth) die(xl('Not authorized'));

// If we are saving, then save.
//
if ($_POST['formaction']=='save' && $list_id) {
    $opt = $_POST['opt'];
    if ($list_id == 'feesheet') {
        // special case for the feesheet list
        sqlStatement("DELETE FROM fee_sheet_options");
        for ($lino = 1; isset($opt["$lino"]['category']); ++$lino) {
            $iter = $opt["$lino"];
            $category = formTrim($iter['category']);
            $option   = formTrim($iter['option']);
            $codes    = formTrim($iter['codes']);
            if (strlen($category) > 0 && strlen($option) > 0) {
                sqlInsert("INSERT INTO fee_sheet_options ( " .
                            "fs_category, fs_option, fs_codes " .
                            ") VALUES ( "   .
                            "'$category', " .
                            "'$option', "   .
                            "'$codes' "     .
                        ")");
            }
        }
    }
    else if ($list_id == 'code_types') {
      // special case for code types
      sqlStatement("DELETE FROM code_types");
      for ($lino = 1; isset($opt["$lino"]['ct_key']); ++$lino) {
        $iter = $opt["$lino"];
        $ct_key  = formTrim($iter['ct_key']);
        $ct_id   = formTrim($iter['ct_id']) + 0;
        $ct_seq  = formTrim($iter['ct_seq']) + 0;
        $ct_mod  = formTrim($iter['ct_mod']) + 0;
        $ct_just = formTrim($iter['ct_just']);
        $ct_mask = formTrim($iter['ct_mask']);
        $ct_fee  = empty($iter['ct_fee' ]) ? 0 : 1;
        $ct_rel  = empty($iter['ct_rel' ]) ? 0 : 1;
        $ct_nofs = empty($iter['ct_nofs']) ? 0 : 1;
        $ct_diag = empty($iter['ct_diag']) ? 0 : 1;
        $ct_active = empty($iter['ct_active' ]) ? 0 : 1;
        $ct_label = formTrim($iter['ct_label']);
        $ct_external = formTrim($iter['ct_external']) + 0;
        $ct_claim = empty($iter['ct_claim']) ? 0 : 1;
        if (strlen($ct_key) > 0 && $ct_id > 0) {
          sqlInsert("INSERT INTO code_types ( " .
            "ct_key, ct_id, ct_seq, ct_mod, ct_just, ct_mask, ct_fee, ct_rel, ct_nofs, ct_diag, ct_active, ct_label, ct_external, ct_claim " .
            ") VALUES ( "   .
            "'$ct_key' , " .
            "'$ct_id'  , " .
            "'$ct_seq' , " .
            "'$ct_mod' , " .
            "'$ct_just', " .
            "'$ct_mask', " .
            "'$ct_fee' , " .
            "'$ct_rel' , " .
            "'$ct_nofs', " .
            "'$ct_diag', " .
            "'$ct_active', " .
            "'$ct_label', " .
            "'$ct_external', " .
            "'$ct_claim' " .
            ")");
        }
      }
    }
    else {
        // all other lists
        //
        // erase lists options and recreate them from the submitted form data
        sqlStatement("DELETE FROM list_options WHERE list_id = '$list_id'");
        for ($lino = 1; isset($opt["$lino"]['id']); ++$lino) {
            $iter = $opt["$lino"];
            $value = empty($iter['value']) ? 0 : (formTrim($iter['value']) + 0);
            $id = formTrim($iter['id']);
            if (strlen($id) > 0) {

              // Special processing for the immunizations list
              // Map the entered cvx codes into the immunizations table cvx_code
              sqlStatement ("UPDATE `immunizations` " .
                            "SET `cvx_code`='".$value."' " .
                            "WHERE `immunization_id`='".$id."'");

              // Force List Based Form names to start with LBF.
              if ($list_id == 'lbfnames' && substr($id,0,3) != 'LBF')
                $id = "LBF$id";
              sqlInsert("INSERT INTO list_options ( " .
                "list_id, option_id, title, seq, is_default, option_value, mapping, notes " .
                ") VALUES ( " .
                "'$list_id', "                       .
                "'" . $id                        . "', " .
                "'" . formTrim($iter['title'])   . "', " .
                "'" . formTrim($iter['seq'])     . "', " .
                "'" . formTrim($iter['default']) . "', " .
                "'" . $value                     . "', " .
                "'" . formTrim($iter['mapping']) . "', " .
                "'" . formTrim($iter['notes'])   . "' "  .
                ")");
            }
        }
    }
}
else if ($_POST['formaction']=='addlist') {
    // make a new list ID from the new list name
    $newlistID = $_POST['newlistname'];
    $newlistID = preg_replace("/\W/", "_", $newlistID);

    // determine the position of this new list
    $row = sqlQuery("SELECT max(seq) as maxseq FROM list_options WHERE list_id= 'lists'");

    // add the new list to the list-of-lists
    sqlInsert("INSERT INTO list_options ( " .
                "list_id, option_id, title, seq, is_default, option_value " .
                ") VALUES ( " .
                "'lists',". // the master list-of-lists
                "'".$newlistID."',".
                "'".$_POST['newlistname']."', ".
                "'".($row['maxseq']+1)."',".
                "'1', '0')"
                );
}
else if ($_POST['formaction']=='deletelist') {
    // delete the lists options
    sqlStatement("DELETE FROM list_options WHERE list_id = '".$_POST['list_id']."'");
    // delete the list from the master list-of-lists
    sqlStatement("DELETE FROM list_options WHERE list_id = 'lists' and option_id='".$_POST['list_id']."'");
}

$opt_line_no = 0;

// Given a string of multiple instances of code_type|code|selector,
// make a description for each.
// @TODO Instead should use a function from custom/code_types.inc.php and need to remove casing functions
function getCodeDescriptions($codes) {
  global $code_types;
  $arrcodes = explode('~', $codes);
  $s = '';
  foreach ($arrcodes as $codestring) {
    if ($codestring === '') continue;
    $arrcode = explode('|', $codestring);
    $code_type = $arrcode[0];
    $code      = $arrcode[1];
    $selector  = $arrcode[2];
    $desc = '';
    if ($code_type == 'PROD') {
      $row = sqlQuery("SELECT name FROM drugs WHERE drug_id = '$code' ");
      $desc = "$code:$selector " . $row['name'];
    }
    else {
      $row = sqlQuery("SELECT code_text FROM codes WHERE " .
        "code_type = '" . $code_types[$code_type]['id'] . "' AND " .
        "code = '$code' ORDER BY modifier LIMIT 1");
      $desc = "$code_type:$code " . ucfirst(strtolower($row['code_text']));
    }
    $desc = str_replace('~', ' ', $desc);
    if ($s) $s .= '~';
    $s .= $desc;
  }
  return $s;
}

// Write one option line to the form.
//
function writeOptionLine($option_id, $title, $seq, $default, $value, $mapping='', $notes='') {
  global $opt_line_no, $list_id;
  ++$opt_line_no;
  $bgcolor = "#" . (($opt_line_no & 1) ? "ddddff" : "ffdddd");
  $checked = $default ? " checked" : "";

  echo " <tr bgcolor='$bgcolor'>\n";

  echo "  <td align='center' class='optcell'>";
  echo "<input type='text' name='opt[$opt_line_no][id]' value='" .
       htmlspecialchars($option_id, ENT_QUOTES) . "' size='12' maxlength='63' class='optin' />";
  echo "</td>\n";

  echo "  <td align='center' class='optcell'>";
  echo "<input type='text' name='opt[$opt_line_no][title]' value='" .
       htmlspecialchars($title, ENT_QUOTES) . "' size='20' maxlength='63' class='optin' />";
  echo "</td>\n";

  // if not english and translating lists then show the translation
  if ($GLOBALS['translate_lists'] && $_SESSION['language_choice'] > 1) {
       echo "  <td align='center' class='translation'>" . (htmlspecialchars( xl($title), ENT_QUOTES)) . "</td>\n";
  }
    
  echo "  <td align='center' class='optcell'>";
  echo "<input type='text' name='opt[$opt_line_no][seq]' value='" .
       htmlspecialchars($seq, ENT_QUOTES) . "' size='4' maxlength='10' class='optin' />";
  echo "</td>\n";

  echo "  <td align='center' class='optcell'>";
  echo "<input type='checkbox' name='opt[$opt_line_no][default]' value='1' " .
    "onclick='defClicked($opt_line_no)' class='optin'$checked />";
  echo "</td>\n";

  // Tax rates, contraceptive methods and LBF names have an additional attribute.
  //
  if ($list_id == 'taxrate' || $list_id == 'contrameth' || $list_id == 'lbfnames') {
    echo "  <td align='center' class='optcell'>";
    echo "<input type='text' name='opt[$opt_line_no][value]' value='" .
        htmlspecialchars($value, ENT_QUOTES) . "' size='8' maxlength='15' class='optin' />";
    echo "</td>\n";
  }

  // Adjustment reasons use option_value as a reason category.  This is
  // needed to distinguish between adjustments that change the invoice
  // balance and those that just shift responsibility of payment or
  // are used as comments.
  //
  else if ($list_id == 'adjreason') {
    echo "  <td align='center' class='optcell'>";
    echo "<select name='opt[$opt_line_no][value]' class='optin'>";
    foreach (array(
      1 => xl('Charge adjustment'),
      2 => xl('Coinsurance'),
      3 => xl('Deductible'),
      4 => xl('Other pt resp'),
      5 => xl('Comment'),
    ) as $key => $desc) {
      echo "<option value='$key'";
      if ($key == $value) echo " selected";
      echo ">" . htmlspecialchars($desc) . "</option>";
    }
    echo "</select>";
    echo "</td>\n";
  }

  // Address book categories use option_value to flag category as a
  // person-centric vs company-centric vs indifferent.
  //
  else if ($list_id == 'abook_type') {
    echo "  <td align='center' class='optcell'>";
    echo "<select name='opt[$opt_line_no][value]' class='optin'>";
    foreach (array(
      1 => xl('Unassigned'),
      2 => xl('Person'),
      3 => xl('Company'),
    ) as $key => $desc) {
      echo "<option value='$key'";
      if ($key == $value) echo " selected";
      echo ">" . htmlspecialchars($desc) . "</option>";
    }
    echo "</select>";
    echo "</td>\n";
  }

  // Immunization categories use option_value to map list items
  // to CVX codes.
  //
  else if ($list_id == 'immunizations') {
  echo "  <td align='center' class='optcell'>";
  echo "<input type='text' size='10' name='opt[$opt_line_no][value]' " .
       "value='" . htmlspecialchars($value,ENT_QUOTES) . "' onclick='sel_cvxcode(this)' " .
       "title='" . htmlspecialchars( xl('Click to select or change CVX code'), ENT_QUOTES) . "'/>";
  echo "</td>\n";
  }

  // IPPF includes the ability to map each list item to a "master" identifier.
  // Sports teams use this for some extra info for fitness levels.
  //
  if ($GLOBALS['ippf_specific'] || $list_id == 'fitness') {
    echo "  <td align='center' class='optcell'>";
    echo "<input type='text' name='opt[$opt_line_no][mapping]' value='" .
        htmlspecialchars($mapping, ENT_QUOTES) . "' size='12' maxlength='15' class='optin' />";
    echo "</td>\n";
  }

  echo "  <td align='center' class='optcell'>";
  echo "<input type='text' name='opt[$opt_line_no][notes]' value='" .
      htmlspecialchars($notes, ENT_QUOTES) . "' size='25' maxlength='255' class='optin' />";
  echo "</td>\n";

  echo " </tr>\n";
}

// Write a form line as above but for the special case of the Fee Sheet.
//
function writeFSLine($category, $option, $codes) {
  global $opt_line_no;

  ++$opt_line_no;
  $bgcolor = "#" . (($opt_line_no & 1) ? "ddddff" : "ffdddd");

  $descs = getCodeDescriptions($codes);

  echo " <tr bgcolor='$bgcolor'>\n";

  echo "  <td align='center' class='optcell'>";
  echo "<input type='text' name='opt[$opt_line_no][category]' value='" .
       htmlspecialchars($category, ENT_QUOTES) . "' size='20' maxlength='63' class='optin' />";
  echo "</td>\n";

  echo "  <td align='center' class='optcell'>";
  echo "<input type='text' name='opt[$opt_line_no][option]' value='" .
       htmlspecialchars($option, ENT_QUOTES) . "' size='20' maxlength='63' class='optin' />";
  echo "</td>\n";

  echo "  <td align='left' class='optcell'>";
  echo "   <div id='codelist_$opt_line_no'>";
  if (strlen($descs)) {
    $arrdescs = explode('~', $descs);
    $i = 0;
    foreach ($arrdescs as $desc) {
      echo "<a href='' onclick='return delete_code($opt_line_no,$i)' title='" . xl('Delete') . "'>";
      echo "[x]&nbsp;</a>$desc<br />";
      ++$i;
    }
  }
  echo "</div>";
  echo "<a href='' onclick='return select_code($opt_line_no)'>";
  echo "[" . xl('Add') . "]</a>";

  echo "<input type='hidden' name='opt[$opt_line_no][codes]' value='" .
       htmlspecialchars($codes, ENT_QUOTES) . "' />";
  echo "<input type='hidden' name='opt[$opt_line_no][descs]' value='" .
       htmlspecialchars($descs, ENT_QUOTES) . "' />";
  echo "</td>\n";

  echo " </tr>\n";
}

// Helper functions for writeCTLine():

function ctGenCell($opt_line_no, $ct_array, $name, $size, $maxlength, $title='') {
  $value = isset($ct_array[$name]) ? $ct_array[$name] : '';
  $s = "  <td align='center' class='optcell'";
  if ($title) $s .= " title='" . addslashes($title) . "'";
  $s .= ">";
  $s .= "<input type='text' name='opt[$opt_line_no][$name]' value='";
  $s .= htmlspecialchars($value, ENT_QUOTES);
  $s .= "' size='$size' maxlength='$maxlength' class='optin' />";
  $s .= "</td>\n";
  return $s;
}

function ctGenCbox($opt_line_no, $ct_array, $name, $title='') {
  $checked = empty($ct_array[$name]) ? '' : 'checked ';
  $s = "  <td align='center' class='optcell'";
  if ($title) $s .= " title='" . addslashes($title) . "'";
  $s .= ">";
  $s .= "<input type='checkbox' name='opt[$opt_line_no][$name]' value='1' ";
  $s .= "$checked/>";
  $s .= "</td>\n";
  return $s;
}

// Write a form line as above but for the special case of Code Types.
//
function writeCTLine($ct_array) {
  global $opt_line_no,$cd_external_options;

  ++$opt_line_no;
  $bgcolor = "#" . (($opt_line_no & 1) ? "ddddff" : "ffdddd");

  echo " <tr bgcolor='$bgcolor'>\n";

  echo ctGenCBox($opt_line_no, $ct_array, 'ct_active',
    xl('Is this code type active?'));
  echo ctGenCell($opt_line_no, $ct_array, 'ct_key' , 6, 15,
    xl('Unique human-readable identifier for this type'));
  echo ctGenCell($opt_line_no, $ct_array, 'ct_id'  , 2, 11,
    xl('Unique numeric identifier for this type'));
  echo ctGenCell($opt_line_no, $ct_array, 'ct_label' , 6, 30,
    xl('Label for this type'));
  echo ctGenCell($opt_line_no, $ct_array, 'ct_seq' , 2,  3,
    xl('Numeric display order'));
  echo ctGenCell($opt_line_no, $ct_array, 'ct_mod' , 1,  2,
    xl('Length of modifier, 0 if none'));
  echo ctGenCell($opt_line_no, $ct_array, 'ct_just', 4, 15,
    xl('If billing justification is used enter the name of the diagnosis code type.'));
  echo ctGenCell($opt_line_no, $ct_array, 'ct_mask', 6,  9,
    xl('Specifies formatting for codes. # = digit, @ = alpha, * = any character. Empty if not used.'));
  echo ctGenCBox($opt_line_no, $ct_array, 'ct_claim',
    xl('Is this code type used in claims?'));
  echo ctGenCBox($opt_line_no, $ct_array, 'ct_fee',
    xl('Are fees charged for this type?'));
  echo ctGenCBox($opt_line_no, $ct_array, 'ct_rel',
    xl('Does this type allow related codes?'));
  echo ctGenCBox($opt_line_no, $ct_array, 'ct_nofs',
    xl('Is this type hidden in the fee sheet?'));
  echo ctGenCBox($opt_line_no, $ct_array, 'ct_diag',
    xl('Is this a diagnosis type?'));
  // Show the external code types selector
  $value_ct_external = isset($ct_array['ct_external']) ? $ct_array['ct_external'] : '';
  echo "  <td title='" . xla('Is this using external sql tables? If it is, then choose the format.') . "' align='center' class='optcell'>";
  echo "<select name='opt[$opt_line_no][ct_external]' class='optin'>";
  foreach ( $cd_external_options as $key => $desc) {
    echo "<option value='" . attr($key) . "'";
    if ($key == $value_ct_external) echo " selected";
    echo ">" . text($desc) . "</option>";
  }
  echo "</select>";
  echo "</td>\n";
  echo " </tr>\n";
}
?>
<html>

<head>
<?php html_header_show();?>

<!-- supporting javascript code -->
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.js"></script>

<link rel="stylesheet" href='<?php  echo $css_header ?>' type='text/css'>
<title><?php  xl('List Editor','e'); ?></title>

<style>
tr.head   { font-size:10pt; background-color:#cccccc; text-align:center; }
tr.detail { font-size:10pt; }
td        { font-size:10pt; }
input     { font-size:10pt; }
a, a:visited, a:hover { color:#0000cc; }
.optcell  { }
.optin    { background-color:transparent; }
.help     { cursor:help; }
.translation { color:green; }
</style>

<script type="text/javascript" src="../../library/dialog.js"></script>

<script language="JavaScript">

var current_lino = 0;

// Helper function to set the contents of a div.
// This is for Fee Sheet administration.
function setDivContent(id, content) {
 if (document.getElementById) {
  var x = document.getElementById(id);
  x.innerHTML = '';
  x.innerHTML = content;
 }
 else if (document.all) {
  var x = document.all[id];
  x.innerHTML = content;
 }
}

// Given a line number, redisplay its descriptive list of codes.
// This is for Fee Sheet administration.
function displayCodes(lino) {
 var f = document.forms[0];
 var s = '';
 var descs = f['opt[' + lino + '][descs]'].value;
 if (descs.length) {
  var arrdescs = descs.split('~');
  for (var i = 0; i < arrdescs.length; ++i) {
   s += "<a href='' onclick='return delete_code(" + lino + "," + i + ")' title='<?php xl('Delete','e'); ?>'>";
   s += "[x]&nbsp;</a>" + arrdescs[i] + "<br />";
  }
 }
 setDivContent('codelist_' + lino, s);
}

// Helper function to remove a Fee Sheet code.
function dc_substring(s, i) {
 var r = '';
 var j = s.indexOf('~', i);
 if (j < 0) { // deleting last segment
  if (i > 0) r = s.substring(0, i-1); // omits trailing ~
 }
 else { // not last segment
  r = s.substring(0, i) + s.substring(j + 1);
 }
 return r;
}

// Remove a generated Fee Sheet code.
function delete_code(lino, seqno) {
 var f = document.forms[0];
 var celem = f['opt[' + lino + '][codes]'];
 var delem = f['opt[' + lino + '][descs]'];
 var ci = 0;
 var di = 0;
 for (var i = 0; i < seqno; ++i) {
  ci = celem.value.indexOf('~', ci) + 1;
  di = delem.value.indexOf('~', di) + 1;
 }
 celem.value = dc_substring(celem.value, ci);
 delem.value = dc_substring(delem.value, di);
 displayCodes(lino);
 return false;
}

// This invokes the find-code popup.
// For Fee Sheet administration.
function select_code(lino) {
 current_lino = lino;
 dlgopen('../patient_file/encounter/find_code_popup.php', '_blank', 700, 400);
 return false;
}

// This is for callback by the find-code popup.
function set_related(codetype, code, selector, codedesc) {
 if (typeof(current_sel_name) == 'undefined')
 {
 // Coming from Fee Sheet edit
  var f = document.forms[0];
  var celem = f['opt[' + current_lino + '][codes]'];
  var delem = f['opt[' + current_lino + '][descs]'];
  var i = 0;
  while ((i = codedesc.indexOf('~')) >= 0) {
   codedesc = codedesc.substring(0, i) + ' ' + codedesc.substring(i+1);
  }
  if (code) {
   if (celem.value) {
    celem.value += '~';
    delem.value += '~';
   }
   celem.value += codetype + '|' + code + '|' + selector;
   if (codetype == 'PROD') delem.value += code + ':' + selector + ' ' + codedesc;
   else delem.value += codetype + ':' + code + ' ' + codedesc;
  } else {
   celem.value = '';
   delem.value = '';
  }
  displayCodes(current_lino);
 }
 else
 {
  // Coming from Immunizations edit
     var f = document.forms[0][current_sel_name];
     var s = f.value;
     if (code) {
         s = code;
     }
     else {
         s = '0';
     }
     f.value = s;
 }
}

// Called when a "default" checkbox is clicked.  Clears all the others.
function defClicked(lino) {
 var f = document.forms[0];
 for (var i = 1; f['opt[' + i + '][default]']; ++i) {
  if (i != lino) f['opt[' + i + '][default]'].checked = false;
 }
}

// Form validation and submission.
// This needs more validation.
function mysubmit() {
 var f = document.forms[0];
 if (f.list_id.value == 'code_types') {
  for (var i = 1; f['opt[' + i + '][ct_key]'].value; ++i) {
   var ikey = 'opt[' + i + ']';
   for (var j = i+1; f['opt[' + j + '][ct_key]'].value; ++j) {
    var jkey = 'opt[' + j + ']';
    if (f[ikey+'[ct_key]'].value == f[jkey+'[ct_key]'].value) {
     alert('<?php echo xl('Error: duplicated name on line') ?>' + ' ' + j);
     return;
    }
    if (parseInt(f[ikey+'[ct_id]'].value) == parseInt(f[jkey+'[ct_id]'].value)) {
     alert('<?php echo xl('Error: duplicated ID on line') ?>' + ' ' + j);
     return;
    }
   }
  }
 }
 f.submit();
}

// This invokes the find-code popup.
function sel_cvxcode(e) {
 current_sel_name = e.name;
 dlgopen('../patient_file/encounter/find_code_popup.php?codetype=CVX', '_blank', 500, 400);
}

</script>

</head>

<body class="body_top">

<form method='post' name='theform' id='theform' action='edit_list.php'>
<input type="hidden" name="formaction" id="formaction">

<p><b><?php xl('Edit list','e'); ?>:</b>&nbsp;
<select name='list_id' id="list_id">
<?php

// List order depends on language translation options.
$lang_id = empty($_SESSION['language_choice']) ? '1' : $_SESSION['language_choice'];

if (($lang_id == '1' && !empty($GLOBALS['skip_english_translation'])) ||
  !$GLOBALS['translate_lists'])
{
  $res = sqlStatement("SELECT option_id, title FROM list_options WHERE " .
    "list_id = 'lists' ORDER BY title, seq");
}
else {
  // Use and sort by the translated list name.
  $res = sqlStatement("SELECT lo.option_id, " .
    "IF(LENGTH(ld.definition),ld.definition,lo.title) AS title " .
    "FROM list_options AS lo " .
    "LEFT JOIN lang_constants AS lc ON lc.constant_name = lo.title " .
    "LEFT JOIN lang_definitions AS ld ON ld.cons_id = lc.cons_id AND " .
    "ld.lang_id = '$lang_id' " .
    "WHERE lo.list_id = 'lists' " .
    "ORDER BY IF(LENGTH(ld.definition),ld.definition,lo.title), lo.seq");
}

while ($row = sqlFetchArray($res)) {
  $key = $row['option_id'];
  echo "<option value='$key'";
  if ($key == $list_id) echo " selected";
  echo ">" . $row['title'] . "</option>\n";
}

?>
</select>
<input type="button" id="<?php echo $list_id; ?>" class="deletelist" value=<?php xl('Delete List','e','\'','\''); ?>>
<input type="button" id="newlist" class="newlist" value=<?php xl('New List','e','\'','\''); ?>>
</p>

<center>

<table cellpadding='2' cellspacing='0'>
 <tr class='head'>
<?php if ($list_id == 'feesheet') { ?>
  <td><b><?php xl('Group'    ,'e'); ?></b></td>
  <td><b><?php xl('Option'   ,'e'); ?></b></td>
  <td><b><?php xl('Generates','e'); ?></b></td>
<?php } else if ($list_id == 'code_types') { ?>
  <td><b><?php xl('Active'      ,'e'); ?></b></td>
  <td><b><?php xl('Key'        ,'e'); ?></b></td>
  <td><b><?php xl('ID'          ,'e'); ?></b></td>
  <td><b><?php xl('Label'       ,'e'); ?></b></td>
  <td><b><?php xl('Seq'         ,'e'); ?></b></td>
  <td><b><?php xl('ModLength'   ,'e'); ?></b></td>
  <td><b><?php xl('Justify'     ,'e'); ?></b></td>
  <td><b><?php xl('Mask'        ,'e'); ?></b></td>
  <td><b><?php xl('Claims'      ,'e'); ?></b></td>
  <td><b><?php xl('Fees'        ,'e'); ?></b></td>
  <td><b><?php xl('Relations'   ,'e'); ?></b></td>
  <td><b><?php xl('Hide'        ,'e'); ?></b></td>
  <td><b><?php xl('Diagnosis'   ,'e'); ?></b></td>
  <td><b><?php xl('External'    ,'e'); ?></b></td>
<?php } else { ?>
  <td title=<?php xl('Click to edit','e','\'','\''); ?>><b><?php  xl('ID','e'); ?></b></td>
  <td><b><?php xl('Title'  ,'e'); ?></b></td>	
  <?php //show translation column if not english and the translation lists flag is set 
  if ($GLOBALS['translate_lists'] && $_SESSION['language_choice'] > 1) {
    echo "<td><b>".xl('Translation')."</b><span class='help' title='".xl('The translated Title that will appear in current language')."'> (?)</span></td>";    
  } ?>  
  <td><b><?php xl('Order'  ,'e'); ?></b></td>
  <td><b><?php xl('Default','e'); ?></b></td>
<?php if ($list_id == 'taxrate') { ?>
  <td><b><?php xl('Rate'   ,'e'); ?></b></td>
<?php } else if ($list_id == 'contrameth') { ?>
  <td><b><?php xl('Effectiveness','e'); ?></b></td>
<?php } else if ($list_id == 'lbfnames') { ?>
  <td title='<?php xl('Number of past history columns','e'); ?>'><b><?php xl('Repeats','e'); ?></b></td>
<?php } else if ($list_id == 'fitness') { ?>
  <td><b><?php xl('Color:Abbr','e'); ?></b></td>
<?php } else if ($list_id == 'adjreason' || $list_id == 'abook_type') { ?>
  <td><b><?php xl('Type','e'); ?></b></td>
<?php } else if ($list_id == 'immunizations') { ?>
  <td><b>&nbsp;&nbsp;&nbsp;&nbsp;<?php xl('CVX Code Mapping','e'); ?></b></td>
<?php } if ($GLOBALS['ippf_specific']) { ?>
  <td><b><?php xl('Global ID','e'); ?></b></td>
<?php } ?>
  <td><b><?php xl('Notes','e'); ?></b></td>	
<?php } // end not fee sheet ?>
 </tr>

<?php 
// Get the selected list's elements.
if ($list_id) {
  if ($list_id == 'feesheet') {
    $res = sqlStatement("SELECT * FROM fee_sheet_options " .
      "ORDER BY fs_category, fs_option");
    while ($row = sqlFetchArray($res)) {
      writeFSLine($row['fs_category'], $row['fs_option'], $row['fs_codes']);
    }
    for ($i = 0; $i < 3; ++$i) {
      writeFSLine('', '', '');
    }
  }
  else if ($list_id == 'code_types') {
    $res = sqlStatement("SELECT * FROM code_types " .
      "ORDER BY ct_seq, ct_key");
    while ($row = sqlFetchArray($res)) {
      writeCTLine($row);
    }
    for ($i = 0; $i < 3; ++$i) {
      writeCTLine(array());
    }
  }
  else {
    $res = sqlStatement("SELECT * FROM list_options WHERE " .
      "list_id = '$list_id' ORDER BY seq,title");
    while ($row = sqlFetchArray($res)) {
      writeOptionLine($row['option_id'], $row['title'], $row['seq'],
        $row['is_default'], $row['option_value'], $row['mapping'],
        $row['notes']);
    }
    for ($i = 0; $i < 3; ++$i) {
      writeOptionLine('', '', '', '', 0);
    }
  }
}
?>

</table>

<p>
 <input type='button' name='form_save' id='form_save' value='<?php xl('Save','e'); ?>' />
</p>
</center>

</form>

<!-- template DIV that appears when user chooses to make a new list -->
<div id="newlistdetail" style="border: 1px solid black; padding: 3px; display: none; visibility: hidden; background-color: lightgrey;">
<?php xl('List Name','e'); ?>: <input type="textbox" size="20" maxlength="30" name="newlistname" id="newlistname">
<br>
<input type="button" class="savenewlist" value=<?php xl('Save New List','e','\'','\''); ?>>
<input type="button" class="cancelnewlist" value=<?php xl('Cancel','e','\'','\''); ?>>
</div>
</body>
<script language="javascript">
// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    $("#form_save").click(function() { SaveChanges(); });
    $("#list_id").change(function() { $('#theform').submit(); });

    $(".newlist").click(function() { NewList(this); });
    $(".savenewlist").click(function() { SaveNewList(this); });
    $(".deletelist").click(function() { DeleteList(this); });
    $(".cancelnewlist").click(function() { CancelNewList(this); });

    var SaveChanges = function() {
        $("#formaction").val("save");
        // $('#theform').submit();
        mysubmit();
    }

    // show the DIV to create a new list
    var NewList = function(btnObj) {
        // show the field details DIV
        $('#newlistdetail').css('visibility', 'visible');
        $('#newlistdetail').css('display', 'block');
        $(btnObj).parent().append($("#newlistdetail"));
        $('#newlistdetail > #newlistname').focus();
    }
    // save the new list
    var SaveNewList = function() {
        // the list name can only have letters, numbers, spaces and underscores
        // AND it cannot start with a number
        if ($("#newlistname").val().match(/^\d+/)) {
            alert("<?php xl('List names cannot start with numbers.','e'); ?>");
            return false;
        }
        var validname = $("#newlistname").val().replace(/[^A-za-z0-9 -]/g, "_"); // match any non-word characters and replace them
        if (validname != $("#newlistname").val()) {
            if (! confirm("<?php xl('Your list name has been changed to meet naming requirements.','e','','\n') . xl('Please compare the new name','e','',', \''); ?>"+validname+"<?php xl('with the old name','e','\' ',', \''); ?>"+$("#newlistname").val()+"<?php xl('Do you wish to continue with the new name?','e','\'.\n',''); ?>"))
            {
                return false;
            }
        }
        $("#newlistname").val(validname);
    
        // submit the form to add a new field to a specific group
        $("#formaction").val("addlist");
        $("#theform").submit();
    }
    // actually delete an entire list from the database
    var DeleteList = function(btnObj) {
        var listid = $(btnObj).attr("id");
        if (confirm("<?php xl('WARNING','e','',' - ') . xl('This action cannot be undone.','e','','\n') . xl('Are you sure you wish to delete the entire list','e',' ','('); ?>"+listid+")?")) {
            // submit the form to add a new field to a specific group
            $("#formaction").val("deletelist");
            $("#deletelistname").val(listid);
            $("#theform").submit();
        }
    };
    
    // just hide the new list DIV
    var CancelNewList = function(btnObj) {
        // hide the list details DIV
        $('#newlistdetail').css('visibility', 'hidden');
        $('#newlistdetail').css('display', 'none');
        // reset the new group values to a default
        $('#newlistdetail > #newlistname').val("");
    };
});

</script>

</html>
