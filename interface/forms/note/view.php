<?php

/*
 * Work/School Note Form view.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Nikolai Vitsyn
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Daniel Pflieger <daniel@growlingflea.com>
 * @copyright Copyright (c) 2004-2005 Nikolai Vitsyn
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2022 Daniel Pflieger <daniel@growlingflea.com>
 *
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */



require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

formHeader("Form: note");
$returnurl = 'encounter_top.php';
$provider_results = sqlQuery("select fname, lname from users where username=?", array($_SESSION["authUser"]));
$patient_data = sqlQuery("select * from patient_data where pid = {$GLOBALS['pid']}"); //***oe-ca-pediatric-printout-menu ADD

/* name of this form */
$form_name = "note";

//***oe-ca-pediatric-printout-menu ADD
$note1 = "{$patient_data['fname']} has one low risk symptom. They may return to school when they have no symptoms for 24 hours";
$note2 = "{$patient_data['fname']}  has two or more low risk symptoms, or one or more high risk symptoms. [Name]'s COVID-19 test was negative, so may return to school when symptoms are improving and when there is no fever for 24 hours.";
$note3 = "{$patient_data['fname']} 's COVID-19 test was positive.";
$note4 = "{$patient_data['fname']}  was exposed to someone who was reportedly was COVID-19 positive. They may return to school 14 days after last exposure  as long as no symptoms develop.";
$note5 = "{$patient_data['fname']}  can return to school on ";

// get the record from the database
if ($_GET['id'] != "") {
    $obj = formFetch("form_" . $form_name, $_GET["id"]);
}

/* remove the time-of-day from the date fields */
if ($obj['date_of_signature'] != "") {
    $dateparts = explode(" ", $obj['date_of_signature']);
    $obj['date_of_signature'] = $dateparts[0];
}
?>
<html><head>

<?php Header::setupHeader('datetime-picker'); ?>

<script>
// required for textbox date verification
var mypcc = <?php echo js_escape($GLOBALS['phone_country_code']); ?>;

function PrintForm() {
    //*** //*** oe-ca-pediatric-printout-menu Unsure of PrintForm function
    //newwin = window.open("<?php //echo $rootdir."/patient_file/report/custom_report.php?printable=1&note_".$_GET["id"]."=".$encounter; ?>//");

    newwin = window.open(<?php echo js_escape($rootdir . "/forms/" . $form_name . "/print.php?id=" . urlencode($_GET["id"])); ?>,"mywin");
}

</script>

</head>
<body class="body_top">

<form method=post action="<?php echo $rootdir . "/forms/" . $form_name . "/save.php?mode=update&id=" . attr_url($_GET["id"]);?>" name="my_form" id="my_form">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<span class="title"><?php echo xlt('Work/School Note'); ?></span><br /><br />

<div style="margin: 10px;">
<input type="button" class="save" value="    <?php echo xla('Save'); ?>    "> &nbsp;
<input type="button" class="dontsave" value="<?php echo xla('Don\'t Save'); ?>"> &nbsp;
<input type="button" class="printform" value="<?php echo xla('View Printable Version'); ?>"> &nbsp;
</div>

<!--    //*** oe-ca-pediatric-printout-menu-->
    <select name="note_type" id="note_type"> <?php //***oe-ca-pediatric-printout-menu Modify ?>

        <option value="SCHOOL NOTE" <?php if ($obj['note_type'] == "SCHOOL NOTE") {
            echo " SELECTED";
                                    } ?> selected><?php xl('SCHOOL NOTE', 'e'); ?></option>
        <!--    //***oe-ca-pediatric-printout-menu ADD-->
        <option value="COVID-19 SCHOOL NOTE" <?php if ($obj['note_type'] == "COVID-19 SCHOOL NOTE") {
            echo " SELECTED";
                                             } ?>><?php xl('COVID-19 SCHOOL NOTE', 'e'); ?></option>
        <option value="PROVIDER COMMUNICATION" <?php if ($obj['note_type'] == "PROVIDER COMMUNICATION") {
            echo " SELECTED";
                                               } ?>><?php xl('PROVIDER COMMUNICATION', 'e'); ?></option>
        <option value="REFERRAL" <?php if ($obj['note_type'] == "REFERRAL") {
            echo " SELECTED"; ?>><?php xl('REFERRAL', 'e');
                                 } ?></option>
        <!--   //*** oe-ca-pediatric-printout-menu ADD-->
        <option value="WORK NOTE" <?php if ($obj['note_type'] == "WORK NOTE") {
            echo " SELECTED"; ?>><?php xl('WORK NOTE', 'e');
                                  } ?></option>
    </select>
<!--    //*** oe-ca-pediatric-printout-menu-->
    <br><br><br><br>
    <b><?php echo xlt('MESSAGE:'); ?></b>
    <br>
    <?php
    $obj["message"] = str_replace(array('\n','\r'), '', $obj["message"]);
    ?>
    <textarea name="message" id="message" cols ="67" rows="4"><?php echo $obj["message"]; ?></textarea>
    <br> <br>
    <!--    //***oe-ca-pediatric-printout-menu ADD-->
    <div id = "covid_choice" >
        <div  style="min-height:50px; width:300px; display:none; " id="txtDiv1" >
            <?php echo xla($note1); ?>
        </div>
        <div  style="min-height:50px; width:300px; display:none;" id="txtDiv2" >
            <?php echo xla($note2); ?>
        </div>
        <div  style="min-height:50px; width:300px; display:none;" id="txtDiv3" >
            <?php echo xla($note3); ?>
        </div>
        <div style="min-height:50px; width:300px; display:none;" id="txtDiv4" >
            <?php echo xla($note4); ?>
        </div>
        <div  style="min-height:50px; width:300px; display:none;" id="txtDiv5" >
            <?php echo xla($note5); ?>
        </div>
    </div>

<table>
    <tr><td>
    <span class=text><?php echo xlt('Doctor:'); ?> </span><input type="text" name="doctor" value="<?php echo attr($obj["doctor"]);?>">
    </td><td>
    <span class="text"><?php echo xlt('Date'); ?></span>
       <input type='text' size='10' class='datepicker' name='date_of_signature' id='date_of_signature'
        value='<?php echo attr($obj['date_of_signature']); ?>'
        title='<?php echo xla('yyyy-mm-dd'); ?>' />
    </td></tr>
</table>
    <!--//***//*** oe-ca-pediatric-printout-menu ADD-->
    <div id = "covid">
        <input type="checkbox" id="statement1"  value="<?php echo attr($note1) ?>" >
        <label for="statement1"><?php echo attr($note1) ?></label><br>
        <input type="checkbox" id="statement2"  value="<?php echo attr($note2) ?>1" >
        <label for="statement2"><?php echo attr($note2) ?></label><br>
        <input type="checkbox" id="statement3" value="<?php echo attr($note3) ?>" >
        <label for="statement3"><?php echo attr($note3) ?></label><br>
        <input type="checkbox" id="statement4" value="<?php echo attr($note4) ?>" >
        <label for="statement4"><?php echo attr($note4) ?></label><br>
        <input type="checkbox" id="statement5" value="<?php echo attr($note5) ?>" >
        <label for="statement5"><?php echo attr($note5) ?></label>
        <input type='text' size='10' class='datepicker'  id='date_of_return'
               value='<?php echo date('Y-m-d', time()); ?>'
               title='<?php echo xla('yyyy-mm-dd'); ?>' />
        <br>
    </div>

<div style="margin: 10px;">
<input type="button" class="save" value="    <?php echo xla('Save'); ?>    "> &nbsp;
<input type="button" class="dontsave" value="<?php echo xla('Don\'t Save'); ?>"> &nbsp;
<input type="button" class="printform" value="<?php echo xla('Print'); ?>"> &nbsp;
</div>

</form>

</body>

<script>

// jQuery stuff to make the page a little easier to use

$(function () {

    //***oe-ca-pediatric-printout-menu ADD
    let choice = $("#note_type").val();

    if(choice === "COVID-19 SCHOOL NOTE"){
        $("#covid").show();
    }else{
        $("#covid").hide();
    }

    $(".save").click(function() {
        top.restoreSession();
        //***oe-ca-pediatric-printout-menu ADD We are going to append the visible strings to message
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
    //*** oe-ca-pediatric-printout-menu ADD

    $(".dontsave").click(function() { parent.closeTab(window.name, false); });
    $(".printform").click(function() { PrintForm(); });

    // disable the Print ability if the form has changed
    // this forces the user to save their changes prior to printing
    $("#img_date_of_signature").click(function() { $(".printform").attr("disabled","disabled"); });
    $("input").keydown(function() { $(".printform").attr("disabled","disabled"); });
    $("select").change(function() { $(".printform").attr("disabled","disabled"); });
    $("textarea").keydown(function() { $(".printform").attr("disabled","disabled"); });

    $('.datepicker').datetimepicker({
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = false; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });


    //***oe-ca-pediatric-printout-menu ADD
    $("#note_type").change(function() {
        console.log($("#note_type").val());
        choice = $("#note_type").val();
        if(choice === "COVID-19 SCHOOL NOTE"){
            $("#covid").show();
            console.log("WE SHOW view.php")

        }else{
            $("#covid").hide();
            console.log("WE HIDE view.php")
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
    //***oe-ca-pediatric-printout-menu ADD END
});

</script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/interface/forms/note/updates.js"></script>
</html>

