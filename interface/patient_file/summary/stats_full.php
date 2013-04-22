<?php
/**
 * Copyright (C) 2005-2009 Rod Roark <rod@sunsetsystems.com>
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

?>
<html>

<head>
<?php html_header_show();?>

<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>

<title><?php echo htmlspecialchars( xl('Patient Issues'), ENT_NOQUOTES) ; ?></title>

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
  echo " <table style='margin-bottom:1em;text-align:center'>";
  ?>
  <tr class='head'>
    <th><?php echo htmlspecialchars( xl('Title'), ENT_NOQUOTES); ?></th>
    <th><?php echo htmlspecialchars( xl('Begin'), ENT_NOQUOTES); ?></th>
    <th><?php echo htmlspecialchars( xl('End'), ENT_NOQUOTES); ?></th>
    <th><?php echo htmlspecialchars( xl('Diag'), ENT_NOQUOTES); ?></th>
    <th><?php echo htmlspecialchars(xl('Status'),ENT_NOQUOTES); ?></th>
    <th><?php echo htmlspecialchars( xl('Occurrence'), ENT_NOQUOTES); ?></th>
    <?php if ($focustype == "allergy") { ?>
      <th><?php echo htmlspecialchars( xl('Reaction'), ENT_NOQUOTES); ?></th>
    <?php } ?>
    <?php if ($GLOBALS['athletic_team']) { ?>
      <th><?php echo htmlspecialchars( xl('Missed'), ENT_NOQUOTES); ?></th>
    <?php } else { ?>
      <th><?php echo htmlspecialchars( xl('Referred By'), ENT_NOQUOTES); ?></th>
    <?php } ?>
    <th><?php echo htmlspecialchars( xl('Comments'), ENT_NOQUOTES); ?></th>
    <th><?php echo htmlspecialchars( xl('Enc'), ENT_NOQUOTES); ?></th>
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
      echo "<tr><td class='text'><b>" . htmlspecialchars( xl("None"), ENT_NOQUOTES) . "</b></td></tr>";
    }
    else {
      // Data entry has not happened to this type, so can show the none selection option.
      echo "<tr><td class='text'><input type='checkbox' class='noneCheck' name='" . htmlspecialchars($focustype,ENT_QUOTES) . "' value='none' /><b>" . htmlspecialchars( xl("None"), ENT_NOQUOTES) . "</b></td></tr>";
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

    // look up the diag codes
    $codetext = "";
    if ($row['diagnosis'] != "") {
        $diags = explode(";", $row['diagnosis']);
        foreach ($diags as $diag) {
            $codedesc = lookup_code_descriptions($diag);
            $codetext .= htmlspecialchars($diag,ENT_NOQUOTES) . " (" . htmlspecialchars($codedesc,ENT_NOQUOTES) . ")<br>";
        }
    }

    // calculate the status
    if ($row['outcome'] == "1" && $row['enddate'] != NULL) {
      // Resolved
      $statusCompute = generate_display_field(array('data_type'=>'1','list_id'=>'outcome'), $row['outcome']);
    }
    else if($row['enddate'] == NULL) {
      $statusCompute = htmlspecialchars( xl("Active") ,ENT_NOQUOTES);
    }
    else {
      $statusCompute = htmlspecialchars( xl("Inactive") ,ENT_NOQUOTES);
    }
    $click_class='statrow';
    if($row['erx_source']==1 && $focustype=='allergy')
    $click_class='';
    elseif($row['erx_uploaded']==1 && $focustype=='medication')
    $click_class='';
    // output the TD row of info
    if ($row['enddate'] == NULL) {
      echo " <tr class='$bgclass detail $click_class' style='color:red;font-weight:bold' id='$rowid'>\n";
    }
    else {
      echo " <tr class='$bgclass detail $click_class' id='$rowid'>\n";
    }
    echo "  <td style='text-align:left'>" . htmlspecialchars($disptitle,ENT_NOQUOTES) . "</td>\n";
    echo "  <td>" . htmlspecialchars($row['begdate'],ENT_NOQUOTES) . "&nbsp;</td>\n";
    echo "  <td>" . htmlspecialchars($row['enddate'],ENT_NOQUOTES) . "&nbsp;</td>\n";
    // both codetext and statusCompute have already been escaped above with htmlspecialchars)
    echo "  <td>" . $codetext . "</td>\n";
    echo "  <td>" . $statusCompute . "&nbsp;</td>\n";
    echo "  <td class='nowrap'>";
    echo generate_display_field(array('data_type'=>'1','list_id'=>'occurrence'), $row['occurrence']);
    echo "</td>\n";
    if ($focustype == "allergy") {
      echo "  <td>" . htmlspecialchars($row['reaction'],ENT_NOQUOTES) . "&nbsp;</td>\n";
    }
    if ($GLOBALS['athletic_team']) {
        echo "  <td class='center'>" . $row['extrainfo'] . "</td>\n"; // games missed
    }
    else {
        echo "  <td>" . htmlspecialchars($row['referredby'],ENT_NOQUOTES) . "</td>\n";
    }
    echo "  <td>" . htmlspecialchars($row['comments'],ENT_NOQUOTES) . "</td>\n";
    echo "  <td id='e_$rowid' class='noclick center' title='" . htmlspecialchars( xl('View related encounters'), ENT_QUOTES) . "'>";
    echo "  <input type='button' value='" . htmlspecialchars($ierow['count'],ENT_QUOTES) . "' class='editenc' id='" . htmlspecialchars($rowid,ENT_QUOTES) . "' />";
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
    $(".editenc").click(function(event) { doeclick(this.id); event.stopPropagation(); });
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
