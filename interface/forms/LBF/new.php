<?php
// Copyright (C) 2009-2011 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/formatting.inc.php");

$CPR = 4; // cells per row

$pprow = array();

// $is_lbf is defined in trend_form.php and indicates that we are being
// invoked from there; in that case the current encounter is irrelevant.
if (empty($is_lbf) && !$encounter) {
  die("Internal error: we do not seem to be in an encounter!");
}

function end_cell() {
  global $item_count, $cell_count, $historical_ids;
  if ($item_count > 0) {
    echo "</td>";

    foreach ($historical_ids as $key => $dummy) {
      $historical_ids[$key] .= "</td>";
    }

    $item_count = 0;
  }
}

function end_row() {
  global $cell_count, $CPR, $historical_ids;
  end_cell();
  if ($cell_count > 0) {
    for (; $cell_count < $CPR; ++$cell_count) {
      echo "<td></td>";
      foreach ($historical_ids as $key => $dummy) {
        $historical_ids[$key] .= "<td></td>";
      }
    }

    foreach ($historical_ids as $key => $dummy) {
      echo $historical_ids[$key];
    }

    echo "</tr>\n";
    $cell_count = 0;
  }
}

function end_group() {
  global $last_group;
  if (strlen($last_group) > 0) {
    end_row();
    echo " </table>\n";
    // No div for an empty group name.
    if (strlen($last_group) > 1) echo "</div>\n";
  }
}

$formname = formData('formname', 'G');
$formid   = 0 + formData('id', 'G');

// Get title and number of history columns for this form.
$tmp = sqlQuery("SELECT title, option_value FROM list_options WHERE " .
  "list_id = 'lbfnames' AND option_id = '$formname'");
$formtitle = $tmp['title'];
$formhistory = 0 + $tmp['option_value'];

$newid = 0;

// If Save was clicked, save the info.
//
if ($_POST['bn_save']) {
  $sets = "";
  $fres = sqlStatement("SELECT * FROM layout_options " .
    "WHERE form_id = '$formname' AND uor > 0 AND field_id != '' AND " .
    "edit_options != 'H' " .
    "ORDER BY group_name, seq");
  while ($frow = sqlFetchArray($fres)) {
    $field_id  = $frow['field_id'];
    $value = get_layout_form_value($frow);
    if ($formid) { // existing form
      if ($value === '') {
        $query = "DELETE FROM lbf_data WHERE " .
          "form_id = '$formid' AND field_id = '$field_id'";
      }
      else {
        $query = "REPLACE INTO lbf_data SET field_value = '$value', " .
          "form_id = '$formid', field_id = '$field_id'";
      }
      sqlStatement($query);
    }
    else { // new form
      if ($value !== '') {
        if ($newid) {
          sqlStatement("INSERT INTO lbf_data " .
            "( form_id, field_id, field_value ) " .
            " VALUES ( '$newid', '$field_id', '$value' )");
        }
        else {
          $newid = sqlInsert("INSERT INTO lbf_data " .
            "( field_id, field_value ) " .
            " VALUES ( '$field_id', '$value' )");
        }
      }
      // Note that a completely empty form will not be created at all!
    }
  }

  if (!$formid && $newid) {
    addForm($encounter, $formtitle, $newid, $formname, $pid, $userauthorized);
  }

  formHeader("Redirecting....");
  formJump();
  formFooter();
  exit;
}

if (empty($is_lbf)) {
  $fname = $GLOBALS['OE_SITE_DIR'] . "/LBF/$formname.plugin.php";
  if (file_exists($fname)) include_once($fname);
}
?>
<html>
<head>
<?php html_header_show();?>
<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
<style>

td, input, select, textarea {
 font-family: Arial, Helvetica, sans-serif;
 font-size: 10pt;
}

div.section {
 border: solid;
 border-width: 1px;
 border-color: #0000ff;
 margin: 0 0 0 10pt;
 padding: 5pt;
}

</style>

<style type="text/css">@import url(../../../library/dynarch_calendar.css);</style>

<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['webroot'] ?>/library/js/fancybox/jquery.fancybox-1.2.6.css" media="screen" />
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.1.3.2.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/common.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/fancybox/jquery.fancybox-1.2.6.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-ui.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.easydrag.handler.beta2.js"></script>
<script type="text/javascript" src="../../../library/textformat.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../../library/dynarch_calendar_setup.js"></script>

<script language="JavaScript">
$(document).ready(function(){
// fancy box
    if(window.enable_modals){
	enable_modals();
	}
	if(window.tabbify){
    tabbify();
	}
    // special size for
	$(".iframe_medium").fancybox( {
		'overlayOpacity' : 0.0,
		'showCloseButton' : true,
		'frameHeight' : 580,
		'frameWidth' : 900
	});
	
	$(function(){
		// add drag and drop functionality to fancybox
		$("#fancy_outer").easydrag();
	});
});
var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

// Supports customizable forms.
function divclick(cb, divid) {
 var divstyle = document.getElementById(divid).style;
 if (cb.checked) {
  divstyle.display = 'block';
 } else {
  divstyle.display = 'none';
 }
 return true;
}

// This is for callback by the find-code popup.
// Appends to or erases the current list of related codes.
function set_related(codetype, code, selector, codedesc) {
 var frc = document.getElementById('form_related_code');
 var s = frc.value;
 if (code) {
  if (s.length > 0) s += ';';
  s += codetype + ':' + code;
 } else {
  s = '';
 }
 frc.value = s;
}

// This invokes the find-code popup.
function sel_related() {
 dlgopen('<?php echo $rootdir ?>/patient_file/encounter/find_code_popup.php', '_blank', 500, 400);
}

<?php if (function_exists($formname . '_javascript')) call_user_func($formname . '_javascript'); ?>

</script>
</head>

<body <?php echo $top_bg_line; ?> topmargin="0" rightmargin="0" leftmargin="2" bottommargin="0" marginwidth="2" marginheight="0">
<form method="post" action="<?php echo $rootdir ?>/forms/LBF/new.php?formname=<?php echo $formname ?>&id=<?php echo $formid ?>"
 onsubmit="return top.restoreSession()">

<?php
  if (empty($is_lbf)) {
    $enrow = sqlQuery("SELECT p.fname, p.mname, p.lname, fe.date FROM " .
      "form_encounter AS fe, forms AS f, patient_data AS p WHERE " .
      "p.pid = '$pid' AND f.pid = '$pid' AND f.encounter = '$encounter' AND " .
      "f.formdir = 'newpatient' AND f.deleted = 0 AND " .
      "fe.id = f.form_id LIMIT 1");
    echo "<p class='title' style='margin-top:8px;margin-bottom:8px;text-align:center'>\n";
    echo "$formtitle " . xl('for') . ' ';
    echo $enrow['fname'] . ' ' . $enrow['mname'] . ' ' . $enrow['lname'];
    echo ' ' . htmlspecialchars(xl('on')) . ' ' . substr($enrow['date'], 0, 10);
    echo "</p>\n";
  }
?>

<!-- This is where a chart might display. -->
<div id="chart"></div>

<?php
  $shrow = getHistoryData($pid);

  $fres = sqlStatement("SELECT * FROM layout_options " .
    "WHERE form_id = '$formname' AND uor > 0 " .
    "ORDER BY group_name, seq");
  $last_group = '';
  $cell_count = 0;
  $item_count = 0;
  $display_style = 'block';

  // This is an array keyed on forms.form_id for other occurrences of this
  // form type.  The maximum number of such other occurrences to display is
  // in list_options.option_value for this form's list item.  Values in this
  // array are work areas for building the ending HTML for each displayed row.
  //
  $historical_ids = array();

  // True if any data items in this form can be graphed.
  $form_is_graphable = false;

  while ($frow = sqlFetchArray($fres)) {
    $this_group = $frow['group_name'];
    $titlecols  = $frow['titlecols'];
    $datacols   = $frow['datacols'];
    $data_type  = $frow['data_type'];
    $field_id   = $frow['field_id'];
    $list_id    = $frow['list_id'];
    $edit_options = $frow['edit_options'];

    $graphable  = strpos($edit_options, 'G') !== FALSE;
    if ($graphable) $form_is_graphable = true;

    $currvalue  = '';

    if ($frow['edit_options'] == 'H') {
      // This data comes from static history
      if (isset($shrow[$field_id])) $currvalue = $shrow[$field_id];
    } else {
      if ($formid) {
        $pprow = sqlQuery("SELECT field_value FROM lbf_data WHERE " .
          "form_id = '$formid' AND field_id = '$field_id'");
        if (!empty($pprow)) $currvalue = $pprow['field_value'];
      }
      else {
        // New form, see if there is a custom default from a plugin.
        $fname = $formname . '_default_' . $field_id;
        if (function_exists($fname)) {
          $currvalue = call_user_func($fname);
        }
      }
    }

    // Handle a data category (group) change.
    if (strcmp($this_group, $last_group) != 0) {
      end_group();
      $group_seq  = 'lbf' . substr($this_group, 0, 1);
      $group_name = substr($this_group, 1);
      $last_group = $this_group;

      // If group name is blank, no checkbox or div.
      if (strlen($this_group) > 1) {
        echo "<br /><span class='bold'><input type='checkbox' name='form_cb_$group_seq' value='1' " .
          "onclick='return divclick(this,\"div_$group_seq\");'";
        if ($display_style == 'block') echo " checked";
        echo " /><b>" . htmlspecialchars(xl_layout_label($group_name)) . "</b></span>\n";
        echo "<div id='div_$group_seq' class='section' style='display:$display_style;'>\n";
      }
      // echo " <table border='0' cellpadding='0' width='100%'>\n";
      echo " <table border='0' cellpadding='0' width='100%'>\n";
      $display_style = 'none';

      // Initialize historical data array and write date headers.
      $historical_ids = array();
      if ($formhistory > 0) {
        echo " <tr>";
        echo "<td colspan='$CPR' align='right' class='bold'>";
        if (empty($is_lbf)) echo htmlspecialchars(xl('Current'));
        echo "</td>\n";
        $hres = sqlStatement("SELECT f.form_id, fe.date " .
          "FROM forms AS f, form_encounter AS fe WHERE " .
          "f.pid = ? AND f.formdir = ? AND " .
          "f.form_id != ? AND f.deleted = 0 AND " .
          "fe.pid = f.pid AND fe.encounter = f.encounter " .
          "ORDER BY fe.date DESC, f.encounter DESC, f.date DESC " .
          "LIMIT ?",
          array($pid, $formname, $formid, $formhistory));
        // For some readings like vitals there may be multiple forms per encounter.
        // We sort these sensibly, however only the encounter date is shown here;
        // at some point we may wish to show also the data entry date/time.
        while ($hrow = sqlFetchArray($hres)) {
          $historical_ids[$hrow['form_id']] = '';
          echo "<td colspan='$CPR' align='right' class='bold'>&nbsp;" .
            oeFormatShortDate(substr($hrow['date'], 0, 10)) . "</td>\n";
        }
        echo " </tr>";
      }

    }

    // Handle starting of a new row.
    if (($titlecols > 0 && $cell_count >= $CPR) || $cell_count == 0) {
      end_row();
      echo " <tr>";
      // Clear historical data string.
      foreach ($historical_ids as $key => $dummy) {
        $historical_ids[$key] = '';
      }
    }

    if ($item_count == 0 && $titlecols == 0) $titlecols = 1;

    // Handle starting of a new label cell.
    if ($titlecols > 0) {
      end_cell();
      echo "<td valign='top' colspan='$titlecols' width='1%' nowrap";
      echo " class='";
      echo ($frow['uor'] == 2) ? "required" : "bold";
      if ($graphable) echo " graph";
      echo "'";
      if ($cell_count == 2) echo " style='padding-left:10pt'";
      if ($graphable) echo " id='$field_id'";
      echo ">";

      foreach ($historical_ids as $key => $dummy) {
        $historical_ids[$key] .= "<td valign='top' colspan='$titlecols' class='text' nowrap>";
      }

      $cell_count += $titlecols;
    }
    ++$item_count;

    echo "<b>";
    if ($frow['title']) echo htmlspecialchars(xl_layout_label($frow['title']) . ":"); else echo "&nbsp;";
    echo "</b>";

    // Note the labels are not repeated in the history columns.

    // Handle starting of a new data cell.
    if ($datacols > 0) {
      end_cell();
      echo "<td valign='top' colspan='$datacols' class='text'";
      if ($cell_count > 0) echo " style='padding-left:5pt'";
      echo ">";

      foreach ($historical_ids as $key => $dummy) {
        $historical_ids[$key] .= "<td valign='top' align='right' colspan='$datacols' class='text'>";
      }

      $cell_count += $datacols;
    }

    ++$item_count;

    // Skip current-value fields for the display-only case.
    if (empty($is_lbf)) {
      if ($frow['edit_options'] == 'H')
        echo generate_display_field($frow, $currvalue);
      else
        generate_form_field($frow, $currvalue);
    }

    // Append to historical data of other dates for this item.
    foreach ($historical_ids as $key => $dummy) {
      $hvrow = sqlQuery("SELECT field_value FROM lbf_data WHERE " .
        "form_id = '$key' AND field_id = '$field_id'");
      $value = empty($hvrow) ? '' : $hvrow['field_value'];
      $historical_ids[$key] .= generate_display_field($frow, $value);
    }

  }

  end_group();
?>

<p style='text-align:center'>
<?php if (empty($is_lbf)) { ?>
<input type='submit' name='bn_save' value='<?php echo htmlspecialchars(xl('Save')) ?>' />
&nbsp;
<input type='button' value='<?php echo htmlspecialchars(xl('Cancel')) ?>' onclick="top.restoreSession();location='<?php echo $GLOBALS['form_exit_url']; ?>'" />
&nbsp;
<?php if ($form_is_graphable) { ?>
<input type='button' value='<?php echo htmlspecialchars(xl('Show Graph')) ?>' onclick="top.restoreSession();location='../../patient_file/encounter/trend_form.php?formname=<?php echo $formname; ?>'" />
&nbsp;
<?php } ?>
<?php } else { ?>
<input type='button' value='<?php echo htmlspecialchars(xl('Back')) ?>' onclick='window.back();' />
<?php } ?>
</p>

</form>

<!-- include support for the list-add selectbox feature -->
<?php include $GLOBALS['fileroot'] . "/library/options_listadd.inc"; ?>

<script language="JavaScript">
<?php echo $date_init; ?>
<?php
if (function_exists($formname . '_javascript_onload')) {
  call_user_func($formname . '_javascript_onload');
}
// TBD: If $alertmsg, display it with a JavaScript alert().
?>
</script>

</body>
</html>
