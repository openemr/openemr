<?php
/**
 *
 * Patient disclosures main screen.
 *
 * Copyright (C) Visolve <vicareplus_engg@visolve.com>
 * Copyright (C) 2017 Brady Miller <brady.g.miller@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Visolve <vicareplus_engg@visolve.com>
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @link    http://www.open-emr.org
 */





require_once("../../globals.php");
require_once("$srcdir/options.inc.php");

//if the edit button for editing disclosure is set.
if (isset($_GET['editlid'])) {
    $editlid=$_GET['editlid'];
}
?>
<!DOCTYPE html>
<html>
<head>
<link rel='stylesheet' href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.min.css">

<!-- supporting javascript code -->
<script type="text/javascript" src="<?php echo $webroot ?>/interface/main/tabs/js/include_opener.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-3-1-1/index.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.full.min.js"></script>

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
    <span class="title"><?php echo htmlspecialchars(xl('Edit Disclosure'), ENT_NOQUOTES); ?></span><?php
} else { ?>
        <span class="title"><?php echo htmlspecialchars(xl('Record Disclosure'), ENT_NOQUOTES); ?></span><?php
} ?>
</div>

<form name="disclosure_form" id="disclosure_form" method="POST" action="disclosure_full.php">
    <div><button class='css_button_span large_button_span' name='form_save' id='form_save'>
            <?php echo htmlspecialchars(xl('Save'), ENT_NOQUOTES); ?>
        </button></div>
    <div><a class="css_button large_button" id='cancel' href='#' onclick='top.restoreSession();dlgclose()'> <span
                class='css_button_span large_button_span'><?php echo htmlspecialchars(xl('Cancel'), ENT_NOQUOTES); ?></span>
        </a></div>
    <br>
<input type=hidden name=mode value="disclosure">
<table border=0 cellpadding=3 cellspacing=0 align='center'>
    <br>
    <tr>
        <td><span class='text'><?php echo htmlspecialchars(xl('Date'), ENT_NOQUOTES); ?>:</span></td>
        <td><!--retrieve disclosures from extended_log table for modifications-->
        <?php
        if ($editlid) {
            $dres=sqlQuery("select date,recipient,description,event from extended_log where id=?", array($editlid));
                       $description=$dres{"description"};
            $app_event=$dres{"event"};
            $disc_date=$dres{"date"};
                       $recipient_name=$dres{"recipient"};
            ?>
            <input type=hidden name=disclosure_id value="<?php echo htmlspecialchars($editlid, ENT_QUOTES); ?>">
            <input type=hidden name=updatemode value="disclosure_update">
            <input type='entry' size='20' class='datepicker' name='dates' id='dates' value='<?php echo htmlspecialchars($disc_date, ENT_QUOTES);?>' style="background-color:white"/>&nbsp; <?php
        } else {
            ?> <input type='entry' size='20' class='datepicker' name='dates' id='dates' value='' style="background-color:white"/>&nbsp;<?php
        } ?>
    </tr>
    <tr>
        <td><span class=text><?php echo htmlspecialchars(xl('Type of Disclosure'), ENT_NOQUOTES); ?>: </span></TD>
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
        <td><span class=text><?php echo htmlspecialchars(xl('Recipient of the Disclosure'), ENT_NOQUOTES); ?>:
        </span></td>
        <td class='text'>
        <?php
        if ($editlid) {
            ?> <input type=entry name=recipient_name size=20 value="<?php echo htmlspecialchars($recipient_name, ENT_QUOTES); ?>"></td>
            <?php
        } else {?>
            <input type=entry name=recipient_name size=20 value="">
        </td>
        <?php
        }?>
    </tr>
    <tr>
        <td>
        <span class=text><?php echo htmlspecialchars(xl('Description of the Disclosure'), ENT_NOQUOTES); ?>:</span></td>
        <?php if ($editlid) { ?>
            <td>
            <textarea name=desc_disc wrap=auto rows=4 cols=30><?php echo htmlspecialchars($description, ENT_NOQUOTES); ?></textarea>
        <?php } else {?>
            <td>
            <textarea name=desc_disc wrap=auto rows=4 cols=30></textarea>
        <?php }?>
        </td>
    </tr>
</table>
</form>
</body>
