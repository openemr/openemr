<?php

/*
 * PSYCHIATRISCH ONDERZOEK
 * Report of Research psihiatric - Dutch specific form
 * Version: 1.0 - 29-03-2008
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
require_once("$srcdir/api.inc");
require_once("$srcdir/patient.inc");

use OpenEMR\Core\Header;

formHeader("Form: psychiatrisch_onderzoek");
$returnurl = 'encounter_top.php';

$result = getPatientData($pid, "fname,lname,pid,pubpid,phone_home,pharmacy_id,DOB,DATE_FORMAT(DOB,'%Y%m%d') as DOB_YMD");
$provider_results = sqlQuery("select * from users where username=?", array($_SESSION["authUser"]));

////////////////////////////////////////////////////////////////////
// Function:    getPatientDateOfLastEncounter
function getPatientDateOfLastEncounter($nPid)
{
  // get date of last encounter F103 or F153
    $strEventDate = sqlQuery("SELECT MAX(pc_eventDate) AS max
                  FROM openemr_postcalendar_events
                  WHERE pc_pid = ?
                  AND pc_apptstatus = '@'
                  AND ( pc_catid = 17 OR pc_catid = 25 OR pc_catid = 13 OR pc_catid = 26 )
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
$vectAutosavePO = sqlQuery("SELECT id, autosave_flag, autosave_datetime FROM form_psychiatrisch_onderzoek
                            WHERE pid = ?
                            AND groupname= ?
                            AND user=? AND
                            authorized=? AND activity=1
                            AND autosave_flag=1
                            ORDER by id DESC limit 1", array($_SESSION["pid"], $_SESSION["authProvider"], $_SESSION["authUser"], $userauthorized));

//fetch data from INTAKE-VERSLAG
$obj_iv = formFetch("form_intakeverslag", $vectIntakeverslagQuery['id']);
// fetch data from PSYCHIATRISCH ONDERZOEK
$obj_po = formFetch("form_psychiatrisch_onderzoek", $vectAutosavePO['id']);
/////////////////
// here we mix the data
// Reden van aanmelding
if ($obj_po['reden_van_aanmelding'] != '') {
    $obj['reden_van_aanmelding'] = $obj_po['reden_van_aanmelding'];
} elseif ($obj_iv['reden_van_aanmelding'] != '') {
    $obj['reden_van_aanmelding'] = $obj_iv['reden_van_aanmelding'];
} else {
    $obj['reden_van_aanmelding'] = '';
}

// Conclusie van intake
if ($obj_po['conclusie_van_intake'] != '') {
    $obj['conclusie_van_intake'] = $obj_po['conclusie_van_intake'];
} elseif ($obj_iv['beschrijvende_conclusie'] != '') {
    $obj['conclusie_van_intake'] = $obj_iv['beschrijvende_conclusie'];
} else {
    $obj['conclusie_van_intake'] = '';
}

// Medicatie - local
if ($obj_po['medicatie'] != '') {
    $obj['medicatie'] = $obj_po['medicatie'];
} else {
    $obj['medicatie'] = '';
}

// Anamnese - local
if ($obj_po['anamnese'] != '') {
    $obj['anamnese'] = $obj_po['anamnese'];
} else {
    $obj['anamnese'] = '';
}

// Psychiatrisch onderzoek i.e.z. - local
if ($obj_po['psychiatrisch_onderzoek'] != '') {
    $obj['psychiatrisch_onderzoek'] = $obj_po['psychiatrisch_onderzoek'];
} else {
    $obj['psychiatrisch_onderzoek'] = '';
}

// Beschrijvende conclusie
if ($obj_po['beschrijvende_conclusie'] != '') {
    $obj['beschrijvende_conclusie'] = $obj_po['beschrijvende_conclusie'];
} elseif ($obj_iv['beschrijvende_conclusie'] != '') {
    $obj['beschrijvende_conclusie'] = $obj_iv['beschrijvende_conclusie'];
} else {
    $obj['beschrijvende_conclusie'] = '';
}

// Behandelvoorstel
if ($obj_po['behandelvoorstel'] != '') {
    $obj['behandelvoorstel'] = $obj_po['behandelvoorstel'];
} elseif ($obj_iv['behandelvoorstel'] != '') {
    $obj['behandelvoorstel'] = $obj_iv['behandelvoorstel'];
} else {
    $obj['behandelvoorstel'] = '';
}

$tmpDate = stripslashes($obj["datum_onderzoek"]);
if ($tmpDate && $tmpDate != '0000-00-00 00:00:00') {
    $m_strEventDate = $tmpDate;
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

if ($vectAutosavePO['id']) {
    $psychiatrisch_onderzoek_id = $vectAutosavePO['id'];
} else {
    $psychiatrisch_onderzoek_id = "0";
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
              url: "../../forms/psychiatrisch_onderzoek/delete_autosave.php",
              data: "id=" + <?php echo js_url($psychiatrisch_onderzoek_id); ?>
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

  var a_datum_onderzoek = $("#datum_onderzoek").val();
  var a_reden_van_aanmelding = $("#reden_van_aanmelding").val();
  var a_conclusie_van_intake = $("#conclusie_van_intake").val();
  var a_medicatie = $("#medicatie").val();
  var a_anamnese = $("#anamnese").val();
  var a_psychiatrisch_onderzoek = $("#psychiatrisch_onderzoek").val();
  var a_beschrijvende_conclusie = $("#beschrijvende_conclusie").val();
  var a_behandelvoorstel = $("#behandelvoorstel").val();

  if( a_datum_onderzoek.length > 0 || a_reden_van_aanmelding.length > 0 )
  {
    $.ajax(
            {
              type: "POST",
              url: "../../forms/psychiatrisch_onderzoek/autosave.php",
              data: "id=" + <?php echo js_url($psychiatrisch_onderzoek_id); ?> +
                        "&datum_onderzoek=" + encodeURIComponent($("#datum_onderzoek").val()) +
                        "&reden_van_aanmelding=" + encodeURIComponent(a_reden_van_aanmelding) +
                        "&conclusie_van_intake=" + encodeURIComponent(a_conclusie_van_intake) +
                        "&medicatie=" + encodeURIComponent(a_medicatie) +
                        "&anamnese=" + encodeURIComponent(a_anamnese) +
                        "&psychiatrisch_onderzoek=" + encodeURIComponent(a_psychiatrisch_onderzoek) +
                        "&beschrijvende_conclusie=" + encodeURIComponent(a_beschrijvende_conclusie) +
                        "&behandelvoorstel=" + encodeURIComponent(a_behandelvoorstel)
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
<form method=post action="<?php echo $rootdir;?>/forms/psychiatrisch_onderzoek/save.php?mode=new&saveid=<?php echo attr_url($psychiatrisch_onderzoek_id); ?>" name="my_form">
<span class="title"><?php echo xlt('Psychiatric Examination'); ?></span><br /><br />

<table>
<tr>
<td><?php echo xlt('Examination Date'); ?>:</td><td>
<input type='text' class='datepicker' name='datum_onderzoek' id='datum_onderzoek' size='10' value='<?php echo attr($m_strEventDate); ?>'
          title='<?php echo xla('Examination Date'); ?>: yyyy-mm-dd'></input>


<?php

?></td>
</tr>
</table>

<br /><span class=text><?php echo xlt('Reason for Visit'); ?></span><br />
<textarea cols=80 rows=5 wrap=virtual name="reden_van_aanmelding" id="reden_van_aanmelding"><?php echo text($obj["reden_van_aanmelding"]);?></textarea><br />
<br /><span class=text><?php echo xlt('Intake Conclusion'); ?></span><br />
<textarea cols=80 rows=5 wrap=virtual name="conclusie_van_intake" id="conclusie_van_intake"><?php echo text($obj["conclusie_van_intake"]);?></textarea><br />
<br /><span class=text><?php echo xlt('Medications'); ?></span><br />
<textarea cols=80 rows=5 wrap=virtual name="medicatie" id="medicatie"><?php echo text($obj["medicatie"]);?></textarea><br />

<br /><span class=text><?php echo xlt('History'); ?></span><br />
<textarea cols=80 rows=10 wrap=virtual name="anamnese" id="anamnese"><?php echo text($obj["anamnese"]);?></textarea><br />

<br /><span class=text><?php echo xlt('Psychiatric Examination'); ?></span><br />
<textarea cols=80 rows=5 wrap=virtual name="psychiatrisch_onderzoek" id="psychiatrisch_onderzoek"><?php echo text($obj["psychiatrisch_onderzoek"]);?></textarea><br />
<br /><span class=text><?php echo xlt('Conclusions'); ?></span><br />
<textarea cols=80 rows=5 wrap=virtual name="beschrijvende_conclusie" id="beschrijvende_conclusie"><?php echo text($obj["beschrijvende_conclusie"]);?></textarea><br />
<br /><span class=text><?php echo xlt('Treatment Plan'); ?></span><br />
<textarea cols=80 rows=5 wrap=virtual name="behandelvoorstel" id="behandelvoorstel"><?php echo text($obj["behandelvoorstel"]);?></textarea><br />



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
