<?php
// Copyright (C) 2007-2008 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../globals.php");
require_once("$srcdir/acl.inc");
require_once("../../custom/code_types.inc.php");

$lists = array(
  'boolean'    => xl('Boolean'),
  'feesheet'   => xl('Fee Sheet'),
  'language'   => xl('Language'),
  'marital'    => xl('Marital Status'),
  'pricelevel' => xl('Price Level'),
  'ethrace'    => xl('Race/Ethnicity'),
  'risklevel'  => xl('Risk Level'),
  'sex'        => xl('Sex'),
  'taxrate'    => xl('Tax Rate'),
  'titles'     => xl('Titles'),
  'yesno'      => xl('Yes/No'),
  'userlist1'  => xl('User Defined List 1'),
  'userlist2'  => xl('User Defined List 2'),
);

$list_id = empty($_REQUEST['list_id']) ? 'language' : $_REQUEST['list_id'];

// Check authorization.
$thisauth = acl_check('admin', 'super');
if (!$thisauth) die("Not authorized.");

$opt_line_no = 0;

// Given a string of multiple instances of code_type|code|selector,
// make a description for each.
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
function writeOptionLine($option_id, $title, $seq, $default, $value) {
  global $opt_line_no, $list_id;
  ++$opt_line_no;
  $bgcolor = "#" . (($opt_line_no & 1) ? "ddddff" : "ffdddd");
  $checked = $default ? " checked" : "";

  echo " <tr bgcolor='$bgcolor'>\n";

  echo "  <td align='center' class='optcell'>";
  echo "<input type='text' name='opt[$opt_line_no][id]' value='" .
       htmlspecialchars($option_id, ENT_QUOTES) . "' size='20' maxlength='63' class='optin' />";
  echo "</td>\n";

  echo "  <td align='center' class='optcell'>";
  echo "<input type='text' name='opt[$opt_line_no][title]' value='" .
       htmlspecialchars($title, ENT_QUOTES) . "' size='20' maxlength='63' class='optin' />";
  echo "</td>\n";

  echo "  <td align='center' class='optcell'>";
  echo "<input type='text' name='opt[$opt_line_no][seq]' value='" .
       htmlspecialchars($seq, ENT_QUOTES) . "' size='4' maxlength='10' class='optin' />";
  echo "</td>\n";

  echo "  <td align='center' class='optcell'>";
  echo "<input type='checkbox' name='opt[$opt_line_no][default]' value='1'$checked class='optin' />";
  echo "</td>\n";

  if ($list_id == 'taxrate') {
    echo "  <td align='center' class='optcell'>";
    echo "<input type='text' name='opt[$opt_line_no][value]' value='" .
        htmlspecialchars($value, ENT_QUOTES) . "' size='8' maxlength='15' class='optin' />";
    echo "</td>\n";
  }

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
  echo "<a href='' id='codelist_$opt_line_no' onclick='return select_code($opt_line_no)'>";
  if (strlen($descs)) {
    $arrdescs = explode('~', $descs);
    foreach ($arrdescs as $desc) {
      echo "$desc<br />";
    }
  }
  else {
    echo "[Add]";
  }
  echo "</a>";
  echo "<input type='hidden' name='opt[$opt_line_no][codes]' value='" .
       htmlspecialchars($codes, ENT_QUOTES) . "' />";
  echo "<input type='hidden' name='opt[$opt_line_no][descs]' value='" .
       htmlspecialchars($descs, ENT_QUOTES) . "' />";
  echo "</td>\n";

  echo " </tr>\n";
}
?>
<html>

<head>
<? html_header_show();?>

<link rel=stylesheet href='<?php  echo $css_header ?>' type='text/css'>
<title><?php  xl('List Editor','e'); ?></title>

<style>
tr.head   { font-size:10pt; background-color:#cccccc; text-align:center; }
tr.detail { font-size:10pt; }
td        { font-size:10pt; }
input     { font-size:10pt; }
a, a:visited, a:hover { color:#0000cc; }
.optcell  { }
.optin    { background-color:transparent; }
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
   s += arrdescs[i] + '<br />';
  }
 }
 if (s.length == 0) s = '[Add]';
 setDivContent('codelist_' + lino, s);
}

// This invokes the find-code popup.
// For Fee Sheet administration.
function select_code(lino) {
 current_lino = lino;
 dlgopen('../patient_file/encounter/find_code_popup.php', '_blank', 700, 400);
 return false;
}

// This is for callback by the find-code popup.
// For Fee Sheet administration.
function set_related(codetype, code, selector, codedesc) {
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

</script>

</head>

<body <?php echo $top_bg_line;?>>

<?php
// If we are saving, then save.
//
if ($_POST['form_save'] && $list_id) {
  $opt = $_POST['opt'];
  if ($list_id == 'feesheet') {
    sqlStatement("DELETE FROM fee_sheet_options");
    for ($lino = 1; isset($opt["$lino"]['category']); ++$lino) {
      $iter = $opt["$lino"];
      $category = trim($iter['category']);
      $option   = trim($iter['option']);
      $codes    = $iter['codes'];
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
  else {
    sqlStatement("DELETE FROM list_options WHERE list_id = '$list_id'");
    for ($lino = 1; isset($opt["$lino"]['id']); ++$lino) {
      $iter = $opt["$lino"];
      $value = empty($iter['value']) ? 0 : (trim($iter['value']) + 0);
      if (strlen(trim($iter['id'])) > 0) {
        sqlInsert("INSERT INTO list_options ( " .
        "list_id, option_id, title, seq, is_default, option_value " .
        ") VALUES ( " .
        "'$list_id', "                       .
        "'" . trim($iter['id'])      . "', " .
        "'" . trim($iter['title'])   . "', " .
        "'" . trim($iter['seq'])     . "', " .
        "'" . trim($iter['default']) . "', " .
        "'" . $value                 . "' "  .
        ")");
      }
    }
  }
}
?>

<form method='post' name='theform' action='edit_list.php'>

<p><b>Edit list:</b>&nbsp;
<select name ='list_id' onchange='form.submit()'>
<?php
foreach ($lists as $key => $value) {
  echo "<option value='$key'";
  if ($key == $list_id) echo " selected";
  echo ">$value</option>\n";
}
?>
</select></p>

<center>

<table cellpadding='2' cellspacing='0'>
 <tr class='head'>
<?php if ($list_id == 'feesheet') { ?>
  <td><b><?php xl('Group'    ,'e'); ?></b></td>
  <td><b><?php xl('Option'   ,'e'); ?></b></td>
  <td><b><?php xl('Generates','e'); ?></b></td>
<?php } else { ?>
  <td title='Click to edit'><b><?php  xl('ID','e'); ?></b></td>
  <td><b><?php xl('Title'  ,'e'); ?></b></td>
  <td><b><?php xl('Order'  ,'e'); ?></b></td>
  <td><b><?php xl('Default','e'); ?></b></td>
<?php if ($list_id == 'taxrate') { ?>
  <td><b><?php xl('Rate'   ,'e'); ?></b></td>
<?php } ?>
<?php } ?>
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
  else {
    $res = sqlStatement("SELECT * FROM list_options WHERE " .
      "list_id = '$list_id' ORDER BY seq");
    while ($row = sqlFetchArray($res)) {
      writeOptionLine($row['option_id'], $row['title'], $row['seq'],
        $row['is_default'], $row['option_value']);
    }
    for ($i = 0; $i < 3; ++$i) {
      writeOptionLine('', '', '', '', 0);
    }
  }
}
?>

</table>

<p>
 <input type='submit' name='form_save' value='<?php xl('Save','e'); ?>' />
</p>
</center>

</form>
</body>
</html>
