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

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

// Control access
$authWrite = AclMain::aclCheckCore('patients', 'disclosure', '', 'write');
$authAddonly = AclMain::aclCheckCore('patients', 'disclosure', '', 'addonly');
if (!$authWrite && !$authAddonly) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Edit/Record Disclosure")]);
    exit;
}

//if the edit button for editing disclosure is set.
if (isset($_GET['editlid'])) {
    if (!$authWrite) {
        echo xlt('Not Authorized');
        exit;
    }
    $editlid = $_GET['editlid'];
}
?>
<!DOCTYPE html>
<html>
<head>
<?php Header::setupHeader(['datetime-picker', 'opener']); ?>

<script>
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

$(function () {
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
<body>
    <div class="container" id="record-disclosure">
        <div class="row">
            <div class="col-12">
                <?php
                if (!empty($editlid)) {
                    ?><!--Edit the disclosures-->
                    <h2 class="title"><?php echo xlt('Edit Disclosure'); ?></h2><?php
                } else { ?>
                    <span class="title"><?php echo xlt('Record Disclosure'); ?></span><?php
                } ?>
            </div>
            <div class="col-12">
                <form name="disclosure_form" id="disclosure_form" method="POST" action="disclosure_full.php">
                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                    <div class="btn-group">
                        <button class='btn btn-primary btn-save' name='form_save' id='form_save'>
                            <?php echo xlt('Save'); ?>
                        </button>
                        <button class="btn btn-secondary btn-cancel" id='cancel' onclick='top.restoreSession();dlgclose()'>
                            <?php echo xlt('Cancel'); ?>
                        </button>
                    </div>

                    <input type='hidden' name='mode' value="disclosure" />

                    <div class="form-group mt-3">
                        <label><?php echo xlt('Date'); ?>:</label>
                        <?php
                        if (!empty($editlid)) {
                            $dres = sqlQuery("select date,recipient,description,event from extended_log where id=?", array($editlid));
                            $description = $dres["description"];
                            $app_event = $dres["event"];
                            $disc_date = $dres["date"];
                            $recipient_name = $dres["recipient"];
                            ?>
                            <input type="hidden" name="disclosure_id" value="<?php echo attr($editlid); ?>">
                            <input type="hidden" name="updatemode" value="disclosure_update">
                            <input type='entry' size='20' class='datepicker form-control' name='dates' id='dates' value='<?php echo attr($disc_date);?>'/>&nbsp; <?php
                        } else {
                            ?> <input type='entry' size='20' class='datepicker form-control' name='dates' id='dates' value=''/>&nbsp;<?php
                        } ?>
                    </div>

                    <div class="form-group mt-3">
                        <label><?php echo xlt('Type of Disclosure'); ?>:</label>
                        <?php
                        if (!empty($editlid)) {
                            //To incorporate the disclosure types  into the list_options listings
                            generate_form_field(array('data_type' => 1,'field_id' => 'disclosure_type','list_id' => 'disclosure_type','fld_length' => '10','max_length' => '63','empty_title' => 'SKIP'), $app_event);
                        } else {
                            //To incorporate the disclosure types  into the list_options listings
                            generate_form_field(array('data_type' => 1,'field_id' => 'disclosure_type','list_id' => 'disclosure_type','fld_length' => '10','max_length' => '63','empty_title' => 'SKIP'), ($title ?? ''));
                        } ?>
                    </div>

                    <div class="form-group mt-3">
                        <label><?php echo xlt('Recipient of the Disclosure'); ?>:</label>
                        <?php
                        if (!empty($editlid)) {
                            ?> <input type="entry" class="form-control" name="recipient_name" size="20" value="<?php echo attr($recipient_name); ?>" />
                            <?php
                        } else {?>
                            <input type="entry" class="form-control" name="recipient_name" size="20" value="" />
                            <?php
                        }?>
                    </div>

                    <div class="form-group mt-3">
                        <label><?php echo xlt('Description of the Disclosure'); ?>:</label>
                        <?php if (!empty($editlid)) { ?>
                            <textarea class="form-control" name="desc_disc" wrap="auto" rows="4" cols="30"><?php echo text($description); ?></textarea>
                        <?php } else {?>
                            <textarea class="form-control" name="desc_disc" wrap="auto" rows="4" cols="30"></textarea>
                        <?php }?>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
