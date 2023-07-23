<?php

/*
 * Intakeverslag - view
 * Report of First visit - Dutch specific form
 * Version: 1.0 - 27-03-2008
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

$returnurl = 'encounter_top.php';

$result = getPatientData($pid, "fname,lname,pid,pubpid,phone_home,pharmacy_id,DOB,DATE_FORMAT(DOB,'%Y%m%d') as DOB_YMD");
$provider_results = sqlQuery("select * from users where username= ?", array($_SESSION["authUser"] ));
$age = getPatientAge($result["DOB_YMD"]);

////////////////////////////////////////////////////////////////////
// Function:    getPatientDateOfLastEncounter
function getPatientDateOfLastEncounter($nPid)
{
    $strEventDate = sqlQuery("SELECT MAX(pc_eventDate) AS max
                  FROM openemr_postcalendar_events
                  WHERE pc_pid = ?
                  AND pc_apptstatus = '@'
                  AND ( pc_catid = 12 OR pc_catid = 16 )
                  AND pc_eventDate >= '2007-01-01'", array($nPid));

  // now check if there was a previous encounter
    if ($strEventDate['max'] != "") {
        return( $strEventDate['max'] );
    } else {
        return( "00-00-0000" );
    }
}

$m_strEventDate = getPatientDateOfLastEncounter($result['pid']);

// get autosave id
$vectAutosave = sqlQuery("SELECT id, autosave_flag, autosave_datetime FROM form_intakeverslag
                             WHERE pid = ?
                            AND groupname= ?
                            AND user=? AND
                            authorized=? AND activity=1
                            AND autosave_flag=1
                            ORDER by id DESC limit 1", array($_SESSION["pid"], $_SESSION["authProvider"], $_SESSION["authUser"], $userauthorized));

//$obj = formFetch("form_intakeverslag", $vectAutosave['id']);

if ($vectAutosave['id'] && $vectAutosave['id'] != "" && $vectAutosave['id'] > 0) {
    $obj = formFetch("form_intakeverslag", $vectAutosave['id']);
} else {
    $obj = formFetch("form_intakeverslag", (int)$_GET["id"]);
}

$tmpDate = stripslashes($obj["intakedatum"]);
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
require_once("$srcdir/api.inc.php");
//$obj = formFetch("form_intakeverslag", (int)$_GET["id"]);
?>

<?php

if ($_GET["id"]) {
    $intakeverslag_id = $_GET["id"];
} else {
    $intakeverslag_id = "0";
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
  if( confirm(<?php echo xlj('Are you sure you want to completely remove this form?'); ?>") )
  {
    $.ajax(
            {
              type: "POST",
              url: "../../forms/intakeverslag/delete_autosave.php",
              data: "id=" + <?php echo js_url($intakeverslag_id); ?>
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

  var a_intakedatum = $("#intakedatum").val();
  var a_reden_van_aanmelding = $("#reden_van_aanmelding").val();
  var a_klachten_probleemgebieden = $("#klachten_probleemgebieden").val();
  var a_hulpverlening_onderzoek = $("#hulpverlening_onderzoek").val();
  var a_hulpvraag_en_doelen = $("#hulpvraag_en_doelen").val();
  var a_bijzonderheden_systeem = $("#bijzonderheden_systeem").val();
  var a_werk_opleiding_vrije_tijdsbesteding = $("#werk_opleiding_vrije_tijdsbesteding").val();
  var a_relatie_kinderen = $("#relatie_kinderen").val();
  var a_somatische_context = $("#somatische_context").val();
  var a_alcohol = $("#alcohol").val();
  var a_drugs = $("#drugs").val();
  var a_roken = $("#roken").val();
  var a_medicatie = $("#medicatie").val();
  var a_familieanamnese = $("#familieanamnese").val();
  var a_indruk_observaties = $("#indruk_observaties").val();
  var a_beschrijvende_conclusie = $("#beschrijvende_conclusie").val();
  var a_behandelvoorstel = $("#behandelvoorstel").val();

  if( a_intakedatum.length > 0 || a_reden_van_aanmelding.length > 0 )
  {
    $.ajax(
            {
              type: "POST",
              url: "../../forms/intakeverslag/autosave.php",
              data: "id=" + <?php echo js_url($intakeverslag_id); ?> +
                        "&intakedatum=" + encodeURIComponent($("#intakedatum").val()) +
                        "&reden_van_aanmelding=" + encodeURIComponent(a_reden_van_aanmelding) +
                        "&klachten_probleemgebieden=" + encodeURIComponent(a_klachten_probleemgebieden) +
                        "&hulpverlening_onderzoek=" + encodeURIComponent(a_hulpverlening_onderzoek) +
                        "&hulpvraag_en_doelen=" + encodeURIComponent(a_hulpvraag_en_doelen) +
                        "&bijzonderheden_systeem=" + encodeURIComponent(a_bijzonderheden_systeem) +
                        "&werk_opleiding_vrije_tijdsbesteding=" + encodeURIComponent(a_werk_opleiding_vrije_tijdsbesteding) +
                        "&relatie_kinderen=" + encodeURIComponent(a_relatie_kinderen) +
                        "&somatische_context=" + encodeURIComponent(a_somatische_context) +
                        "&alcohol=" + encodeURIComponent(a_alcohol) +
                        "&drugs=" + encodeURIComponent(a_drugs) +
                        "&roken=" + encodeURIComponent(a_roken) +
                        "&medicatie=" + encodeURIComponent(a_medicatie) +
                        "&familieanamnese=" + encodeURIComponent(a_familieanamnese) +
                        "&indruk_observaties=" + encodeURIComponent(a_indruk_observaties) +
                        "&beschrijvende_conclusie=" + encodeURIComponent(a_beschrijvende_conclusie) +
                        "&behandelvoorstel=" + encodeURIComponent(a_behandelvoorstel) +
                        "&mode=update"
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


<form method=post action="<?php echo $rootdir?>/forms/intakeverslag/save.php?mode=update&id=<?php echo attr_url($_GET["id"]); ?>" name="my_form">
<span class="title"><?php echo xlt('Psychiatric Intake'); ?></span><br /><br />

<table>
<tr>
<td><?php echo xlt('Intake Date'); ?>:</td><td>
<input type='text' class='datepicker' name='intakedatum' id='intakedatum' size='10' value='<?php echo attr($m_strEventDate); ?>'
          title='<?php echo xla('Intake Date'); ?>: yyyy-mm-dd'></input>


<?php

?></td>
</tr>
</table>

<br /><span class=text><?php echo xlt('Reason for Visit'); ?></span><br />
<textarea cols=80 rows=5 wrap=virtual name="reden_van_aanmelding" id="reden_van_aanmelding"><?php echo text($obj["reden_van_aanmelding"]);?></textarea><br />
<br /><span class=text><?php echo xlt('Problem List'); ?></span><br />
<textarea cols=80 rows=5 wrap=virtual name="klachten_probleemgebieden" id="klachten_probleemgebieden"><?php echo text($obj["klachten_probleemgebieden"]);?></textarea><br />

<br /><span class=text><?php echo xlt('Psychiatric History'); ?></span><br />
<textarea cols=80 rows=10 wrap=virtual name="hulpverlening_onderzoek" id="hulpverlening_onderzoek"><?php echo text($obj["hulpverlening_onderzoek"]);?></textarea><br />

<br /><span class=text><?php echo xlt('Treatment Goals'); ?></span><br />
<textarea cols=80 rows=10 wrap=virtual name="hulpvraag_en_doelen" id="hulpvraag_en_doelen"><?php echo text($obj["hulpvraag_en_doelen"]);?></textarea><br />

<br /><span class=text><?php echo xlt('Specialty Systems'); ?></span><br />
<textarea cols=80 rows=5 wrap=virtual name="bijzonderheden_systeem" id="bijzonderheden_systeem"><?php echo text($obj["bijzonderheden_systeem"]);?></textarea><br />
<br /><span class=text><?php echo xlt('Work/ Education/ Hobbies'); ?></span><br />
<textarea cols=80 rows=5 wrap=virtual name="werk_opleiding_vrije_tijdsbesteding" id="werk_opleiding_vrije_tijdsbesteding"><?php echo text($obj["werk_opleiding_vrije_tijdsbesteding"]);?></textarea><br />
<br /><span class=text><?php echo xlt('Relation(s) / Children'); ?></span><br />
<textarea cols=80 rows=5 wrap=virtual name="relatie_kinderen" id="relatie_kinderen"><?php echo text($obj["relatie_kinderen"]);?></textarea><br />
<br /><span class=text><?php echo xlt('Somatic Context'); ?></span><br />
<textarea cols=80 rows=5 wrap=virtual name="somatische_context" id="somatische_context"><?php echo text($obj["somatische_context"]);?></textarea><br />

<br />
<table>
<tr>
<td align="right"  class=text><?php echo xlt('Alcohol'); ?></td>
<td><input type="text" name="alcohol" size="60" value="<?php echo attr($obj["alcohol"]);?>" id="alcohol"></input></td>
</tr><tr>
<td align="right" class=text><?php echo xlt('Drugs'); ?></td>
<td><input type="text" name="drugs" size="60" value="<?php echo attr($obj["drugs"]);?>" id="drugs"></input></td>
</tr><tr>
<td align="right" class=text><?php echo xlt('Tobacco'); ?></td>
<td><input type="text" name="roken" size="60" value="<?php echo attr($obj["roken"]);?>" id="roken"></input></td>
</tr>
</table>

<br /><span class=text><?php echo xlt('Medications'); ?></span><br />
<textarea cols=80 rows=5 wrap=virtual name="medicatie" id="medicatie"><?php echo text($obj["medicatie"]);?></textarea><br />
<br /><span class=text><?php echo xlt('Family History'); ?></span><br />
<textarea cols=80 rows=5 wrap=virtual name="familieanamnese" id="familieanamnese"><?php echo text($obj["familieanamnese"]);?></textarea><br />
<br /><span class=text><?php echo xlt('Assessment'); ?></span><br />
<textarea cols=80 rows=5 wrap=virtual name="indruk_observaties" id="indruk_observaties"><?php echo text($obj["indruk_observaties"]);?></textarea><br />
<br /><span class=text><?php echo xlt('Conclusions'); ?></span><br />
<textarea cols=80 rows=5 wrap=virtual name="beschrijvende_conclusie" id="beschrijvende_conclusie"><?php echo text($obj["beschrijvende_conclusie"]);?></textarea><br />
<br /><span class=text><?php echo xlt('Treatment Plan'); ?></span><br />
<textarea cols=80 rows=5 wrap=virtual name="behandelvoorstel" id="behandelvoorstel"><?php echo text($obj["behandelvoorstel"]);?></textarea><br />

<table><tr>
<?php
// this to be used/moved above for form header with patient name/etc
?>
</tr></table>

<br /><br />
<a href="javascript:document.my_form.submit();" class="link_submit">[<?php echo xlt('Save'); ?>]</a>
<br />
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link_submit"
 onclick="top.restoreSession()">[<?php echo xlt('Don\'t Save Changes'); ?>]</a>
</form>

<div id="timestamp"></div>

<?php
formFooter();
?>
