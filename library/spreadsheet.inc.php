<?php
// Copyright (C) 2006-2007 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");
include_once("$include_root/forms/fee_sheet/codes.php");

$celltypes = array(
 '0' => 'Unused',
 '1' => 'Static',
 '2' => 'Checkbox',
 '3' => 'Text',
 '4' => 'Longtext',
// '5' => 'Function',
);

// encode a string from a form field for database writing.
function form2db($fldval) {
 $fldval = trim($fldval);
 if (!get_magic_quotes_gpc()) $fldval = addslashes($fldval);
 return $fldval;
}

// encode a plain string for database writing.
function real2db($fldval) {
 return addslashes($fldval);
}

// Get the actual string from a form field.
function form2real($fldval) {
 $fldval = trim($fldval);
 if (get_magic_quotes_gpc()) $fldval = stripslashes($fldval);
 return $fldval;
}

// encode a plain string for html display.
function real2form($fldval) {
 return htmlspecialchars($fldval, ENT_QUOTES);
}

// Putting an error message in here will result in a javascript alert.
$alertmsg = '';

// If we are invoked as a popup (not in an encounter):
$popup = $_GET['popup'];

// The form ID is passed to us when an existing encounter form is loaded.
$formid = $_GET['id'];

// $tempid is the currently selected template, if any.
$tempid = $_POST['form_template'] + 0;

// This is the start date to be saved with the spreadsheet.
$start_date = '';

$form_completed = '0';

if (!$popup && !$encounter) { // $encounter comes from globals.php
 die("Internal error: we do not seem to be in an encounter!");
}

// Get the name of the template selected by the dropdown, if any;
// or if we are loading a form then it comes from that.
$template_name = '';
if ($tempid) {
  $trow = sqlQuery("SELECT value FROM form_$spreadsheet_form_name WHERE " .
    "id = $tempid AND rownbr = -1 AND colnbr = -1");
  $template_name = $trow['value'];
}
else if ($formid) {
  $trow = sqlQuery("SELECT value FROM form_$spreadsheet_form_name WHERE " .
    "id = $formid AND rownbr = -1 AND colnbr = -1");
  list($form_completed, $start_date, $template_name) = explode('|', $trow['value'], 3);
}

if (!$start_date) $start_date = form2real($_POST['form_start_date']);

// Used rows and columns are those beyond which there are only unused cells.
$num_used_rows = 0;
$num_used_cols = 0;

// If we are saving...
//
if ($_POST['bn_save_form'] || $_POST['bn_save_template']) {

  // The form data determines how many rows and columns are now used.
  $cells = $_POST['cell'];
  for ($i = 0; $i < count($cells); ++$i) {
    $row = $cells[$i];
    for ($j = 0; $j < count($row); ++$j) {
      if (substr($row[$j], 0, 1)) {
        if ($i >= $num_used_rows) $num_used_rows = $i + 1;
        if ($j >= $num_used_cols) $num_used_cols = $j + 1;
      }
    }
  }

  if ($_POST['bn_save_form']) {
    $form_completed = $_POST['form_completed'] ? '1' : '0';

    // If updating an existing form...
    if ($formid) {
      sqlStatement("UPDATE form_$spreadsheet_form_name SET "      .
        "value = '$form_completed|$start_date|$template_name' " .
        "WHERE id = '$formid' AND rownbr = -1 AND colnbr = -1");
      sqlStatement("DELETE FROM form_$spreadsheet_form_name WHERE " .
        "id = '$formid' AND rownbr >= 0 AND colnbr >= 0");
    }
    // If adding a new form...
    else {
      sqlStatement("LOCK TABLES form_$spreadsheet_form_name WRITE");
      $tmprow = sqlQuery("SELECT MAX(id) AS maxid FROM form_$spreadsheet_form_name");
      $formid = $tmprow['maxid'] + 1;
      if ($formid <= 0) $formid = 1;
      sqlInsert("INSERT INTO form_$spreadsheet_form_name ( " .
        "id, rownbr, colnbr, datatype, value " .
        ") VALUES ( " .
        "$formid, -1, -1, 0, " .
        "'$form_completed|$start_date|$template_name' " .
        ")");
      sqlStatement("UNLOCK TABLES");
      addForm($encounter, "Injury Log", $formid, "$spreadsheet_form_name",
        $pid, $userauthorized);
    }
    $saveid = $formid;
  }
  else { // saving a template
    // The rule is, we can update the original name, or insert a new name
    // which must not match any existing template name.
    $new_template_name = form2real($_POST['form_new_template_name']);
    if ($new_template_name != $template_name) {
      $trow = sqlQuery("SELECT id FROM form_$spreadsheet_form_name WHERE " .
        "id < 0 AND rownbr = -1 AND colnbr = -1 AND value = '" .
        real2db($new_template_name) . "'");
      if ($trow['id']) {
        $alertmsg = "Template \"" . real2form($new_template_name) .
          "\" already exists!";
      }
      else {
        $tempid = 0; // to force insert of new template
        $template_name = $new_template_name;
      }
    }
    if (!$alertmsg) {
      // If updating an existing template...
      if ($tempid) {
        sqlStatement("DELETE FROM form_$spreadsheet_form_name WHERE " .
          "id = '$tempid' AND rownbr >= 0 AND colnbr >= 0");
      }
      // If adding a new template...
      else {
        sqlStatement("LOCK TABLES form_$spreadsheet_form_name WRITE");
        $tmprow = sqlQuery("SELECT MIN(id) AS minid FROM form_$spreadsheet_form_name");
        $tempid = $tmprow['minid'] - 1;
        if ($tempid >= 0) $tempid = -1;
        sqlInsert("INSERT INTO form_$spreadsheet_form_name ( " .
          "id, rownbr, colnbr, datatype, value " .
          ") VALUES ( " .
          "$tempid, -1, -1, 0, " .
          "'" . real2db($template_name) . "' " .
          ")");
        sqlStatement("UNLOCK TABLES");
      }
      $saveid = $tempid;
    }
  }

  if (!$alertmsg) {
    // Finally, save the table cells.
    for ($i = 0; $i < $num_used_rows; ++$i) {
      for ($j = 0; $j < $num_used_cols; ++$j) {
        $tmp = $cells[$i][$j];
        $celltype = substr($tmp, 0, 1) + 0;
        $cellvalue = form2db(substr($tmp, 1));
        if ($celltype) {
          sqlInsert("INSERT INTO form_$spreadsheet_form_name ( " .
            "id, rownbr, colnbr, datatype, value " .
            ") VALUES ( " .
            "$saveid, $i, $j, $celltype, '$cellvalue' )");
        }
      }
    }
  }
}
else if ($_POST['bn_delete_template'] && $tempid) {
  sqlStatement("DELETE FROM form_$spreadsheet_form_name WHERE " .
    "id = '$tempid'");
  $tempid = 0;
  $template_name = '';
}

if ($_POST['bn_save_form'] && !$alertmsg && !$popup) {
  formHeader("Redirecting....");
  formJump();
  formFooter();
  exit;
}

// If we get here then we are displaying a spreadsheet, either a template or
// an encounter form.

// Get the array of template names.
$tres = sqlStatement("SELECT id, value FROM form_$spreadsheet_form_name WHERE " .
  "id < 0 AND rownbr = -1 AND colnbr = -1 ORDER BY value");

$dres = false;

# If we are reloading a form, get it.
if ($formid) {
  $dres = sqlStatement("SELECT * FROM form_$spreadsheet_form_name WHERE " .
    "id = '$formid' ORDER BY rownbr, colnbr");
  $tmprow = sqlQuery("SELECT MAX(rownbr) AS rowmax, MAX(colnbr) AS colmax " .
    "FROM form_$spreadsheet_form_name WHERE id = '$formid'");
  $num_used_rows = $tmprow['rowmax'] + 1;
  $num_used_cols = $tmprow['colmax'] + 1;
}
# Otherwise if we are editing a template, get it.
else if ($tempid) {
  $dres = sqlStatement("SELECT * FROM form_$spreadsheet_form_name WHERE " .
    "id = '$tempid' ORDER BY rownbr, colnbr");
  $tmprow = sqlQuery("SELECT MAX(rownbr) AS rowmax, MAX(colnbr) AS colmax " .
    "FROM form_$spreadsheet_form_name WHERE id = '$tempid'");
  $num_used_rows = $tmprow['rowmax'] + 1;
  $num_used_cols = $tmprow['colmax'] + 1;
}

// Virtual rows and columns are those available when in Edit Structure mode,
// and include some additional ones beyond those used. This allows quite a
// lot of stuff to be entered before having to save the template.
$num_virtual_rows = $num_used_rows ? $num_used_rows + 5 : 10;
$num_virtual_cols = $num_used_cols ? $num_used_cols + 5 : 10;
?>
<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<style  type="text/css">@import url(../../../library/dynarch_calendar.css);</style>
<style>
.sstable td {
 font-family: sans-serif;
 font-weight: bold;
 font-size: 9pt;
}
.seltype {
 font-family: sans-serif;
 font-weight: normal;
 font-size: 8pt;
 background-color: transparent;
}
.selgen {
 font-family: sans-serif;
 font-weight: normal;
 font-size: 8pt;
 background-color: transparent;
}
.intext {
 font-family: sans-serif;
 font-weight: normal;
 font-size: 9pt;
 background-color: transparent;
 width: 100%;
}
.seldiv {
 margin: 0 0 0 0;
 padding: 0 0 0 0;
}
</style>
<script type="text/javascript" src="../../../library/textformat.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar_en.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar_setup.js"></script>

<script language="JavaScript">
 var mypcc = '<?php echo $GLOBALS['phone_country_code']; ?>';
 var ssChanged = false; // if they have changed anything in the spreadsheet
 var startDate = '<?php echo $start_date ? $start_date : date('Y-m-d'); ?>';

 // Helper function to set the contents of a block.
 function setBlockContent(id, content) {
  if (document.getElementById) {
   var x = document.getElementById(id);
   x.innerHTML = '';
   x.innerHTML = content;
  }
  else if (document.all) {
   var x = document.all[id];
   x.innerHTML = content;
  }
  // alert("ID = \"" + id + "\", string = \"" + content + "\"");
 }

 // Called when a different template name is selected.
 function newTemplate(sel) {
  if (ssChanged && !confirm('You have made changes that will be discarded ' +
    'if you select a new template. Do you really want to do this?'))
  {
   // Restore the original template selection.
   for (var i = 0; i < sel.options.length; ++i) {
    if (sel.options[i].value == '<?php echo $tempid ?>') {
     sel.options[i].selected = true;
    }
   }
   return;
  }
  top.restoreSession();
  document.forms[0].submit();
 }

 // Called when the Cancel button is clicked.
 function doCancel() {
  if (!ssChanged || confirm('You have made changes that will be discarded ' +
    'if you close now. Click OK if you really want to exit this form.'))
  {
<?php if ($popup) { ?>
   window.close();
<?php } else { ?>
   top.restoreSession();
   location='<?php echo $GLOBALS['form_exit_url'] ?>';
<?php } ?>
  }
 }

 // Called when the Edit Structure checkbox is clicked.
 function editChanged() {
  var f = document.forms[0];
  var newdisplay = f.form_edit_template.checked ? '' : 'none';
  var usedrows = 0;
  var usedcols = 0;
  for (var i = 0; i < <?php echo $num_virtual_rows; ?>; ++i) {
   for (var j = 0; j < <?php echo $num_virtual_cols; ?>; ++j) {
    if (f['cell['+i+']['+j+']'].value.charAt(0) != '0') {
     if (i >= usedrows) usedrows = i + 1;
     if (j >= usedcols) usedcols = j + 1;
    }
   }
  }
  for (var i = 0; i < <?php echo $num_virtual_rows; ?>; ++i) {
   for (var j = 0; j < <?php echo $num_virtual_cols; ?>; ++j) {
    // document.getElementById('div_'+i+'_'+j).style.display = newdisplay;
    document.getElementById('sel_'+i+'_'+j).style.display = newdisplay;
    if (i >= usedrows || j >= usedcols) {
     document.getElementById('td_'+i+'_'+j).style.display = newdisplay;
    }
   }
  }
 }

 // Prepare a string for use as an HTML value attribute in single quotes.
 function escQuotes(s) {
  return s.replace(/'/g, "&#39;");
 }

 // Parse static text to evaluate possible functions.
 function genStatic(s) {
  var i = 0;

  // Parse "%day(n)".
  while ((i = s.indexOf('%day(')) >= 0) {
   var s1 = s.substring(0, i);
   i += 5;
   var j = s.indexOf(')', i);
   if (j < 0) break;
   var dayinc = parseInt(s.substring(i,j));
   var mydate = new Date(parseInt(startDate.substring(0,4)),
    parseInt(startDate.substring(5,7))-1, parseInt(startDate.substring(8)));
   mydate.setTime(1000 * 60 * 60 * 24 * dayinc + mydate.getTime());
   var year = mydate.getYear(); if (year < 1900) year += 1900;
   s = s1 + year + '-' +
    ('' + (mydate.getMonth() + 101)).substring(1) + '-' +
    ('' + (mydate.getDate()  + 100)).substring(1) +
    s.substring(j + 1);
  }

  // Parse "%sel(first,second,third,...,default)".
  while ((i = s.indexOf('%sel(')) >= 0) {
   var s1 = s.substring(0, i);
   i += 5;
   var j = s.indexOf(')', i);
   if (j < 0) break;
   var x = s.substring(0,j);
   var k = x.lastIndexOf(',');
   if (k < i) break;
   var dflt = s.substring(k+1, j);
   x = "<select class='selgen' onchange='newsel(this)'>";
   while ((k = s.indexOf(',', i)) > i) {
    if (k > j) break;
    var elem = s.substring(i,k);
    x += "<option value='" + elem + "'";
    if (elem == dflt) x += " selected";
    x += ">" + elem + "</option>";
    i = k + 1;
   }
   x += "</select>";
   s = s1 + x + s.substring(j + 1);
   break; // only one %sel allowed
  }

  // Parse "%ptp(default)".
  while ((i = s.indexOf('%ptp(')) >= 0) {
   var s1 = s.substring(0, i);
   i += 5;
   var j = s.indexOf(')', i);
   if (j < 0) break;
   var dflt = s.substring(i, j);
   x = "<select class='selgen' onchange='newptp(this)'>";
   x += "<option value=''>-- Select --</option>";
<?php
  foreach ($bcodes['PTCJ']['Physiotherapy Procedures'] as $key => $value) {
    echo "   x += \"<option value='$key'\";\n";
    echo "   if (dflt == '$key') x += ' selected';\n";
    echo "   x += '>$value</option>';\n";
  }
?>
   x += "</select>";
   s = s1 + x + s.substring(j + 1);
   break; // only one %ptp allowed
  }

  return s;
 }

 // Called when a cell type selector in the spreadsheet is clicked.
 function newType(i,j) {
  ssChanged = true;
  var f = document.forms[0];
  var typeval = f['cell['+i+']['+j+']'].value;
  var thevalue = typeval.substring(1);
  var thetype = document.getElementById('sel_'+i+'_'+j).value;
  var s = "<input type='hidden' name='cell[" + i + "][" + j + "]' " +
   "value='" + thetype + escQuotes(thevalue) + "' />";

  if (thetype == '1') {
   s += genStatic(thevalue);
  }
  else if (thetype == '2') {
   s += "<input type='checkbox' value='1' onclick='cbClick(this," + i + "," + j + ")'";
   if (thevalue) s += " checked";
   s += " />";
  }
  else if (thetype == '3') {
   s += "<input type='text' onchange='textChange(this," + i + "," + j + ")'" +
    " class='intext' value='" + escQuotes(thevalue) + "' size='12' />";
  }
  else if (thetype == '4') {
   s += "<textarea rows='3' cols='25' wrap='virtual' class='intext' " +
    "onchange='longChange(this," + i + "," + j + ")'>" +
    escQuotes(thevalue) + "</textarea>";
  }
  setBlockContent('vis_' + i + '_' + j, s);
 }

 // Called when a checkbox in the spreadsheet is clicked.
 function cbClick(elem,i,j) {
  ssChanged = true;
  var f = document.forms[0];
  var cell = f['cell['+i+']['+j+']'];
  cell.value = '2' + (elem.checked ? '1' : '');
 }

 // Called when a text value in the spreadsheet is changed.
 function textChange(elem,i,j) {
  ssChanged = true;
  var f = document.forms[0];
  var cell = f['cell['+i+']['+j+']'];
  cell.value = '3' + elem.value;
 }

 // Called when a textarea value in the spreadsheet is changed.
 function longChange(elem,i,j) {
  ssChanged = true;
  var f = document.forms[0];
  var cell = f['cell['+i+']['+j+']'];
  cell.value = '4' + elem.value;
 }

 // Helper function to get the value element of a table cell given any
 // other element within that cell.
 function getHidden(sel) {
  var p = sel.parentNode;
  while (p.tagName != 'TD') {
   if (!p.parentNode || p.parentNode == p) {
    alert("JavaScript error, cannot find TD element");
    return '';
   }
   p = p.parentNode;
  }
  // Get the <input type=hidden> element within this table cell.
  var f = document.forms[0];
  var s = p.id.substring(3);
  var uix = s.indexOf('_');
  var i = s.substring(0, uix);
  var j = s.substring(uix+1);
  return f['cell[' + i + '][' + j + ']'];
 }

 // Called when a user-defined select list has a new selection.
 // This rewrites the function definition for the select list.
 function newsel(sel) {
  var inelem = getHidden(sel);
  var s = inelem.value;
  var i = s.indexOf('%sel(');
  var j = s.indexOf(')', i);
  var x = s.substring(0, j);
  var k = x.lastIndexOf(',');
  inelem.value = s.substring(0, k+1) + sel.value + s.substring(j);
 }

 // Called when a physiotherapy select list has a new selection.
 // This rewrites the function definition for the select list.
 function newptp(sel) {
  var inelem = getHidden(sel);
  var s = inelem.value;
  var i = s.indexOf('%ptp(') + 5;
  var j = s.indexOf(')', i);
  inelem.value = s.substring(0, i) + sel.value + s.substring(j);
 }

</script>

</head>

<body <?echo $top_bg_line;?> topmargin="0" rightmargin="0" leftmargin="0"
 bottommargin="0" marginwidth="0" marginheight="0">
<form method="post" action="<?php echo "$rootdir/forms/$spreadsheet_form_name/new.php?id=$formid"; if ($popup) echo '&popup=1'; ?>"
 onsubmit="return top.restoreSession()">
<center>

<table border='0' cellpadding='5' cellspacing='0' style='margin:8pt'>
 <tr bgcolor='#ddddff'>
  <td>
   <?php xl('Start Date','e'); ?>:
   <input type='text' name='form_start_date' id='form_start_date'
    size='10' value='<?php echo $start_date; ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'
    <?php if ($formid && $start_date) echo 'disabled '; ?>/>
<?php if (!$formid || !$start_date) { ?>
   <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_start_date' border='0' alt='[?]' style='cursor:pointer'
    title='Click here to choose a date'>
<?php } ?>
   &nbsp;
   <?php xl('Template:','e') ?>
   <select name='form_template' onchange='newTemplate(this)'<?php if ($formid) echo ' disabled'; ?>>
    <option value='0'>-- Select --</option>
<?php
 while ($trow = sqlFetchArray($tres)) {
  echo "    <option value='" . $trow['id'] . "'";
  if ($tempid && $tempid == $trow['id'] ||
      $formid && $template_name == $trow['value'])
  {
    echo " selected";
  }
  echo ">" . $trow['value'] . "</option>\n";
 }
?>
   </select>
   &nbsp;
   <input type='checkbox' name='form_edit_template'
    onclick='editChanged()'
    title='<?php xl("If you want to change data types, or add rows or columns","e") ?>' />
   <?php xl('Edit Structure','e') ?>
<?php if ($formid) { ?>
   &nbsp;
   <input type='checkbox' name='form_completed'
    title='<?php xl("If all data for all columns are complete for this form","e") ?>'
    <?php if ($form_completed) echo 'checked '; ?>/>
   <?php xl('Completed','e') ?>
<?php } ?>
  </td>
 </tr>
</table>

<table border='1' cellpadding='2' cellspacing='0' class='sstable'>
<?php
if ($dres) $drow = sqlFetchArray($dres);
$typeprompts = array('unused','static','checkbox','text');

for ($i = 0; $i < $num_virtual_rows; ++$i) {
  echo " <tr>\n";
  for ($j = 0; $j < $num_virtual_cols; ++$j) {

    // Match up with the database for cell type and value.
    $celltype = '0';
    $cellvalue = '';
    if ($dres) {
      while ($drow && $drow['rownbr'] < $i)
        $drow = sqlFetchArray($dres);
      while ($drow && $drow['rownbr'] == $i && $drow['colnbr'] < $j)
        $drow = sqlFetchArray($dres);
      if ($drow && $drow['rownbr'] == $i && $drow['colnbr'] == $j) {
        $celltype = $drow['datatype'];
        $cellvalue = real2form($drow['value']);
        $cellstatic = addslashes($drow['value']);
      }
    }

    echo "  <td id='td_${i}_${j}' valign='top'";
    if ($i >= $num_used_rows || $j >= $num_used_cols)
      echo " style='display:none'";
    echo ">";

    /*****************************************************************
    echo "<span id='div_${i}_${j}' ";
    echo "style='float:right;cursor:pointer;display:none' ";
    echo "onclick='newType($i,$j)'>[";
    echo $typeprompts[$celltype];
    echo "]</span>";
    *****************************************************************/
    echo "<div class='seldiv'>";
    echo "<select id='sel_${i}_${j}' class='seltype' style='display:none' " .
      "onchange='newType($i,$j)'>";
    foreach ($celltypes as $key => $value) {
      echo "<option value='$key'";
      if ($key == $celltype) echo " selected";
      echo ">$value</option>";
    }
    echo "</select>";
    echo "</div>";
    /****************************************************************/

    echo "<span id='vis_${i}_${j}'>"; // new //

    echo "<input type='hidden' name='cell[$i][$j]' value='$celltype$cellvalue' />";
    if ($celltype == '1') {
      // So we don't have to write a PHP version of genStatic():
      echo "<script language='JavaScript'>document.write(genStatic('$cellstatic'));</script>";
    }
    else if ($celltype == '2') {
      echo "<input type='checkbox' value='1' onclick='cbClick(this,$i,$j)'";
      if ($cellvalue) echo " checked";
      echo " />";
    }
    else if ($celltype == '3') {
      echo "<input type='text' class='intext' onchange='textChange(this,$i,$j)'";
      echo " value='$cellvalue'";
      echo " size='12' />";
    }
    else if ($celltype == '4') {
      echo "<textarea rows='3' cols='25' wrap='virtual' class='intext' " .
        "onchange='longChange(this,$i,$j)'>";
      echo $cellvalue;
      echo "</textarea>";
    }

    echo "</span>"; // new //

    echo "</td>\n";
  }
  echo " </tr>\n";
}
?>
</table>

<p>
<input type='submit' name='bn_save_form' value='Save Form' />
<?php if (!$formid) { ?>
&nbsp;
<input type='submit' name='bn_save_template' value='Save as Template:' />
&nbsp;
<input type='text' name='form_new_template_name' value='<?php echo $template_name ?>' />
&nbsp;
<input type='submit' name='bn_delete_template' value='Delete Template' />
<?php } ?>
&nbsp;
<input type='button' value='Cancel' onclick="doCancel()" />
</p>

</center>
</form>
<script language='JavaScript'>
 Calendar.setup({inputField:"form_start_date", ifFormat:"%Y-%m-%d", button:"img_start_date"});
<?php
if ($alertmsg) echo " alert('$alertmsg');\n";
?>
</script>
</body>
</html>
