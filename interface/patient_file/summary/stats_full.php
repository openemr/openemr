<?php
// Copyright (C) 2005-2009 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../../globals.php");
require_once("$srcdir/lists.inc");
require_once("$srcdir/acl.inc");
require_once("../../../custom/code_types.inc.php");
require_once("$srcdir/options.inc.php");

 // Check authorization.
 $thisauth = acl_check('patients', 'med');
 if ($thisauth) {
  $tmp = getPatientData($pid, "squad");
  if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
   $thisauth = 0;
 }
 if (!$thisauth) die(xl('Not authorized'));
 
 // mdsupport : Set the enddate to today if quickstop button is used for an issue
 if ($_POST["endissue"]) {
	sqlQuery( "UPDATE lists ".
				"SET enddate=NOW() ".
				"WHERE id=".$_POST["endissue"]
	);
 }
 // mdsupport : end change
 // get issues
 $pres = sqlStatement("SELECT * FROM lists WHERE pid = $pid " .
  "ORDER BY type, begdate");
?>
<html>

<head>
<?php html_header_show();?>

<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>

<title><?php xl('Patient Issues','e'); ?></title>

<script type="text/javascript" src="../../../library/dialog.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.js"></script>

<script language="JavaScript">

// callback from add_edit_issue.php:
function refreshIssue(issue, title) {
    top.restoreSession();
    location.reload();
}

function dopclick(id) {
    <?php if ($thisauth == 'write'): ?>
    dlgopen('add_edit_issue.php?issue=' + id, '_blank', 550, 400);
    <?php else: ?>
    alert("<?php xl('You are not authorized to add/edit issues','e'); ?>");
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

// Process click on status end.
function statclick(id) {
	var f = document.getElementById("endissue");
    f.value = id;
}
</script>

</head>

<body class="body_top">
<div id='patient_stats'>

<form method='post' action='stats_full.php' onsubmit='return top.restoreSession()'>
<input type="hidden" name="endissue" id="endissue" />

<table>

<?php
$encount = 0;
$lasttype = "";
$first = 1; // flag for first section
$something = 0; // flag that there is data to show
// If empty
if (sqlNumRows($pres) < 1) {
  echo "<b>" . htmlspecialchars( xl("No Issues"), ENT_NOQUOTES) . "</b><br>";
}
while ($row = sqlFetchArray($pres)) {
    $something = 1;
    if ($lasttype != $row['type']) {
        $encount = 0;
        $lasttype = $row['type'];

        $disptype = $ISSUE_TYPES[$lasttype][0];

        if ($first) {
          $first = 0;
        }
        else {
          echo "</table>";
        }

        echo "  <b>$disptype</b>\n";

        echo " <table style='margin-bottom:1em;text-align:center'>";
        // Show header for each category
        ?>
        <tr class='head'>
          <th><?php xl('Title','e'); ?></th>
          <th><?php xl('Begin','e'); ?></th>
          <th><?php xl('End','e'); ?></th>
          <th><?php xl('Diag','e'); ?></th>
          <th><?php echo htmlspecialchars(xl('Status'),ENT_NOQUOTES); ?></th>
          <th><?php xl('Occurrence','e'); ?></th>
          <?php if ($lasttype == "allergy") { ?>
            <th><?php echo htmlspecialchars( xl('Reaction'), ENT_NOQUOTES); ?></th>
          <?php } ?>
          <?php if ($GLOBALS['athletic_team']) { ?>
            <th><?php xl('Missed','e'); ?></th>
          <?php } else { ?>
            <th><?php xl('Referred By','e'); ?></th>
          <?php } ?>
          <th><?php xl('Comments','e'); ?></th>
          <th><?php xl('Enc','e'); ?></th>
        </tr>
      <?php

    }

    $rowid = $row['id'];

    $disptitle = trim($row['title']) ? $row['title'] : "[Missing Title]";

    $ierow = sqlQuery("SELECT count(*) AS count FROM issue_encounter WHERE " .
      "list_id = $rowid");

    // encount is used to toggle the color of the table-row output below
    ++$encount;
    $bgclass = (($encount & 1) ? "bg1" : "bg2");

    // look up the diag codes
    $codetext = "";
    if ($row['diagnosis'] != "") {
        $diags = explode(";", $row['diagnosis']);
        foreach ($diags as $diag) {
            $codedesc = lookup_code_descriptions($diag);
            $codetext .= $diag." (".$codedesc.")<br>";
        }
    }

    // calculate the status
    if ($row['outcome'] == "1" && $row['enddate'] != NULL) {
      // Resolved
      $statusCompute = generate_display_field(array('data_type'=>'1','list_id'=>'outcome'), $row['outcome']);
    }
    else if($row['enddate'] == NULL) {
      $statusCompute = xl("Active");
    }
    else {
      $statusCompute = xl("Inactive");
    }

    // output the TD row of info
    if ($row['enddate'] == NULL) {
      echo " <tr class='$bgclass detail statrow' style='color:red;font-weight:bold' id='$rowid'>\n";
    }
    else {
      echo " <tr class='$bgclass detail statrow' id='$rowid'>\n";
    }
    echo "  <td style='text-align:left'>$disptitle</td>\n";
    echo "  <td>" . $row['begdate'] . "&nbsp;</td>\n";
    echo "  <td>" . $row['enddate'] . "&nbsp;</td>\n";
    echo "  <td>" . $codetext . "</td>\n";
    echo "  <td>" . htmlspecialchars($statusCompute,ENT_NOQUOTES) . "&nbsp";
    if (($thisauth == 'write') && ($row['enddate'] == NULL)) {
    	echo "<input class='switchStat' type='image' src='../../../images/deleteBtn.png' id='$rowid'>";
    }
    echo "</td>\n";
    echo "  <td class='nowrap'>";
    echo generate_display_field(array('data_type'=>'1','list_id'=>'occurrence'), $row['occurrence']);
    echo "</td>\n";
    if ($lasttype == "allergy") {
      echo "  <td>" . htmlspecialchars($row['reaction'],ENT_NOQUOTES) . "&nbsp;</td>\n";
    }
    if ($GLOBALS['athletic_team']) {
        echo "  <td class='center'>" . $row['extrainfo'] . "</td>\n"; // games missed
    }
    else {
        echo "  <td>" . $row['referredby'] . "</td>\n";
    }
    echo "  <td>" . $row['comments'] . "</td>\n";
    echo "  <td id='e_$rowid' class='noclick center' title='" . xl('View related encounters') . "'>";
    echo "  <input type='button' value='" . $ierow['count'] . "' class='editenc' id='".$rowid."' />";
    echo "  </td>";
    echo " </tr>\n";
}
if ($something) echo "</table>";
?>

</table>

<div style="text-align:center" class="buttons">
 <p>
 <input type='button' value='<?php xl('Add Issue','e'); ?>' id='addissue' class='btn' /> &nbsp;
 <input type='button' value='<?php xl('Add Encounter','e'); ?>' id='newencounter' class='btn' /> &nbsp;
 <input type='button' value='<?php xl('To History','e'); ?>' id='history' class='btn' /> &nbsp;
 <input type='button' value='<?php xl('Back','e'); ?>' id='back' class='btn' />
 </p>
</div>

</form>
</div> <!-- end patient_stats -->

</body>

<script language="javascript">
// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    $(".statrow").mouseover(function() { $(this).toggleClass("highlight"); });
    $(".statrow").mouseout(function() { $(this).toggleClass("highlight"); });
    <?php if ($thisauth == 'write') { ?>
    $(".switchStat").click(function(event) { statclick(this.id); event.stopPropagation(); });
    <?php } ?>
    $(".statrow").click(function() { dopclick(this.id); });
    $(".editenc").click(function(event) { doeclick(this.id); event.stopPropagation(); });
    $("#addissue").click(function() { dopclick(0); });
    $("#newencounter").click(function() { newEncounter(); });
    $("#history").click(function() { GotoHistory(); });
    $("#back").click(function() { GoBack(); });
});

var GotoHistory = function() {
    top.restoreSession();
<?php if ($GLOBALS['concurrent_layout']): ?>
    parent.left_nav.setRadio(window.name,'his');
    location.href='../history/history_full.php';
<?php else: ?>
    location.href='../history/history_full.php';
<?php endif; ?>
};

var GoBack = function () {
    top.restoreSession();
<?php if ($GLOBALS['concurrent_layout']): ?>
    parent.left_nav.setRadio(window.name,'dem');
    location.href='demographics.php';
<?php else: ?>
    location.href="patient_summary.php";
<?php endif; ?>
};

</script>

</html>
