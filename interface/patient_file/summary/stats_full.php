<?php
/**
 * Copyright (C) 2005-2014 Rod Roark <rod@sunsetsystems.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 */

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

require_once('../../globals.php');
require_once($GLOBALS['srcdir'].'/lists.inc');
require_once($GLOBALS['srcdir'].'/acl.inc');
require_once($GLOBALS['fileroot'].'/custom/code_types.inc.php');
require_once($GLOBALS['srcdir'].'/options.inc.php');

 // Check authorization.
 if (acl_check('patients','med')) {
  $tmp = getPatientData($pid, "squad");
  if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
   die(htmlspecialchars( xl('Not authorized'), ENT_NOQUOTES) );
 }
 else {
  die(htmlspecialchars( xl('Not authorized'), ENT_NOQUOTES) );
 }

 // Collect parameter(s)
 $category = empty($_REQUEST['category']) ? '' : $_REQUEST['category'];

// Get patient's preferred language for the patient education URL.
$tmp = getPatientData($pid, 'language');
$language = $tmp['language'];
?>
<html>

<head>
<?php html_header_show();?>

<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>

<title><?php echo xlt('Patient Issues'); ?></title>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dialog.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/js/jquery.js"></script>

<script language="JavaScript">

// callback from add_edit_issue.php:
function refreshIssue(issue, title) {
    top.restoreSession();
    location.reload();
}

function dopclick(id,category) {
    <?php if (acl_check('patients','med','','write')): ?>
    if (category == 0) category = '';
    dlgopen('add_edit_issue.php?issue=' + encodeURIComponent(id) + '&thistype=' + encodeURIComponent(category), '_blank', 550, 400);
    <?php else: ?>
    alert("<?php echo addslashes( xl('You are not authorized to add/edit issues') ); ?>");
    <?php endif; ?>
}

// Process click on number of encounters.
function doeclick(id) {
    dlgopen('../problem_encounter.php?issue=' + id, '_blank', 550, 400);
}

// Process click on diagnosis for patient education popup.
function educlick(codetype, codevalue) {
  dlgopen('../education.php?type=' + encodeURIComponent(codetype) +
    '&code=' + encodeURIComponent(codevalue) +
    '&language=<?php echo urlencode($language); ?>',
    '_blank', 1024, 750);
}

// Add Encounter button is clicked.
function newEncounter() {
 var f = document.forms[0];
 top.restoreSession();
<?php if ($GLOBALS['concurrent_layout']) { ?>
 parent.left_nav.setRadio(window.name, 'nen');
 location.href='../../forms/newpatient/new.php?autoloaded=1&calenc=';
<?php } else { ?>
 top.Title.location.href='../encounter/encounter_title.php';
 top.Main.location.href='../encounter/patient_encounter.php?mode=new';
<?php } ?>
}

</script>

</head>

<body class="body_top">

<br>
<div style="text-align:center" class="buttons">
  <a href='javascript:;' class='css_button' id='back'><span><?php echo htmlspecialchars( xl('Back'), ENT_NOQUOTES); ?></span></a>
</div>
<br>
<br>

<div id='patient_stats'>

<form method='post' action='stats_full.php' onsubmit='return top.restoreSession()'>

<table>

<?php
$encount = 0;
$lasttype = "";
$first = 1; // flag for first section
foreach ($ISSUE_TYPES as $focustype => $focustitles) {

  if ($category) {
    // Only show this category
    if ($focustype != $category) continue;
  }

  if ($first) {
    $first = 0;
  }
  else {
    echo "</table>";
  }

  // Show header
  $disptype = $focustitles[0];
  if(($focustype=='allergy' || $focustype=='medication') && $GLOBALS['erx_enable'])
  echo "<a href='../../eRx.php?page=medentry' class='css_button_small' onclick='top.restoreSession()' ><span>" . htmlspecialchars( xl('Add'), ENT_NOQUOTES) . "</span></a>\n";
  else
  echo "<a href='javascript:;' class='css_button_small' onclick='dopclick(0,\"" . htmlspecialchars($focustype,ENT_QUOTES)  . "\")'><span>" . htmlspecialchars( xl('Add'), ENT_NOQUOTES) . "</span></a>\n";
  echo "  <span class='title'>" . htmlspecialchars($disptype,ENT_NOQUOTES) . "</span>\n";
  // echo " <table style='margin-bottom:1em;text-align:center'>";
  echo " <table style='margin-bottom:1em;'>";
  ?>
  <tr class='head'>
    <th style='text-align:left'><?php echo xlt('Title'); ?></th>
    <th style='text-align:left'><?php echo xlt('Begin'); ?></th>
    <th style='text-align:left'><?php echo xlt('End'); ?></th>
    <th style='text-align:left'><?php echo xlt('Coding (click for education)'); ?></th>
    <th style='text-align:left'><?php echo xlt('Status'); ?></th>
    <th style='text-align:left'><?php echo xlt('Occurrence'); ?></th>
    <?php if ($focustype == "allergy") { ?>
      <th style='text-align:left'><?php echo xlt('Reaction'); ?></th>
    <?php } ?>
    <?php if ($GLOBALS['athletic_team']) { ?>
      <th style='text-align:left'><?php echo xlt('Missed'); ?></th>
    <?php } else { ?>
      <th style='text-align:left'><?php echo xlt('Referred By'); ?></th>
    <?php } ?>
    <th style='text-align:left'><?php echo xlt('Comments'); ?></th>
    <th><?php echo xlt('Enc'); ?></th>
    </tr>
  <?php

  // collect issues
  $condition = '';
  if($GLOBALS['erx_enable'] && $GLOBALS['erx_medication_display'] && $focustype=='medication')
   $condition .= "and erx_uploaded != '1' ";
  $pres = sqlStatement("SELECT * FROM lists WHERE pid = ? AND type = ? $condition" .
   "ORDER BY begdate", array($pid,$focustype) );

  // if no issues (will place a 'None' text vs. toggle algorithm here)
  if (sqlNumRows($pres) < 1) {
    if ( getListTouch($pid,$focustype) ) {
      // Data entry has happened to this type, so can display an explicit None.
      echo "<tr><td class='text'><b>" . xlt("None") . "</b></td></tr>";
    }
    else {
      // Data entry has not happened to this type, so can show the none selection option.
      echo "<tr><td class='text'><input type='checkbox' class='noneCheck' name='" .
        attr($focustype) . "' value='none' /><b>" . xlt("None") . "</b></td></tr>";
    }
  }

  // display issues
  while ($row = sqlFetchArray($pres)) {

    $rowid = $row['id'];

    $disptitle = trim($row['title']) ? $row['title'] : "[Missing Title]";

    $ierow = sqlQuery("SELECT count(*) AS count FROM issue_encounter WHERE " .
      "list_id = ?", array($rowid) );

    // encount is used to toggle the color of the table-row output below
    ++$encount;
    $bgclass = (($encount & 1) ? "bg1" : "bg2");

    $colorstyle = empty($row['enddate']) ? "style='color:red'" : "";

    // look up the diag codes
    $codetext = "";
    if ($row['diagnosis'] != "") {
        $diags = explode(";", $row['diagnosis']);
        foreach ($diags as $diag) {
            $codedesc = lookup_code_descriptions($diag);
            list($codetype, $code) = explode(':', $diag);
            if ($codetext) $codetext .= "<br />";
            $codetext .= "<a href='javascript:educlick(\"$codetype\",\"$code\")' $colorstyle>" .
              text($diag . " (" . $codedesc . ")") . "</a>";
        }
    }

    // calculate the status
    if ($row['outcome'] == "1" && $row['enddate'] != NULL) {
      // Resolved
      $statusCompute = generate_display_field(array('data_type'=>'1','list_id'=>'outcome'), $row['outcome']);
    }
    else if($row['enddate'] == NULL) {
      $statusCompute = xlt("Active");
    }
    else {
      $statusCompute = xlt("Inactive");
    }
    $click_class='statrow';
    if($row['erx_source']==1 && $focustype=='allergy') $click_class='';
    elseif($row['erx_uploaded']==1 && $focustype=='medication') $click_class='';

    echo " <tr class='$bgclass detail' $colorstyle>\n";
    echo "  <td style='text-align:left' class='$click_class' id='$rowid'>" . text($disptitle) . "</td>\n";
    echo "  <td>" . text($row['begdate']) . "&nbsp;</td>\n";
    echo "  <td>" . text($row['enddate']) . "&nbsp;</td>\n";
    // both codetext and statusCompute have already been escaped above with htmlspecialchars)
    echo "  <td>" . $codetext . "</td>\n";
    echo "  <td>" . $statusCompute . "&nbsp;</td>\n";
    echo "  <td class='nowrap'>";
    echo generate_display_field(array('data_type'=>'1','list_id'=>'occurrence'), $row['occurrence']);
    echo "</td>\n";
    if ($focustype == "allergy") {
      echo "  <td>" . text($row['reaction']) . "&nbsp;</td>\n";
    }
    if ($GLOBALS['athletic_team']) {
        echo "  <td class='center'>" . $row['extrainfo'] . "</td>\n"; // games missed
    }
    else {
        echo "  <td>" . text($row['referredby']) . "</td>\n";
    }
    echo "  <td>" . text($row['comments']) . "</td>\n";
    echo "  <td id='e_$rowid' class='noclick center' title='" . xla('View related encounters') . "'>";
    echo "  <input type='button' value='" . attr($ierow['count']) . "' class='editenc' id='" . attr($rowid) . "' />";
    echo "  </td>";
    echo " </tr>\n";
  }
}
echo "</table>";
?>

</table>

</form>
</div> <!-- end patient_stats -->

</body>

<script language="javascript">
// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    $(".statrow").mouseover(function() { $(this).toggleClass("highlight"); });
    $(".statrow").mouseout(function() { $(this).toggleClass("highlight"); });

    $(".statrow").click(function() { dopclick(this.id,0); });
    $(".editenc").click(function(event) { doeclick(this.id); });
    $("#newencounter").click(function() { newEncounter(); });
    $("#history").click(function() { GotoHistory(); });
    $("#back").click(function() { GoBack(); });

    $(".noneCheck").click(function() {
      top.restoreSession();
      $.post( "../../../library/ajax/lists_touch.php", { type: this.name, patient_id: <?php echo htmlspecialchars($pid,ENT_QUOTES); ?> });
      $(this).hide(); 
    });
});

var GotoHistory = function() {
    top.restoreSession();
<?php if ($GLOBALS['concurrent_layout']): ?>
    parent.left_nav.setRadio(window.name,'his');
    location.href='../history/history_full.php';
<?php else: ?>
    location.href='../history/history_full.php';
<?php endif; ?>
}

var GoBack = function () {
    top.restoreSession();
<?php if ($GLOBALS['concurrent_layout']): ?>
    parent.left_nav.setRadio(window.name,'dem');
    location.href='demographics.php';
<?php else: ?>
    location.href="patient_summary.php";
<?php endif; ?>
}

</script>

</html>
