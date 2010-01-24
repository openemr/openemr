<?php
// Copyright (C) 2007-2010 Rod Roark <rod@sunsetsystems.com>
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

  // Tax rates and contraceptive methods have an additional attribute.
  //
  if ($list_id == 'taxrate' || $list_id == 'contrameth') {
    echo "  <td align='center' class='optcell'>";
    echo "<input type='text' name='opt[$opt_line_no][value]' value='" .
        htmlspecialchars($value, ENT_QUOTES) . "' size='8' maxlength='15' class='optin' />";
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

// Called when a "default" checkbox is clicked.  Clears all the others.
function defClicked(lino) {
 var f = document.forms[0];
 for (var i = 1; f['opt[' + i + '][default]']; ++i) {
  if (i != lino) f['opt[' + i + '][default]'].checked = false;
 }
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
<?php } else if ($list_id == 'fitness') { ?>
  <td><b><?php xl('Color:Abbr','e'); ?></b></td>
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
        $('#theform').submit();
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
