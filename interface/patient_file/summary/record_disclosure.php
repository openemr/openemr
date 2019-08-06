<?php
/**
 * Patient disclosures main screen.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Visolve <vicareplus_engg@visolve.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) Visolve <vicareplus_engg@visolve.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../../globals.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;

//if the edit button for editing disclosure is set.
if (isset($_GET['editlid'])) {
    $editlid=$_GET['editlid'];
}
?>
<!DOCTYPE html>
<html>
<head>
<link rel='stylesheet' href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker/build/jquery.datetimepicker.min.css">

<!-- supporting javascript code -->
<script type="text/javascript" src="<?php echo $webroot ?>/interface/main/tabs/js/include_opener.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery/dist/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker/build/jquery.datetimepicker.full.min.js"></script>

<script type="text/javascript">
//function to validate fields in record disclosure page
function submitform() {
    if (document.forms[0].dates.value.length <= 0) {
        document.forms[0].dates.focus();
        document.forms[0].dates.style.backgroundColor = "red";
    }
    else if (document.forms[0].recipient_name.value.length <= 0) {
        document.forms[0].dates.style.backgroundColor = "white";
        document.forms[0].recipient_name.focus();
        document.forms[0].recipient_name.style.backgroundColor = "red";
    }
    else if (document.forms[0].desc_disc.value.length <= 0) {
        document.forms[0].recipient_name.style.backgroundColor = "white";
        document.forms[0].desc_disc.focus();
        document.forms[0].desc_disc.style.backgroundColor = "red";
    }
    else if (document.forms[0].dates.value.length > 0 && document.forms[0].recipient_name.value.length > 0 && document.forms[0].desc_disc.value.length > 0) {
        top.restoreSession();
        document.forms[0].submit();
    }
}

$(document).ready(function () {
    $("#disclosure_form").submit(function (event) {
        event.preventDefault(); //prevent default action
        var post_url = $(this).attr("action");
        var request_method = $(this).attr("method");
        var form_data = $(this).serialize();

        $.ajax({
            url: post_url,
            type: request_method,
            data: form_data
        }).done(function (r) { //
            dlgclose('refreshme', false);
        });
    });

    $('.datepicker').datetimepicker({
        <?php $datetimepicker_timepicker = true; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = false; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });
});

</script>
</head>
<body class="body_top">
<div id="record-disclosure" style='float: left; margin-right: 10px' >
<div style='float: left; margin-right: 5px'><?php
if ($editlid) {
    ?><!--Edit the disclosures-->
    <span class="title"><?php echo xlt('Edit Disclosure'); ?></span><?php
} else { ?>
        <span class="title"><?php echo xlt('Record Disclosure'); ?></span><?php
} ?>
</div>

<form name="disclosure_form" id="disclosure_form" method="POST" action="disclosure_full.php">
    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

    <div><button class='css_button_span large_button_span' name='form_save' id='form_save'>
            <?php echo xlt('Save'); ?>
        </button></div>
    <div><a class="css_button large_button" id='cancel' href='#' onclick='top.restoreSession();dlgclose()'> <span
                class='css_button_span large_button_span'><?php echo xlt('Cancel'); ?></span>
        </a></div>
    <br>
<input type=hidden name=mode value="disclosure">
<table border=0 cellpadding=3 cellspacing=0 align='center'>
    <br>
    <tr>
        <td><span class='text'><?php echo xlt('Date'); ?>:</span></td>
        <td><!--retrieve disclosures from extended_log table for modifications-->
        <?php
        if ($editlid) {
            $dres=sqlQuery("select date,recipient,description,event from extended_log where id=?", array($editlid));
            $description=$dres{"description"};
            $app_event=$dres{"event"};
            $disc_date=$dres{"date"};
            $recipient_name=$dres{"recipient"};
            ?>
            <input type=hidden name=disclosure_id value="<?php echo attr($editlid); ?>">
            <input type=hidden name=updatemode value="disclosure_update">
            <input type='entry' size='20' class='datepicker' name='dates' id='dates' value='<?php echo attr($disc_date);?>' style="background-color:white"/>&nbsp; <?php
        } else {
            ?> <input type='entry' size='20' class='datepicker' name='dates' id='dates' value='' style="background-color:white"/>&nbsp;<?php
        } ?>
    </tr>
    <tr>
        <td><span class=text><?php echo xlt('Type of Disclosure'); ?>: </span></TD>
        <td><?php
        if ($editlid) {
            //To incorporate the disclosure types  into the list_options listings
            generate_form_field(array('data_type'=>1,'field_id'=>'disclosure_type','list_id'=>'disclosure_type','fld_length'=>'10','max_length'=>'63','empty_title'=>'SKIP'), $app_event);
        } else {
            //To incorporate the disclosure types  into the list_options listings
            generate_form_field(array('data_type'=>1,'field_id'=>'disclosure_type','list_id'=>'disclosure_type','fld_length'=>'10','max_length'=>'63','empty_title'=>'SKIP'), $title);
        } ?>
        </td>
    </tr>
    <tr>
        <td><span class=text><?php echo xlt('Recipient of the Disclosure'); ?>:
        </span></td>
        <td class='text'>
        <?php
        if ($editlid) {
            ?> <input type=entry name=recipient_name size=20 value="<?php echo attr($recipient_name); ?>"></td>
            <?php
        } else {?>
            <input type=entry name=recipient_name size=20 value="">
        </td>
            <?php
        }?>
    </tr>
    <tr>
        <td>
        <span class=text><?php echo xlt('Description of the Disclosure'); ?>:</span></td>
        <?php if ($editlid) { ?>
            <td>
            <textarea name=desc_disc wrap=auto rows=4 cols=30><?php echo text($description); ?></textarea>
        <?php } else {?>
            <td>
            <textarea name=desc_disc wrap=auto rows=4 cols=30></textarea>
        <?php }?>
        </td>
    </tr>
</table>
</form>
</body>
