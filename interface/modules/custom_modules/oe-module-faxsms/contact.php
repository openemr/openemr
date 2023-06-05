<?php

/**
 * Fax SMS Module Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018-2021 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$sessionAllowWrite = true;
require_once(__DIR__ . "/../../../globals.php");

use OpenEMR\Core\Header;
use OpenEMR\Modules\FaxSMS\Controller\AppDispatch;

$serviceType = $_REQUEST['type'] ?? '';
// kick off app endpoints controller
$clientApp = AppDispatch::getApiService($serviceType);
if (!$clientApp->verifyAcl()) {
    die("<h3>" . xlt("Not Authorised!") . "</h3>");
}
$logged_in = $clientApp->authenticate();
$isSMS = $clientApp->getRequest('isSMS', 0);
$isForward = ($clientApp->getRequest('mode', null) == 'forward') ? 1 : 0;
$isSMTP = !empty($GLOBALS['SMTP_PASS'] ?? null) && !empty($GLOBALS["SMTP_USER"] ?? null);
$default_message = '';
$interface_pid = null;
$file_mime = '';
$recipient_phone = '';
if (empty($isSMS)) {
    $interface_pid = $clientApp->getRequest('pid');
    $the_file = $clientApp->getRequest('file');
    $isContent = $clientApp->getRequest('isContent');
    $the_docid = $clientApp->getRequest('docid');
    $form_pid = $clientApp->getRequest('form_pid');
    $isDoc = (int)$clientApp->getRequest('isDocuments');
    $isQueue = $clientApp->getRequest('isQueue');
    $file_name = pathinfo($the_file, PATHINFO_BASENAME);
    $details = json_decode($clientApp->getRequest('details', ''), true);
} else {
    $interface_pid = $clientApp->getRequest('pid');
    $doc_name = $clientApp->getRequest('title');
    $portal_url = $GLOBALS['portal_onsite_two_address'];
    $details = json_decode($clientApp->getRequest('details', ''), true);
    $recipient_phone = $clientApp->getRequest('recipient', $details['phone']);
    // TODO need flag for message origin maybe later
    // $default_message = xlt("The following document") . ": " . text($doc_name) . " " . xlt("is available to be completed at") . " " . text($portal_url);
    $pid = $interface_pid ?: $pid;
}

$service = $clientApp::getServiceType();

?>
<!DOCTYPE html>
<html lang="">
<head>
    <title><?php echo xlt('Contact') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php Header::setupHeader();
    echo "<script>var pid=" . js_escape($pid) . ";var isSms=" . js_escape($isSMS) . ";var isForward=" . js_escape($isForward) . ";var recipient=" . js_escape($recipient_phone) . ";</script>";
    ?>
    <?php if (!empty($GLOBALS['text_templates_enabled'])) { ?>
        <script src="<?php echo $GLOBALS['web_root'] ?>/library/js/CustomTemplateLoader.js"></script>
    <?php } ?>
    <script>
        $(function () {
            if (isSms) {
                $(".smsExclude").addClass("d-none");
                $("#form_phone").val(recipient);
            } else {
                $(".faxExclude").addClass("d-none");
            }
            if (isForward) {
                $(".forwardExclude").addClass("d-none");
                $(".show-detail").removeClass("d-none");
            }
                // when the form is submitted
            $('#contact-form').on('submit', function (e) {
                if (!e.isDefaultPrevented()) {
                    let wait = '<div class="text-center wait"><i class="fa fa-cog fa-spin fa-2x"></i></div>';
                    let url = 'sendFax?type=fax';
                    if (isSms) {
                        url = 'sendSMS?type=sms';
                    }
                    if (isForward) {
                        url = 'forwardFax?type=fax';
                    }
                    $('#contact-form').find('.messages').html(wait);
                    // POST values in the background the script URL
                    $.ajax({
                        type: "POST",
                        url: url,
                        data: $(this).serialize(),
                        success: function (data) {
                            try {
                                let t_data = JSON.parse(data);
                                data = t_data;
                            } catch (e) {}
                            let err = (data.search(/Exception/) !== -1 ? 1 : 0);
                            if (!err) {
                                err = (data.search(/Error:/) !== -1) ? 1 : 0;
                            }
                            // Type of the message: success or danger. Apply it to the alert.
                            let messageAlert = 'alert-' + (err !== 0 ? 'danger' : 'success');
                            let messageText = data;

                            // let's compose alert box HTML
                            let alertBox = '<div class="alert ' + messageAlert + ' alert-dismissable">' +
                                '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' + messageText + '</div>';

                            // If we have messageAlert and messageText
                            if (messageAlert && messageText) {
                                // inject the alert to messages div in our form
                                $('#contact-form').find('.messages').html(alertBox);
                                setTimeout(function () {
                                    if (!err) {
                                        // close dialog as we have success.
                                        dlgclose();
                                    }
                                    // if error let user close dialog for time to read error message.
                                }, 4000);
                            }
                        }
                    });
                    return false;
                }
            })
        });

        function sel_patient() {
            const url = top.webroot_url + '/interface/main/calendar/find_patient_popup.php?pflag=0&pkretrieval=1'
            dlgopen(url, '_blank', 'modal-md', 550, false, '', {
            });
        }

        function setpatient(pid, lname, fname, dob) {
            let actionUrl = 'getPatientDetails';
            return $.post(actionUrl, {
                'pid': pid,
                'type': <?php echo js_escape($serviceType); ?>
            }, function (d, s) {
                $("#wait").remove()
            }, 'json').done(
                function (data) {
                    $(".show-detail").removeClass('d-none')
                    $("#form_name").val(data['fname']);
                    $("#form_lastname").val(data['lname']);
                    $("#form_phone").val(data['phone_cell']);
                });
        }

        function contactCallBack(contact) {
            let actionUrl = 'getUser';
            return $.post(actionUrl, {
                'uid': contact,
                'type': <?php echo js_url($serviceType); ?>
            }, function (d, s) {
                $("#wait").remove()
            }, 'json').done(
                function (data) {
                    $(".show-detail").removeClass('d-none')
                    $("#form_name").val(data[0]);
                    $("#form_lastname").val(data[1]);
                    $("#form_phone").val(data[2]);
                    $("#form_email").val(data[4]);
                });
        }

        const getContactBook = function (e, rtnpid) {
            e.preventDefault();
            let btnClose = <?php echo xlj("Cancel"); ?>;
            dlgopen('', '', 'modal-lg', 500, '', '', {
                buttons: [
                    {text: btnClose, close: true, style: 'primary  btn-sm'}
                ],
                url: top.webroot_url + '/interface/usergroup/addrbook_list.php?popup=2&type=' + encodeURIComponent(<?php echo js_escape($serviceType); ?>),
                dialogId: 'fax'
            });
        };
    </script>
    <style>
      .panel-body {
        word-wrap: break-word;
        overflow: hidden;
      }
    </style>
</head>
<body>
    <div class="container-fluid">
        <form class="form" id="contact-form" method="post" action="contact.php" role="form">
            <input type="hidden" id="form_file" name="file" value='<?php echo attr($the_file) ?>'>
            <input type="hidden" id="form_docid" name="docid" value='<?php echo attr($the_docid) ?>'>
            <input type="hidden" id="form_isContent" name="isContent" value='<?php echo attr($isContent); ?>'>
            <input type="hidden" id="form_isDocuments" name="isDocuments" value='<?php echo attr($isDoc) ?>'>
            <input type="hidden" id="form_isQueue" name="isQueue" value='<?php echo attr($isQueue) ?>'>
            <input type="hidden" id="form_isSMS" name="isSMS" value='<?php echo attr($isSMS) ?>'>
            <input type="hidden" id="form_mime" name="mime" value='<?php echo attr($file_mime) ?>'>
            <div class="messages"></div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group forwardExclude smsExclude faxExclude">
                        <label for="form_pid"><?php echo xlt('MRN') ?></label>
                        <input id="form_pid" type="text" name="$form_pid" class="form-control"
                            placeholder="<?php echo xla('If Applicable for charting.') ?>"
                            value="<?php echo attr($interface_pid ?? 0) ?>" />
                        <div class="help-block with-errors"></div>
                    </div>
                    <div class="form-group show-detail smsExclude faxExclude">
                        <label for="form_name"><?php echo xlt('Firstname') ?></label>
                        <input id="form_name" type="text" name="name" class="form-control"
                            placeholder="<?php echo xla('Not Required') ?>"
                            value="<?php echo attr($details['fname'] ?? '') ?>" />
                        <div class="help-block with-errors"></div>
                    </div>
                    <div class="form-group show-detail smsExclude faxExclude">
                        <label for="form_lastname"><?php echo xlt('Lastname') ?></label>
                        <input id="form_lastname" type="text" name="surname" class="form-control"
                            placeholder="<?php echo xla('Not Required') ?>"
                            value="<?php echo attr($details['lname'] ?? '') ?>" />
                        <div class="help-block with-errors"></div>
                    </div>
                    <div class="form-group faxExclude smsExclude show-detail">
                        <label for="form_email"><?php echo xlt('Email') ?></label>
                        <input id="form_email" type="email" name="email" class="form-control"
                            placeholder="<?php echo ($isSMTP ? xla('Forward to email address.') : xla('Unavailable! Setup SMTP in Config Notifications.')); ?>"
                            title="<?php echo xla('Forward to an email address.') ?>" />
                        <div class="help-block with-errors"></div>
                    </div>
                    <div class="form-group">
                        <label for="form_phone"><?php echo xlt('Recipient Phone') ?></label>
                        <input id="form_phone" type="tel" name="phone" class="form-control"
                            placeholder="<?php echo xla('Phone number of recipient') ?>"
                            title="<?php echo xla('You may also forward to a new fax number.') ?>"
                            value="" <?php echo (!$isForward ? 'required' : ''); ?> />
                        <div class="help-block with-errors"></div>
                    </div>
                    <?php if ($service == "1" || !empty($isSMS) || $isForward) { ?>
                        <div class="form-group">
                            <label for="form_message"><?php echo xlt('Message') ?></label>
                            <textarea id="form_message" name="comments" class="form-control" placeholder="
                            <?php echo empty($isSMS) ? xla('Add a note to recipient concerning fax.') : xla('SMS text message.') ?>" rows="6"><?php echo $default_message; ?></textarea>
                            <div class="help-block with-errors"></div>
                        </div>
                    <?php } ?>
                    <div>
                        <span class="text-center forwardExclude smsExclude"><strong><?php echo xlt('Sending File') . ': ' ?></strong><?php echo text($file_name) ?></span>
                    </div>
                    <div class="mt-2">
                        <button type="button" class="btn btn-primary smsExclude" onclick="getContactBook(event, pid)" value="Contacts"><?php echo xlt('Contacts') ?></button>
                        <!-- patient picker ready once get patient info is added. -->
                        <button type="button" class="btn btn-primary faxExclude" onclick="sel_patient()" value="Patients"><?php echo xlt('Patients') ?></button>
                        <button type="submit" class="btn btn-success float-right" value=""><?php echo empty($isSMS) ? xlt('Send Fax') : xlt('Send SMS') ?></button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
