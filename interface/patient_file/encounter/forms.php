<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/calendar.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/formatting.inc.php");
?>
<html>

<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<!-- supporting javascript code -->
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.js"></script>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js"></script>



<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" type="text/css" href="../../../library/js/fancybox/jquery.fancybox-1.2.6.css" media="screen" />
<style type="text/css">@import url(../../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../../library/textformat.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../../library/dynarch_calendar_setup.js"></script>
<script type="text/javascript" src="../../../library/dialog.js"></script>
<script type="text/javascript" src="../../../library/js/jquery.1.3.2.js"></script>
<script type="text/javascript" src="../../../library/js/common.js"></script>
<script type="text/javascript" src="../../../library/js/fancybox/jquery.fancybox-1.2.6.js"></script>

<script language="JavaScript">

 // Process click on Delete link.
 function deleteme() {
  dlgopen('../deleter.php?encounterid=<?php echo $encounter; ?>', '_blank', 500, 450);
  return false;
 }

 // Called by the deleter.php window on a successful delete.
 function imdeleted() {
<?php if ($GLOBALS['concurrent_layout']) { ?>
  parent.parent.left_nav.clearEncounter();
<?php } else { ?>
  top.restoreSession();
  top.Title.location.href = '../patient_file/encounter/encounter_title.php';
  top.Main.location.href  = '../patient_file/encounter/patient_encounter.php?mode=new';
<?php } ?>
 }

</script>

<script language="javascript">
function expandcollapse(atr){
	if(atr == "expand") {
		for(i=1;i<15;i++){
			var mydivid="divid_"+i;var myspanid="spanid_"+i;
				var ele = document.getElementById(mydivid);	var text = document.getElementById(myspanid);
				ele.style.display = "block";text.innerHTML = "<?php xl('Collapse','e'); ?>";
		}
  	}
	else {
		for(i=1;i<15;i++){
			var mydivid="divid_"+i;var myspanid="spanid_"+i;
				var ele = document.getElementById(mydivid);	var text = document.getElementById(myspanid);
				ele.style.display = "none";	text.innerHTML = "<?php xl('Expand','e'); ?>";
		}
	}

}

function divtoggle(spanid, divid) {
	var ele = document.getElementById(divid);
	var text = document.getElementById(spanid);
	if(ele.style.display == "block") {
		ele.style.display = "none";
		text.innerHTML = "<?php xl('Expand','e'); ?>";
  	}
	else {
		ele.style.display = "block";
		text.innerHTML = "<?php xl('Collapse','e'); ?>";
	}
}
</script>

<style type="text/css">
    div.tab {
        min-height: 50px;
        padding:8px;
    }

    div.form_header_controls {
        float:left;margin-bottom:2px;
    }

    div.form_header {
        float:left;
        margin-left:6px;
    }
</style>

</head>

<body class="body_top">

<div id="encounter_forms">


<?php
$dateres = getEncounterDateByEncounter($encounter);
$encounter_date = date("Y-m-d",strtotime($dateres["date"]));
?>

<div style='float:left'>
<span class="title"><?php echo oeFormatShortDate($encounter_date) . " " . xl("Encounter"); ?> </span>
<?php
$auth_notes_a  = acl_check('encounters', 'notes_a');
$auth_notes    = acl_check('encounters', 'notes');
$auth_relaxed  = acl_check('encounters', 'relaxed');

if (is_numeric($pid)) {
    // Check for no access to the patient's squad.
    $result = getPatientData($pid, "fname,lname,squad");
    echo xl('for','',' ',' ') . $result['fname'] . " " . $result['lname'];
    if ($result['squad'] && ! acl_check('squads', $result['squad'])) {
        $auth_notes_a = $auth_notes = $auth_relaxed = 0;
    }
    // Check for no access to the encounter's sensitivity level.
    $result = sqlQuery("SELECT sensitivity FROM form_encounter WHERE " .
                        "pid = '$pid' AND encounter = '$encounter' LIMIT 1");
    if ($result['sensitivity'] && !acl_check('sensitivities', $result['sensitivity'])) {
        $auth_notes_a = $auth_notes = $auth_relaxed = 0;
    }
}
?>
</div>
<div style='float:left;margin-left:10px'>
<?php if (acl_check('admin', 'super')) { ?>
    <a href='toggledivs(this.id,this.id);' class='css_button' onclick='return deleteme()'><span><?php echo xl('Delete') ?></span></a>
	&nbsp;&nbsp;&nbsp;<a href="#" onClick='expandcollapse("expand");' style="font-size:80%;"><?php xl('Expand All','e'); ?></a>
	&nbsp;&nbsp;&nbsp;<a  style="font-size:80%;" href="#" onClick='expandcollapse("collapse");'><?php xl('Collapse All','e'); ?></a>
<?php } ?>
</div>
<br/>
<br/>

<?php
  if ($result = getFormByEncounter($pid, $encounter, "id, date, form_id, form_name, formdir, user, deleted")) {
    echo "<table width='100%' id='partable'>";
	$divnos=1;
    foreach ($result as $iter) {
        $formdir = $iter['formdir'];

        // skip forms whose 'deleted' flag is set to 1
        if ($iter['deleted'] == 1) continue;

        // Skip forms that we are not authorized to see.
        if (($auth_notes_a) ||
            ($auth_notes && $iter['user'] == $_SESSION['authUser']) ||
            ($auth_relaxed && ($formdir == 'sports_fitness' || $formdir == 'podiatry'))) ;
        else continue;

        // $form_info = getFormInfoById($iter['id']);
        if (strtolower(substr($iter['form_name'],0,5)) == 'camos') {
            //CAMOS generates links from report.php and these links should
            //be clickable without causing view.php to come up unexpectedly.
            //I feel that the JQuery code in this file leading to a click
            //on the report.php content to bring up view.php steps on a
            //form's autonomy to generate it's own html content in it's report
            //but until any other form has a problem with this, I will just
            //make an exception here for CAMOS and allow it to carry out this
            //functionality for all other forms.  --Mark
	        echo '<tr title="' . xl('Edit form') . '" '.
       		      'id="'.$formdir.'~'.$iter['form_id'].'">';
        } else {
            echo '<tr title="' . xl('Edit form') . '" '.
                  'id="'.$formdir.'~'.$iter['form_id'].'" class="text onerow">';
        }
        $user = getNameFromUsername($iter['user']);

        $form_name = ($formdir == 'newpatient') ? xl('Patient Encounter') : xl_form_title($iter['form_name']);

        echo "<tr>";
        echo "<td style='border-bottom:1px solid'>";
        // a link to edit the form
        echo "<div class='form_header_controls'>";
        echo "<a target='".
                ($GLOBALS['concurrent_layout'] ? "_parent" : "Main") .
                "' href='$rootdir/patient_file/encounter/view_form.php?" .
                "formname=" . $formdir . "&id=" . $iter['form_id'] .
                "' onclick='top.restoreSession()' class='css_button_small'><span>" . xl('Edit') . "</span></a>";

        if (acl_check('admin', 'super') ) {
            if ( $formdir != 'newpatient') {
                // a link to delete the form from the encounter
                echo "<a target='".
                    ($GLOBALS['concurrent_layout'] ? "_parent" : "Main") .
                    "' href='$rootdir/patient_file/encounter/delete_form.php?" .
                    "formname=" . $formdir .
                    "&id=" . $iter['id'] .
                    "&encounter=". $encounter.
                    "&pid=".$pid.
                    "' class='css_button_small' title='" . xl('Delete this form') . "' onclick='top.restoreSession()'><span>" . xl('Delete') . "</span></a>";
            } else {
                ?><a href='javascript:;' class='css_button_small' style='color:gray'><span><?php xl('Delete','e'); ?></span></a><?php
            }
        }

        echo "<div class='form_header'>";
        echo "<a href='#' onclick='divtoggle(\"spanid_$divnos\",\"divid_$divnos\");' class='small' id='aid_$divnos'><b>$form_name</b> <span class='text'>by " . $user['fname'] . "  " . $user['lname'] . "</span> (<span id=spanid_$divnos class=\"indicator\">" . xl('Collapse') . "</span>)</a></div>";
        echo "</td>\n";
        echo "</tr>";
        echo "<tr>";
        echo "<td valign='top' class='formrow'><div class='tab' id='divid_$divnos' style='display:block'>";

        // Use the form's report.php for display.  Forms with names starting with LBF
        // are list-based forms sharing a single collection of code.
        //
        if (substr($formdir,0,3) == 'LBF') {
          include_once($GLOBALS['incdir'] . "/forms/LBF/report.php");
          call_user_func("lbf_report", $pid, $encounter, 2, $iter['form_id'], $formdir);
        }
        else  {
          include_once($GLOBALS['incdir'] . "/forms/$formdir/report.php");
          call_user_func($formdir . "_report", $pid, $encounter, 2, $iter['form_id']);
        }

        echo "</div></td></tr>";
		$divnos=$divnos+1;
    }
    echo "</table>";
}
?>

<?php if ($GLOBALS['athletic_team'] && $GLOBALS['concurrent_layout'] == 2) { ?>
<script language='JavaScript'>
 // If this is the top frame then show the encounters list in the bottom frame.
 var n  = parent.parent.left_nav;
 var nf = n.document.forms[0];
 if (parent.window.name == 'RTop' && nf.cb_bot.checked) {
  var othername = 'RBot';
  n.setRadio(othername, 'ens');
  n.loadFrame('ens1', othername, 'patient_file/history/encounters.php');
 }
</script>
<?php } ?>

</div> <!-- end large encounter_forms DIV -->
</body>

<script language="javascript">
// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    $(".save").click(function() { top.restoreSession(); document.my_form.submit(); });
    $(".dontsave").click(function() { location.href='<?php echo "$rootdir/patient_file/encounter/$returnurl";?>'; });

    $(".onerow").mouseover(function() { $(this).toggleClass("highlight"); });
    $(".onerow").mouseout(function() { $(this).toggleClass("highlight"); });
    $(".onerow").click(function() { GotoForm(this); });

    // $(".deleteme").click(function(evt) { deleteme(); evt.stopPropogation(); });

    var GotoForm = function(obj) {
        var parts = $(obj).attr("id").split("~");
        top.restoreSession();
        <?php if ($GLOBALS['concurrent_layout']): ?>
        parent.location.href = "<?php echo $rootdir; ?>/patient_file/encounter/view_form.php?formname="+parts[0]+"&id="+parts[1];
        <?php else: ?>
        top.Main.location.href = "<?php echo $rootdir; ?>/patient_file/encounter/view_form.php?formname="+parts[0]+"&id="+parts[1];
        <?php endif; ?>
    }
});

</script>

</html>
