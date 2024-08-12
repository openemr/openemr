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

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Modules\FaxSMS\Controller\AppDispatch;

$serviceType = $_REQUEST['type'] ?? '';
// kick off app endpoints controller
$clientApp = AppDispatch::getApiService($serviceType);
if (!$clientApp->verifyAcl()) {
    die("<h3>" . xlt("Not Authorised!") . "</h3>");
}
$logged_in = $clientApp->authenticate();
$isSMS = $clientApp->getRequest('isSMS', false);
$isEmail = $clientApp->getRequest('isEmail', false);
$isForward = $isFax = 0;
$isForward = ($clientApp->getRequest('mode', '') == 'forward') ? 1 : 0;
$isFax = ($serviceType == 'fax') ? 1 : 0;

$isSMTP = !empty($GLOBALS['SMTP_PASS'] ?? null) && !empty($GLOBALS["SMTP_USER"] ?? null);

$isOnetime = $clientApp->getRequest('isOnetime', false);
$service = $clientApp::getServiceType();

$default_message = '';
$interface_pid = null;
$file_mime = '';
$recipient_phone = '';
$file_name = '';
if (empty($isSMS)) {
// fax contact form
    $interface_pid = $clientApp->getRequest('pid');
    $the_file = $clientApp->getRequest('file');
    $isContent = $clientApp->getRequest('isContent');
    $the_docid = $clientApp->getRequest('docid', $clientApp->getRequest('template_id'));
    $form_pid = $clientApp->getRequest('form_pid');
    $isDoc = (int)$clientApp->getRequest('isDocuments');
    $isQueue = $clientApp->getRequest('isQueue');
    $file_name = pathinfo($the_file, PATHINFO_BASENAME);
    $details = json_decode($clientApp->getRequest('details', ''), true);
    $template_name = $clientApp->getRequest('title');
} else {
// SMS contact dialog. Passed in phone or select patient from popup.
    $interface_pid = $clientApp->getRequest('pid');
    $portal_url = $GLOBALS['portal_onsite_two_address'];
    $details = json_decode($clientApp->getRequest('details', ''), true);
    $recipient_phone = $clientApp->getRequest('recipient', $details['phone'] ?? '');
    $pid = $interface_pid;
}

?>
<!DOCTYPE html>
<html lang="">
<head>
    <title><?php echo xlt('Contact') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php Header::setupHeader();
    echo "<script>var pid=" . js_escape($pid) . ";var isFax=" . js_escape($isFax) . ";var isOnetime=" . js_escape($isOnetime) . ";var isEmail=" . js_escape($isEmail) . ";var isSms=" . js_escape($isSMS) . ";var isForward=" . js_escape($isForward) . ";var recipient=" . js_escape($recipient_phone) . ";</script>";
    ?>
    <?php if (!empty($GLOBALS['text_templates_enabled'])) { ?>
        <script src="<?php echo $GLOBALS['web_root'] ?>/library/js/CustomTemplateLoader.js"></script>
    <?php } ?>
    <script>
        $(function () {
            if (isSms) {
                $("#form_phone").val(recipient);
                if (pid) {
                    setpatient(pid);
                }
                $(".smsExclude").addClass("d-none");
            }
            if (isForward) {
                $(".forwardExclude").addClass("d-none");
                $(".show-detail").removeClass("d-none");
            }
            if (isEmail || isOnetime) {
                $(".emailExclude").addClass("d-none");
                $(".smsExclude").addClass("d-none");
                $(".faxExclude").addClass("d-none");
                $(".show-detail").removeClass("d-none");
                if (pid) {
                    setpatient(pid);
                }
            }
            // when the form is submitted
            $(document).ready(function() {
                // Ensuring event handlers are set after the DOM is fully loaded
                $('#contact-form').on('submit', function(e) {
                    e.preventDefault(); // Prevent the default form submit

                    const wait = '<div class="text-center wait"><i class="fa fa-cog fa-spin fa-2x"></i></div>';
                    $('#contact-form').find('.messages').html(wait);

                    const url = buildUrl();
                    const formData = $(this).serialize();

                    $.ajax({
                        type: "POST",
                        url: url,
                        data: formData,
                        success: handleResponse,
                        error: function() {
                            showErrorMessage('An unexpected error occurred and your request could not be completed.');
                        }
                    });
                });

                function buildUrl() {
                    // Simplify logic by directly mapping conditions to URLs
                    if (isOnetime) {
                        const type = isSms ? 'sms' : 'email';
                        return `./library/api_onetime.php?sendOneTime&type=${encodeURIComponent(type)}`;
                    }

                    if (isSms) {
                        return 'sendSMS?type=sms';
                    } else if (isForward) {
                        return 'forwardFax?type=fax';
                    } else if (isEmail) {
                        return 'sendEmail?type=email';
                    } else {
                        return 'sendFax?type=fax';
                    }
                }

                function handleResponse(data) {
                    let jsonData;
                    try {
                        jsonData = JSON.parse(data);
                    } catch (e) {
                        jsonData = data; // Use data as is if it can't be parsed as JSON
                    }

                    const isError = /Exception|Error:/.test(jsonData);
                    const messageType = isError ? 'danger' : 'success';
                    const messageText = jsonData;
                    const alertBox = `<div class="alert alert-${messageType} alert-dismissable">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            ${messageText}
                          </div>`;

                    $('#contact-form').find('.messages').html(alertBox);
                    if (!isError) {
                        setTimeout(() => { dlgclose(); }, 4000); // Auto-close dialog on success
                    }
                }

                function showErrorMessage(message) {
                    const alertBox = `<div class="alert alert-danger alert-dismissable">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            ${message}
                          </div>`;
                    $('#contact-form').find('.messages').html(alertBox);
                }
            });

        });

        function sel_patient() {
            const url = top.webroot_url + '/interface/main/calendar/find_patient_popup.php?pflag=0&pkretrieval=1'
            dlgopen(url, '_blank', 'modal-md', 550, false, '', {});
        }

        // callback for patient select dialog
        function setpatient(pid) {
            let actionUrl = 'getPatientDetails';
            return $.post(actionUrl, {
                'pid': pid,
                'type': <?php echo js_escape($serviceType); ?>
            }, function () {
                $("#wait").remove()
            }, 'json').done(
                function (data) {
                    if (recipient && !data['phone_cell']) {
                        data['phone_cell'] = recipient;
                    }
                    $(".show-detail").removeClass('d-none')
                    $("#form_name").val(data['fname']);
                    $("#form_lastname").val(data['lname']);
                    $("#form_phone").val(data['phone_cell']);
                    $("#form_email").val(data['email']);
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
            <input type="hidden" name="csrf_token_form" id="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken('contact-form')); ?>" />
            <input type="hidden" id="form_file" name="file" value='<?php echo attr($the_file ?? ''); ?>'>
            <input type="hidden" id="form_docid" name="docid" value='<?php echo attr($the_docid ?? ''); ?>'>
            <input type="hidden" id="form_isContent" name="isContent" value='<?php echo attr($isContent ?? ''); ?>'>
            <input type="hidden" id="form_isDocuments" name="isDocuments" value='<?php echo attr($isDoc ?? ''); ?>'>
            <input type="hidden" id="form_isQueue" name="isQueue" value='<?php echo attr($isQueue ?? ''); ?>'>
            <input type="hidden" id="form_isSMS" name="isSMS" value='<?php echo attr($isSMS ?? ''); ?>'>
            <input type="hidden" id="form_mime" name="mime" value='<?php echo attr($file_mime ?? ''); ?>'>
            <input type="hidden" id="form_file" name="templateName" value='<?php echo attr($template_name ?? ''); ?>'>
            <input type="hidden" id="form_details" name="details" value='<?php echo attr_js($details ?? ''); ?>'>
            <div class="messages"></div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group forwardExclude smsExclude faxExclude">
                        <label for="form_pid"><?php echo xlt('MRN') ?></label>
                        <input id="form_pid" type="text" name="form_pid" class="form-control"
                            placeholder="<?php echo xla('If Applicable for charting.') ?>"
                            value="<?php echo attr($interface_pid ?? 0) ?>" />
                    </div>
                    <div class="form-group show-detail">
                        <label for="form_name"><?php echo xlt('Name') ?></label>
                        <input id="form_name" type="text" name="name" class="form-control"
                            placeholder="<?php echo xla('Not Required') ?>"
                            value="<?php echo attr($details['fname'] ?? '') ?>" />
                        <div class="form-group show-detail smsExclude faxExclude">
                            <!--<label for="form_lastname"><?php /*echo xlt('Lastname') */ ?></label>-->
                            <input id="form_lastname" type="text" name="surname" class="form-control"
                                placeholder="<?php echo xla('Not Required') ?>"
                                value="<?php echo attr($details['lname'] ?? '') ?>" />
                        </div>
                        <div class="form-group faxExclude smsExclude show-detail">
                            <label for="form_email"><?php echo xlt('Email') ?></label>
                            <input id="form_email" type="email" name="email" class="form-control"
                                placeholder="<?php
                                if (($isSMS || $isEmail || $isFax) && !$isForward) {
                                    echo '';
                                } else {
                                    echo($isSMTP ? xla('Forward to email address if address is included.') : xla('Unavailable! Setup SMTP in Config Notifications.'));
                                }
                                ?>"
                                title="<?php echo xla('Forward to an email address.') ?>" />
                        </div>
                        <div class="form-group">
                            <label for="form_phone"><?php echo xlt('Recipient Phone') ?></label>
                            <input id="form_phone" type="tel" name="phone" class="form-control"
                                placeholder="<?php echo xla('Phone number of recipient') ?>"
                                title="<?php echo xla('You may also forward to a new fax number.') ?>"
                                value="" <?php echo(!$isForward ? 'required' : ''); ?> />
                        </div>
                        <?php if ($service == "1" || !empty($isSMS) || $isForward || $isEmail) { ?>
                        <div class="form-group">
                            <label for="form_message"><?php echo xlt('Message') ?></label>
                            <textarea id="form_message" name="comments" class="form-control" placeholder="
                            <?php echo xla('Add a note to recipient.'); ?>" rows="6"><?php echo $default_message; ?></textarea>
                        </div>
                        <?php } ?>
                        <div>
                            <span class="text-center forwardExclude smsExclude"><strong><?php echo xlt('Sending File') . ': ' ?></strong><?php echo text($file_name) ?></span>
                        </div>
                        <div class="mt-2">
                            <button type="button" class="btn btn-primary smsExclude" onclick="getContactBook(event, pid)" value="Contacts"><?php echo xlt('Contacts') ?></button>
                            <?php if ($isOnetime ?? 0) { ?>
                                <span class="form-group">
                            <div class="form-check-inline">
                                <label><?php echo xlt("Send Using"); ?></label>
                            </div>
                            <div class="form-check-inline">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input" name="notification_method" value="sms" /><?php echo xlt("SMS") ?>
                                </label>
                            </div>
                            <div class="form-check-inline">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input" name="notification_method" value="email" checked /><?php echo xlt("Email") ?>
                                </label>
                            </div>
                            <div class="form-check-inline">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input" name="notification_method" value="both" /><?php echo xlt("Both") ?>
                                </label>
                            </div>
                        </span>
                            <?php } ?>
                            <!-- patient picker ready once get patient info is added. -->
                            <button type="button" class="btn btn-primary faxExclude" onclick="sel_patient()" value="Patients"><?php echo xlt('Patients') ?></button>
                            <button type="submit" class="btn btn-success float-right" value=""><?php echo empty($isSMS) || $isOnetime ? xlt('Send') : xlt('Send SMS') ?></button>
                        </div>
                    </div>
                </div>
        </form>
    </div>
</body>
</html>
