<?php

/*
 * Work/School Note Form new.php
 *
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Nikolai Vitsyn
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Daniel Pflieger <daniel@growlingflea.com>
 * @copyright Copyright (c) 2004-2005 Nikolai Vitsyn
 * @copyright Copyright (c) Open Source Medical Software
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2022 Daniel Pflieger <daniel@growlingflea.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$covid19 = true;

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

formHeader("Form: note");
$returnurl = 'encounter_top.php';
$provider_results = sqlQuery("select fname, lname from users where username=?", array($_SESSION["authUser"]));
$patient_data = sqlQuery("select * from patient_data where pid = {$GLOBALS['pid']}");
/* name of this form */
$form_name = "note";
//***oe-ca-pediatric-printout-menu ADD
$note1 = "{$patient_data['fname']} has one low risk symptom. " .
            "They may return to school when they have no symptoms for 24 hours.";
$note2 = "{$patient_data['fname']}  has two or more low risk symptoms, or one or more high risk symptoms. " .
            "{$patient_data['fname']}'s COVID-19 test was negative, so may return to school when symptoms are " .
            "improving and when there is no fever for 24 hours.";
$note3 = "{$patient_data['fname']} 's COVID-19 test was positive.";
$note4 = "{$patient_data['fname']}  was exposed to someone who was reportedly was COVID-19 positive. " .
            "They may return to school 14 days after last exposure  as long as no symptoms develop.";
$note5 = "{$patient_data['fname']}  can return to school on ";
?>

<html><head>

<?php Header::setupHeader('datetime-picker'); ?>

<script>
// required for textbox date verification
let mypcc = <?php echo js_escape($GLOBALS['phone_country_code']); ?>;
</script>

</head>

<body class="body_top">
<?php echo text(date("F d, Y", time())); ?>

<form method=post action="<?php echo $rootdir . "/forms/" . $form_name . "/save.php?mode=new";?>"
      name="my_form" id="my_form">
    <span class="title"><?php echo xlt('Work/School Note'); ?></span><br /><br />
    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
    <div style="margin: 10px;">

    </div>

    <select name="note_type" id="note_type">
        <option value="SCHOOL NOTE" <?php
        if ($obj['note_type'] == "SCHOOL NOTE") {
            echo " SELECTED";
        }
        ?>><?php xl('SCHOOL NOTE', 'e'); ?></option>
        }

        <!--    //***o e-ca-pediatric-printout-menu ADD-->
        <option value="COVID-19 SCHOOL NOTE" <?php if ($obj['note_type'] == "COVID-19 SCHOOL NOTE") {
            echo " SELECTED";
                                             } ?>><?php xl('COVID-19 SCHOOL NOTE', 'e'); ?></option>
        <option value="PROVIDER COMMUNICATION" <?php if ($obj['note_type'] == "PROVIDER COMMUNICATION") {
            echo " SELECTED";
                                               } ?>><?php xl('PROVIDER COMMUNICATION', 'e'); ?></option>
        <option value="REFERRAL" <?php if ($obj['note_type'] == "REFERRAL") {
            echo " SELECTED";
                                 } ?>><?php xl('REFERRAL', 'e'); ?></option>
        <!--   //*** oe-ca-pediatric-printout-menu ADD-->
        <option value="WORK NOTE" <?php if ($obj['note_type'] == "WORK NOTE") {
            echo " SELECTED";
                                  } ?>><?php xl('WORK NOTE', 'e'); ?></option>
    </select>
    <br>
    <div><b><?php echo xlt('MESSAGE:'); ?></b></div>
    <div><textarea name="message" id="message" rows="7" cols="47"></textarea></div>

    <!--    //*** oe-ca-pediatric-printout-menu ADD-->
    <div id = "covid_choice" >
        <div  style="min-height:50px; width:300px; display:none; " id="txtDiv1">
            <?php echo xla($note1); ?>
        </div>
        <div  style="min-height:50px; width:300px; display:none; " id="txtDiv2">
            <?php echo xla($note2); ?>
        </div>
        <div  style="min-height:50px; width:300px; display:none; " id="txtDiv3">
            <?php echo xla($note3); ?>
        </div>
        <div style="min-height:50px; width:300px; display:none; " id="txtDiv4" >
            <?php echo xla($note4); ?>
        </div>
        <div  style="min-height:50px; width:300px; display:none; " id="txtDiv5">
            <?php echo xla($note5); ?>
        </div>
    </div>


<?php
// commented out below private field, because no field in database, and causes error.
?>
<!--
<input type="checkbox" name="private" id="private"><label for="private">This note is private</label>
<br />
-->

<br />
<b><?php echo xlt('Signature:'); ?></b>
<br />

<table>
<tr><td>
<?php echo xlt('Doctor:'); ?>
<input type="text" name="doctor" id="doctor"
       value="<?php echo attr($provider_results["fname"]) . ' ' . attr($provider_results["lname"]); ?>">
</td>

<td>
<span class="text"><?php echo xlt('Date'); ?></span>
   <input type='text' size='10' class='datepicker' name='date_of_signature' id='date_of_signature'
    value='<?php echo attr(date('Y-m-d', time())); ?>'
    title='<?php echo xla('yyyy-mm-dd'); ?>' />
</td>
</tr>
</table>
<!-- //***oe-ca-pediatric-printout-menu ADD -->
<div id = "covid" >
    <input type="checkbox" id="statement1"  value="<?php echo attr($note1) ?>" >
    <label for="statement1"><?php echo xla($note1) ?></label><br>
    <input type="checkbox" id="statement2"  value="<?php echo attr($note2) ?>1" >
    <label for="statement2"><?php echo xla($note2) ?></label><br>
    <input type="checkbox" id="statement3" value="<?php echo attr($note3) ?>" >
    <label for="statement3"><?php echo xla($note3) ?></label><br>
    <input type="checkbox" id="statement4" value="<?php echo attr($note4) ?>" >
    <label for="statement4"><?php echo xla($note4) ?></label><br>
    <input type="checkbox" id="statement5" value="<?php echo attr($note5) ?>" >
    <label for="statement5"><?php echo xla($note5) ?></label>
    <input type='text' size='10' class='datepicker'  id='date_of_return'
           value='<?php echo date('Y-m-d', time()); ?>'
           title='<?php echo xla('yyyy-mm-dd'); ?>' />
    <br>
</div>

<div style="margin: 10px;">
<input type="button" class="save" value="<?php echo xla('Save'); ?>    "> &nbsp;
<input type="button" class="dontsave" value="<?php echo xla('Don\'t Save'); ?>"> &nbsp;
</div>

</form>

</body>

<script>

// jQuery stuff to make the page a little easier to use
$(function () {
    // *** oe-ca-pediatric-printout-menu ADD  ADD
    let choice = $("#note_type").val();

    if(choice === "COVID-19 SCHOOL NOTE"){
        $("#covid").show();
        console.log("show covid school note");
    } else {
        $("#covid").hide();
    }

    $(".save").click(function() {
        top.restoreSession();
        //***SANTI ADD We are going to append the visible strings to message
        var visible = $("#covid_choice div").filter(function() {
            return $(this).css('display') === "block";

        });
        let newMessage = $("#message").val() + visible.text();
        if($("#message").val() === "") {
            newMessage = newMessage.replace(/\n/g, " ");
        }

        $("#message").val(newMessage);
        $('#my_form').submit();
    });
    //*** oe-ca-pediatric-printout-menu ADD  END

    $(".dontsave").click(function() { parent.closeTab(window.name, false); });
    //$("#printform").click(function() { PrintForm(); });

    $('.datepicker').datetimepicker({
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = false; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>

    });

    //***oe-ca-pediatric-printout-menu ADD  ADD
    $("#note_type").change(function() {
        console.log($("#note_type").val() + "from change new.php");
        choice = $("#note_type").val();
        if(choice === "COVID-19 SCHOOL NOTE"){
            $("#covid").show();
            console.log("from on change, we are a schoolnote");
        }else{
            $("#covid").hide();
            console.log("from on change, we are hiding a schoolnote");
        }
    });

    $("#statement1").click(function(){
        $("#txtDiv1").toggle(this.checked);
        console.log("Show message 1");
    });
    $("#statement2").click(function(){
        $("#txtDiv2").toggle(this.checked);
        console.log("Show message 2");
    });
    $("#statement3").click(function(){
        $("#txtDiv3").toggle(this.checked);
        console.log("Show message 3");
    });
    $("#statement4").click(function(){
        $("#txtDiv4").toggle(this.checked);
        console.log("Show message 4");
    });

    $("#statement5").click(function(){
        let dateString = $('#date_of_return').val();
        let originalString = "<?php echo $note5 ?>";
        $("#txtDiv5").text(originalString + " " + dateString );
        $("#txtDiv5").toggle(this.checked);


    });
    //***oe-ca-pediatric-printout-menu ADD  ADD END
});

</script>
<!--    //*** oe-ca-pediatric-printout-menu ADD-->
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/interface/forms/note/updates.js"></script>
</html>
