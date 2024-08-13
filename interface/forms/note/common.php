<?php

/*
 * Work/School Note Form view.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Nikolai Vitsyn
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2004-2005 Nikolai Vitsyn
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */



require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

formHeader("Form: note");
$returnurl = 'encounter_top.php';
$provider_results = sqlQuery("select fname, lname from users where username=?", array($_SESSION["authUser"]));

/* name of this form */
$form_name = "note";

// get the record from the database
if ($_GET['id'] != "") {
    $obj = formFetch("form_" . $form_name, $_GET["id"]);
}

?>
<html>
<head>
    <title><?php echo xlt('School/Work Note'); ?></title>
    <?php Header::setupHeader('datetime-picker'); ?>

    <script>
        // required for textbox date verification
        var mypcc = <?php echo js_escape($GLOBALS['phone_country_code']); ?>;

        $(function () {
            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
            });
        });

        function PrintForm() {
            newwin = window.open(<?php echo js_escape($rootdir . "/forms/" . $form_name . "/print.php?id=" . urlencode($_GET["id"])); ?>,"mywin");
        }

    </script>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-sm-12 mt-5">
            <h1><?php echo xlt('School/Work Note'); ?></h1>
            <?php echo text(date("F d, Y", time())); ?>
        </div>
    </div>
    <form method=post action="<?php echo $rootdir . "/forms/" . $form_name . "/save.php?mode=update&id=" . attr_url($_GET["id"]);?>" name="my_form" id="my_form">
        <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
        <div class="m-3">
            <select name="note_type" class="form-control">
                option value="WORK NOTE" <?php if ($obj['note_type'] == "WORK NOTE") {
                    echo " SELECTED";
                } ?>><?php echo xlt('WORK NOTE'); ?></option>
                <option value="SCHOOL NOTE" <?php if ($obj['note_type'] == "SCHOOL NOTE") {
                    echo " SELECTED";
                } ?>><?php echo xlt('SCHOOL NOTE'); ?></option><
            </select>
        </div>
        <div class="m-4">
            <b><?php echo xlt('MESSAGE:'); ?></b>
            <textarea class="form-control" name="message" id="message" cols ="67" rows="4"><?php echo text($obj["message"]);?></textarea>
        </div>
        <div class="mt-4">
            <b><?php echo xlt('Signature:'); ?>:</b>
        </div>
        <div class="m-3">
            <table>
                <tr><td>
                        <span class="mr-1"><?php echo xlt('Doctor:'); ?> </span><input class="form-control mr-2" type="text" name="doctor" value="<?php echo attr($obj["doctor"]);?>">
                    </td><td>
                        <span class="ml-3"><?php echo xlt('Date'); ?></span>
                        <input type='text' size='10' class='datepicker form-control ml-3' name='date_of_signature' id='date_of_signature'
                               value='<?php echo attr(oeFormatShortDate($obj['date_of_signature'])); ?>'
                               title='<?php echo xla('Date of Signature'); ?>' />
                    </td></tr>
            </table>
        </div>
        <div style="margin: 10px;">
            <input type="button" class="btn btn-primary mr-1" value="    <?php echo xla('Save'); ?>    ">
            <input type="button" class="btn btn-warning mr-1" value="<?php echo xla('Don\'t Save'); ?>">
            <input type="button" class="printform btn btn-success" value="<?php echo xla('View Printable Version'); ?>">
        </div>
    </form>
</div>
</body>
<script>

    // jQuery stuff to make the page a little easier to use

    $(function () {
        $(".save").click(function() { top.restoreSession(); $("#my_form").submit(); });
        $(".dontsave").click(function() { parent.closeTab(window.name, false); });
        $(".printform").click(function() { PrintForm(); });

        // disable the Print ability if the form has changed
        // this forces the user to save their changes prior to printing
        $("#img_date_of_signature").click(function() { $(".printform").attr("disabled","disabled"); });
        $("input").keydown(function() { $(".printform").attr("disabled","disabled"); });
        $("select").change(function() { $(".printform").attr("disabled","disabled"); });
        $("textarea").keydown(function() { $(".printform").attr("disabled","disabled"); });
    });

</script>

</html>
