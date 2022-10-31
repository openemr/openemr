<?php

/*
 * brief_aan_verwijzer
 * letter to verwijzer - Dutch specific form
 * Version: 1.0 - 30-03-2008
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://opensource.org/licenses/gpl-license.php>.
 *
 * @package   OpenEMR
 * @author    Larry Lart
 * @link      http://www.open-emr.org
 */

require_once("../../globals.php");
require_once("$srcdir/api.inc.php");
require_once("$srcdir/patient.inc.php");

use OpenEMR\Core\Header;

formHeader("Form: brief_aan_verwijzer");
$returnurl = 'encounter_top.php';

$result = getPatientData($pid, "fname,lname,pid,pubpid,phone_home,sex,pharmacy_id,DOB,DATE_FORMAT(DOB,'%Y%m%d') as DOB_YMD");
$provider_results = sqlQuery("select * from users where username= ?", array($_SESSION["authUser"]));

////////////////////////////////////////////////////////////////////
// Function:    getPatientDateOfLastEncounter
function getPatientDateOfLastEncounter($nPid)
{
  // get date of last encounter no codes
    $strEventDate = sqlQuery("SELECT MAX(pc_eventDate) AS max
                  FROM openemr_postcalendar_events
                  WHERE pc_pid = ?
                  AND pc_apptstatus = '@'
                  AND pc_eventDate >= '2007-01-01'", array($nPid));

  // now check if there was a previous encounter
    if ($strEventDate['max'] != "") {
        return( $strEventDate['max'] );
    } else {
        return( "00-00-0000" );
    }
}

$m_strEventDate = getPatientDateOfLastEncounter($result['pid']);

// get last saved id for intakeverslag
$vectIntakeverslagQuery = sqlQuery("SELECT id FROM form_intakeverslag
                            WHERE pid = ?
                            AND groupname= ?
                            AND user=? AND
                            authorized=? AND activity=1
                            AND autosave_flag=0
                            ORDER by id DESC limit 1", array($_SESSION["pid"], $_SESSION["authProvider"], $_SESSION["authUser"], $userauthorized));

// get autosave id for Psychiatrisch Onderzoek
$vectPO = sqlQuery("SELECT id FROM form_psychiatrisch_onderzoek
                            WHERE pid = ?
                            AND groupname= ?
                            AND user=? AND
                            authorized=? AND activity=1
                            AND autosave_flag=0
                            ORDER by id DESC limit 1", array($_SESSION["pid"], $_SESSION["authProvider"], $_SESSION["authUser"], $userauthorized));

// get autosave id for Psychiatrisch Onderzoek
$vectAutosaveBAV = sqlQuery("SELECT id, autosave_flag, autosave_datetime FROM form_brief_aan_verwijzer
                            WHERE pid = ?
                            AND groupname= ?
                            AND user=? AND
                            authorized=? AND activity=1
                            AND autosave_flag=0
                            ORDER by id DESC limit 1", array($_SESSION["pid"], $_SESSION["authProvider"], $_SESSION["authUser"], $userauthorized));

//fetch data from INTAKE-VERSLAG
$obj_iv = formFetch("form_intakeverslag", $vectIntakeverslagQuery['id']);
// fetch data from PSYCHIATRISCH ONDERZOEK
$obj_po = formFetch("form_psychiatrisch_onderzoek", $vectPO['id']);
// fetch data from brief_aan_verwijzer
$obj_bav = formFetch("form_brief_aan_verwijzer", $vectAutosaveBAV['id']);

/////////////////
// here we mix the data

// Introductie - local
// create the inroductie form
if ($obj_bav['introductie'] != '') {
    $obj['introductie'] = $obj_bav['introductie'];
} else {
    $obj['introductie'] = xl("Since", "", "", " ") . $m_strEventDate . xl("we have seen your above patient for evaluation and treatment at our outpatient psychiatry clinic. Thank you for this referral.", "", " ");
}

// Reden van aanmelding
if ($obj_bav['reden_van_aanmelding'] != '') {
    $obj['reden_van_aanmelding'] = $obj_bav['reden_van_aanmelding'];
} elseif ($obj_iv['reden_van_aanmelding'] != '') {
    $obj['reden_van_aanmelding'] = $obj_iv['reden_van_aanmelding'];
} else {
    $obj['reden_van_aanmelding'] = '';
}

// Anamnese
if ($obj_bav['anamnese'] != '') {
    $obj['anamnese'] = $obj_bav['anamnese'];
} elseif ($obj_iv['klachten_probleemgebieden'] != '') {
    $obj['anamnese'] = $obj_iv['klachten_probleemgebieden'];
} else {
    $obj['anamnese'] = '';
}

// Psychiatrisch onderzoek
if ($obj_bav['psychiatrisch_onderzoek'] != '') {
    $obj['psychiatrisch_onderzoek'] = $obj_bav['psychiatrisch_onderzoek'];
} elseif ($obj_po['psychiatrisch_onderzoek'] != '') {
    $obj['psychiatrisch_onderzoek'] = $obj_po['psychiatrisch_onderzoek'];
} else {
    $obj['psychiatrisch_onderzoek'] = '';
}

// Beschrijvend conclusie
if ($obj_bav['beschrijvend_conclusie'] != '') {
    $obj['beschrijvend_conclusie'] = $obj_bav['beschrijvend_conclusie'];
} elseif ($obj_po['beschrijvende_conclusie'] != '') {
    $obj['beschrijvend_conclusie'] = $obj_po['beschrijvende_conclusie'];
} else {
    $obj['beschrijvend_conclusie'] = '';
}

// Advies/beleid
if ($obj_bav['advies_beleid'] != '') {
    $obj['advies_beleid'] = $obj_bav['advies_beleid'];
} elseif ($obj_po['behandelvoorstel'] != '') {
    $obj['advies_beleid'] = $obj_po['behandelvoorstel'];
} else {
    $obj['advies_beleid'] = '';
}


?>

<html>
<head>
    <?php Header::setupHeader('datetime-picker'); ?>
    <style>
    body {
        font-family: sans-serif;
        font-size: 0.8125rem;
        font-weight: normal;
    }
    .dehead {
        font-family: sans-serif;
        font-size: 0.8125rem;
        font-weight: bold;
        padding-left: 3px;
        padding-right: 3px;
    }
    .detail {
        font-family: sans-serif;
        font-size: 0.8125rem;
        font-weight: normal;
        padding-left: 3px;
        padding-right: 3px;
    }
</style>
</head>

<body class="body_top">

<?php

if ($vectAutosaveBAV['id']) {
    $brief_aan_verwijzer_id = $vectAutosaveBAV['id'];
} else {
    $brief_aan_verwijzer_id = "0";
}

?>
<script>
$(function () {
        autosave();
        $('.datepicker').datetimepicker({
            <?php $datetimepicker_timepicker = false; ?>
            <?php $datetimepicker_showseconds = false; ?>
            <?php $datetimepicker_formatInput = false; ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
        });
                        });

function delete_autosave( )
{
  if( confirm(<?php echo xlj('Are you sure you want to completely remove this form?'); ?>) )
  {
    $.ajax(
            {
              type: "POST",
              url: "../../forms/brief_aan_verwijzer/delete_autosave.php",
              data: "id=" + <?php echo js_escape($brief_aan_verwijzer_id); ?>
                        ,
                                cache: false,
                                success: function( message )
                {
                     $("#timestamp").empty().append(message);
                }
            });
    return true;

  } else
  {
    return false;
  }

}

function autosave( )
{
  var t = setTimeout("autosave()", 20000);

  var a_introductie = $("#introductie").val();
  var a_reden_van_aanmelding = $("#reden_van_aanmelding").val();
  var a_anamnese = $("#anamnese").val();
  var a_psychiatrisch_onderzoek = $("#psychiatrisch_onderzoek").val();
  var a_beschrijvend_conclusie = $("#beschrijvend_conclusie").val();
  var a_advies_beleid = $("#advies_beleid").val();

  if( a_introductie.length > 0 || a_reden_van_aanmelding.length > 0 )
  {
    $.ajax(
            {
              type: "POST",
              url: "../../forms/brief_aan_verwijzer/autosave.php",
              data: "id=" + <?php echo js_url($brief_aan_verwijzer_id); ?> +
                        "&introductie=" + encodeURIComponent($("#introductie").val()) +
                        "&reden_van_aanmelding=" + encodeURIComponent(a_reden_van_aanmelding) +
                        "&anamnese=" + encodeURIComponent(a_anamnese) +
                        "&psychiatrisch_onderzoek=" + encodeURIComponent(a_psychiatrisch_onderzoek) +
                        "&beschrijvend_conclusie=" + encodeURIComponent(a_beschrijvend_conclusie) +
                        "&advies_beleid=" + encodeURIComponent(a_advies_beleid)
                        ,
                                cache: false,
                                success: function( message )
                {
                                        $("#timestamp").empty().append(message);
                }
            });
  }

}

</script>

<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<form method=post action="<?php echo $rootdir;?>/forms/brief_aan_verwijzer/save.php?mode=new&saveid=<?php echo attr_url($brief_aan_verwijzer_id); ?>" name="my_form">
<span class="title"><?php echo xlt('Psychiatric Brief Letter'); ?></span><br /><br />

<br /><span class=text><?php echo xlt('Introduction'); ?></span><br />
<textarea cols=80 rows=3 wrap=virtual name="introductie" id="introductie"><?php echo text($obj["introductie"]); ?></textarea><br />

<br /><span class=text><?php echo xlt('Reason for Visit'); ?></span><br />
<textarea cols=80 rows=5 wrap=virtual name="reden_van_aanmelding" id="reden_van_aanmelding"><?php echo text($obj["reden_van_aanmelding"]); ?></textarea><br />
<br /><span class=text><?php echo xlt('History'); ?></span><br />
<textarea cols=80 rows=5 wrap=virtual name="anamnese" id="anamnese"><?php echo text($obj["anamnese"]); ?></textarea><br />
<br /><span class=text><?php echo xlt('Psychiatric Examination'); ?></span><br />
<textarea cols=80 rows=5 wrap=virtual name="psychiatrisch_onderzoek" id="psychiatrisch_onderzoek"><?php echo text($obj["psychiatrisch_onderzoek"]); ?></textarea><br />
<br /><span class=text><?php echo xlt('Conclusions'); ?></span><br />
<textarea cols=80 rows=5 wrap=virtual name="beschrijvend_conclusie" id="beschrijvend_conclusie"><?php echo text($obj["beschrijvend_conclusie"]); ?></textarea><br />
<br /><span class=text><?php echo xlt('Treatment Plan'); ?></span><br />
<textarea cols=80 rows=5 wrap=virtual name="advies_beleid" id="advies_beleid"><?php echo text($obj["advies_beleid"]); ?></textarea><br />

<table><tr>

<?php
// here we fill in the header above with patient name etc ? ??? - move above

?>
</tr></table>

<br /><br />
<a href="javascript:document.my_form.submit();" class="link_submit">[<?php echo xlt('Save'); ?>]</a>
<br />
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link_submit" onclick="delete_autosave();top.restoreSession()">[<?php echo xlt('Don\'t Save'); ?>]</a>
</form>


<div id="timestamp"></div>

<?php
formFooter();
?>
