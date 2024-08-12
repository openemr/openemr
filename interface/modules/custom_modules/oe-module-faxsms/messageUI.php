<?php

/**
 * Fax and SMS Module UI Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018-2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$sessionAllowWrite = true;
require_once(__DIR__ . "/../../../globals.php");

use OpenEMR\Core\Header;
use OpenEMR\Modules\FaxSMS\Controller\AppDispatch;

$serviceType = $_REQUEST['type'] ?? '';
$clientApp = AppDispatch::getApiService($serviceType);
$service = $clientApp::getServiceType();
$title = $service == "1" ? xlt('RingCentral') : '';
$title = $service == "2" ? xlt('Twilio SMS') : $title;
$title = $service == "3" ? xlt('etherFAX') : $title;
$tabTitle = $serviceType == "sms" ? xlt('SMS') : xlt('FAX');
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $tabTitle ?? ''; ?></title>
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/dropzone/dist/dropzone.css">
    <?php
    if (!$clientApp->verifyAcl()) {
        die("<h3>" . xlt("Not Authorised!") . "</h3>");
    }
    Header::setupHeader(['opener', 'datetime-picker', 'jspdf', 'jstiff']);
    echo "<script>let pid=" . js_escape($pid ?? 0) . ";let portalUrl=" . js_escape($clientApp->portalUrl ?? '') .
        ";let currentService=" . js_escape($service) . ";let serviceType=" . js_escape($serviceType) . "</script>";
    ?>
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/dropzone/dist/dropzone.js"></script>

    <script>
        $(function () {
            $('.datepicker').datetimepicker({
                <?php
                $datetimepicker_timepicker = false;
                $datetimepicker_showseconds = false;
                $datetimepicker_formatInput = false;
                require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php');
                ?>
            });
            let dateRange = new Date(new Date().setDate(new Date().getDate() - 7));
            $("#fromdate").val(dateRange.toJSON().slice(0, 10));
            $("#todate").val(new Date().toJSON().slice(0, 10));

            $(".other").hide();
            if (currentService == '1' && serviceType == 'fax') {
                $(".rc-hide").hide();
                $(".rc-fax-hide").hide();
            } else if (currentService == '1' && serviceType == 'sms') {
                $(".rc-hide").hide();
            } else if (currentService == '2') {
                $(".etherfax").hide();
            } else if (currentService == '3') {
                $(".twilio").hide();
                $(".etherfax-hide").hide();
                $(".etherfax").show();
            }
            if (serviceType == 'sms') {
                $(".sms-hide").hide();
            }

            retrieveMsgs();
            $('#received').tab('show');
        });

        const sendFax = function (filePath, from = '') {
            let btnClose = <?php echo xlj("Cancel"); ?>;
            let title = <?php echo xlj("Send To Contact"); ?>;
            let url = top.webroot_url + '/interface/modules/custom_modules/oe-module-faxsms/contact.php?type=fax&isDocuments=0&isQueue=' +
                encodeURIComponent(from) + '&file=' + encodeURIComponent(filePath);
            // leave dialog name param empty so send dialogs can cascade.
            dlgopen(url, '', 'modal-sm', 700, '', title, { // dialog restores session
                buttons: [
                    {text: btnClose, close: true, style: 'secondary btn-sm'}
                ],
                resolvePromiseOn: 'close',
                sizeHeight: 'full'
            }).then(function (contact) {
                top.restoreSession();
            });
        };

        const docInfo = function (e, ppath) {
            try {
                top.restoreSession();
            } catch (error) {
                console.log('Session restore failed!');
            }
            let msg = <?php echo xlj('Your Account Portal') ?>;
            dlgopen(ppath, '_blank', 1240, 900, true, msg)
        };

        const popNotify = function (e, ppath) {
            try {
                top.restoreSession();
            } catch (error) {
                console.log('Session restore failed!');
            }
            let msg = <?php echo xlj('Are you sure you wish to send all scheduled reminders now?') ?>;
            if (e === 'live') {
                let yn = confirm(msg);
                if (!yn) {
                    return false
                }
            }
            let msg1 = <?php echo xlj('Appointment Reminder Alerts!') ?>;
            dlgopen(ppath, '_blank', 1240, 900, true, msg1)
        };

        const doSetup = function (e) {
            try {
                top.restoreSession();
            } catch (error) {
                console.log('Session restore failed!');
            }
            e.preventDefault();
            let url = top.webroot_url + '/interface/modules/custom_modules/oe-module-faxsms/setup.php';
            if (currentService === '1') {
                url = top.webroot_url + '/interface/modules/custom_modules/oe-module-faxsms/setup_rc.php';
            }
            let msg = <?php echo xlj('Credentials and Notifications') ?>;
            dlgopen('', 'setup', 'modal-md', 700, '', msg, {
                buttons: [
                    {text: 'Cancel', close: true, style: 'secondary  btn-sm'}
                ],
                url: url + "?type=" + encodeURIComponent(serviceType)
            });
        };

        const forwardFax = function (e, docid = '', filePath = '', details = []) {
            let btnClose = <?php echo xlj("Cancel"); ?>;
            let title = <?php echo xlj("Forward Fax to Email, Fax recipient or both."); ?>;
            let url = top.webroot_url +
                '/interface/modules/custom_modules/oe-module-faxsms/contact.php?type=fax&mode=forward&isDocuments=0&docid=' +
                encodeURIComponent(docid);
            // leave dialog name param empty so send dialogs can cascade.
            dlgopen(url, '', 'modal-md', 800, '', title, { // dialog restores session
                buttons: [{text: btnClose, close: true, style: 'secondary btn-sm'}]
            });
            return false;
        };

        function base64ToArrayBuffer(_base64Str) {
            let binaryString = window.atob(_base64Str);
            let binaryLen = binaryString.length;
            let bytes = new Uint8Array(binaryLen);
            for (let i = 0; i < binaryLen; i++) {
                bytes[i] = binaryString.charCodeAt(i);
            }
            return bytes;
        }

        function showPrint(base64, _contentType = 'image/tiff') {
            const binary = atob(base64.replace(/\s/g, ''));
            const len = binary.length;
            const buffer = new ArrayBuffer(len);
            const view = new Uint8Array(buffer);
            for (let i = 0; i < len; i++) {
                view[i] = binary.charCodeAt(i);
            }
            const blob = new Blob([view], {type: _contentType});
            const url = URL.createObjectURL(blob);
            let iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            iframe.width = '0';
            iframe.height = '0';
            iframe.id = 'tempFrame';
            document.body.appendChild(iframe);
            iframe.onload = function () {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            }
            // write the content
            iframe.src = url;
        }

        // Function to get or dispose of document.
        async function getDocument(e, docuri, docid, downFlag, deleteFlag = '', massDelete = false) {
            try {
                top.restoreSession();
            } catch (error) {
                console.log('Session restore failed!');
            }
            if (e !== '') {
                e.preventDefault();
            }
            if (docuri === null) {
                docuri = '';
            }
            if (downFlag == 'true') {
                let yn = confirm(
                    xl("After a fax is downloaded it is marked as received and no longer available here.") + "\n\n" +
                    xl("Do you want to continue with this download?")
                );
                if (!yn) {
                    return false;
                }
            }
            if (deleteFlag == 'true' && !massDelete) {
                let yn = confirm(
                    xl("Are you sure you want to continue with delete?")
                );
                if (!yn) {
                    return false;
                }
            }
            // Get ready, Get set, Go!
            let actionUrl = 'viewFax?type=fax';
            $(".brand").addClass('fa fa-spinner fa-spin');
            try {
                let json = await $.post(actionUrl, {
                    'type': serviceType,
                    'docuri': docuri,
                    'docid': docid,
                    'pid': pid,
                    'download': downFlag,
                    'delete': deleteFlag
                }).promise();
                $(".brand").removeClass('fa fa-spinner fa-spin');
                let data;
                try {
                    data = JSON.parse(json);
                } catch {
                    data = json;
                }
                if (data.error) {
                    alertMsg(data.error);
                    return false;
                }

                if (deleteFlag == 'true') {
                    if (massDelete) {
                        return false;
                    }
                    setTimeout(retrieveMsgs, 1000);
                    return false;
                }
                if (downFlag == 'true') {
                    let base64 = data.base64;
                    if (data.mime === 'image/tiff' || data.mime === 'image/tif') {
                        let images = await convertTiffToImages(base64ToArrayBuffer(data.base64));
                        base64 = await convertImagesToPdf(images, data.filename);
                    } else {
                        base64 = '';
                    }
                    fetch('disposeDocument?type=fax', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            action: 'setup',
                            file_path: data.path,
                            content: base64
                        })
                    }).then(response => response.json()).then(result => {
                        // Download the file. result.url is temp file path of tiff to pdf by image conversion in JS or imagick.
                        if (result.success) {
                            location.href = "disposeDocument?type=fax&action=download&file_path=" + encodeURIComponent(result.url);
                        } else {
                            console.error('Failed to prepare the file for download:', jsText(result.message));
                        }
                    }).catch(error => {
                        console.error('Error:', error);
                    });

                    return false;
                }
                if (data.mime === 'application/pdf') {
                    showDocument(data.base64, data.mime);
                } else if (data.mime === 'image/tiff') {
                    let images = await convertTiffToImages(base64ToArrayBuffer(data.base64));
                    let pdfBase64 = await convertImagesToPdf(images, data.filename);
                    showDocument(pdfBase64.replace('data:application/pdf;base64,', ''), 'application/pdf');
                } else {
                    showDocument(data.base64, data.mime);
                }
            } catch (error) {
                console.error('Error handling document:', jsText(error));
                $(".brand").removeClass('fa fa-spinner fa-spin');
            }
        }

        // Function to convert TIFF to PNG/JPEG images
        async function convertTiffToImages(tiffData, mime = 'image/jpeg') {
            try {
                let tiff = new Tiff({buffer: tiffData});
                const directories = tiff.countDirectory();
                const promises = [];

                for (let i = 0; i < directories; i++) {
                    promises.push(new Promise((resolve, reject) => {
                        tiff.setDirectory(i);
                        let canvas = tiff.toCanvas();
                        let imageData = canvas.toDataURL(mime);
                        resolve(imageData);
                    }));
                }
                return Promise.all(promises);
            } catch (error) {
                console.error('Failed to convert TIFF to images:', error);
                return [];
            }
        }

        // Function to convert images to PDF and return a base64
        async function convertImagesToPdf(images, filename = 'fax-tiff-to-pdf.pdf') {
            const {jsPDF} = window.jspdf;
            const doc = new jsPDF();
            doc.internal.write.isEvalSupported = false;
            const pageHeight = doc.internal.pageSize.height;
            const pageWidth = doc.internal.pageSize.width;

            for (let i = 0; i < images.length; i++) {
                if (i !== 0) {
                    doc.addPage();
                }
                doc.addImage(images[i], 'JPEG', 0, 0, pageWidth, pageHeight);
            }

            // Return the PDF as base64 string
            return doc.output('datauristring').split(',')[1];
        }

        function showDocument(_base64, _contentType = 'image/tiff') {
            try {
                // Ensure _base64 is a string
                if (typeof _base64 !== 'string') {
                    throw new TypeError('Expected a base64 string');
                }

                // Remove any whitespace in the base64 string
                const cleanedBase64 = _base64.replace(/\s/g, '');
                const binary = atob(cleanedBase64);
                const len = binary.length;
                const buffer = new ArrayBuffer(len);
                const view = new Uint8Array(buffer);

                for (let i = 0; i < len; i++) {
                    view[i] = binary.charCodeAt(i);
                }

                const blob = new Blob([view], {type: _contentType});
                const dataUrl = URL.createObjectURL(blob);
                displayInNewWindow(dataUrl);
            } catch (e) {
                console.error('Error decoding base64 or displaying document:', e);
                alert('Failed to display the document due to an invalid document format.');
            }
        }

        function displayInNewWindow(dataUrl) {
            let width = window.innerWidth || document.documentElement.clientWidth || screen.width;
            let height = window.innerHeight || document.documentElement.clientHeight || screen.height;
            height = screen.height ? screen.height * 0.95 : height;
            let left = (width / 4);
            let top = '10';
            let win = window.open('', '', 'toolbar=0, location=0, directories=0, status=0, menubar=0, scrollbars=0, resizable=0, copyhistory=0, width=' + width / 1.75 + ', height=' + height + ', top=' + top + ', left=' + left);
            if (win === null) {
                alert(xl('Please allow popups for this site'));
            } else {
                win.document.write("<iframe width='100%' height='100%' style='border:none;' src='" + dataUrl + "'></iframe>");
            }
        }

        // SMS Fax status
        function retrieveMsgs(e = '', req = '') {
            try {
                top.restoreSession();
            } catch (error) {
                console.log('Session restore failed!', error);
            }

            if (e !== '') {
                e.preventDefault();
                e.stopPropagation();
            }

            let actionUrl = (serviceType === 'fax') ? 'getPending?type=fax' : '';
            if (serviceType === 'sms' && currentService == '1') { //RC
                actionUrl = 'getPending?type=sms';
            } else if (serviceType === 'sms') { //Twilio
                actionUrl = 'fetchSMSList?type=sms';
            }

            const datefrom = $('#fromdate').val();
            const dateto = $('#todate').val();

            // Cache DOM elements
            const brandElement = $(".brand");
            const rcvDetailsBody = $("#rcvdetails tbody");
            const sentDetailsBody = $("#sent-details tbody");
            const msgDetailsBody = $("#msgdetails tbody");

            // Start loading animation
            brandElement.addClass('fa fa-spinner fa-spin');
            rcvDetailsBody.empty();
            sentDetailsBody.empty();
            msgDetailsBody.empty();

            return $.post(actionUrl, {
                'type': serviceType,
                'pid': pid,
                'datefrom': datefrom,
                'dateto': dateto
            }, null, 'json').done(function (data) {
                brandElement.removeClass('fa fa-spinner fa-spin');

                if (data.error) {
                    alertMsg(data.error);
                    return false;
                }
                // Populate our cards
                rcvDetailsBody.append(data[0]);
                sentDetailsBody.append(data[1]);
                msgDetailsBody.append(data[2]);

                // Get call logs if the service type is SMS
                if (serviceType === 'sms') {
                    getLogs();
                }
            }).fail(function (xhr, status, error) {
                const message = `Error: ${error || 'Request to fetch pending new faxes failed with Unknown error!'}<br />Perhaps invalid or missing credentials. Verify, fix and try again if so.`;
                alertMsg(message, 10000);
                console.error('Request failed: ', status, error);
            }).always(function () {
                brandElement.removeClass('fa fa-spinner fa-spin');
            });
        }

        // Our Call Logs.
        function getLogs() {
            try {
                top.restoreSession();
            } catch (error) {
                console.log('Session restore failed!');
            }
            let actionUrl = 'getCallLogs';
            let id = pid;
            let datefrom = $('#fromdate').val();
            let dateto = $('#todate').val();

            $(".brand").addClass('fa fa-spinner fa-spin');
            return $.post(actionUrl, {
                'type': serviceType,
                'pid': pid,
                'datefrom': datefrom,
                'dateto': dateto
            }).done(function (data) {
                let err = (data.search(/Exception/) !== -1 ? 1 : 0);
                if (!err) {
                    err = (data.search(/Error:/) !== -1 ? 1 : 0);
                }
                if (err) {
                    alertMsg(data);
                }
                $("#logdetails tbody").empty().append(data);
                // Get SMS appointments notifications
                if (serviceType === 'sms') {
                    getNotificationLog();
                }
            }).always(function () {
                $(".brand").removeClass('fa fa-spinner fa-spin');
            });
        }

        function getNotificationLog() {
            try {
                top.restoreSession();
            } catch (error) {
                console.log('Session restore failed!');
            }
            let actionUrl = 'getNotificationLog';
            let id = pid;
            let datefrom = $('#fromdate').val() + " 00:00:01";
            let dateto = $('#todate').val() + " 23:59:59";

            $(".brand").addClass('fa fa-spinner fa-spin');
            return $.post(actionUrl, {
                'type': serviceType,
                'pid': pid,
                'datefrom': datefrom,
                'dateto': dateto
            }).done(function (data) {
                let err = (data.search(/Exception/) !== -1 ? 1 : 0);
                if (!err) {
                    err = (data.search(/Error:/) !== -1 ? 1 : 0);
                }
                if (err) {
                    alertMsg(data);
                }
                $("#alertdetails tbody").empty().append(data);
            }).always(function () {
                $(".brand").removeClass('fa fa-spinner fa-spin');
            });
        }

        function getSelResource() {
            return $('#resource option:selected').val();
        }

        function messageShow(id) {
            $("." + id).toggleClass("d-none");
        }

        function messageReply(phone) {
            let btnClose = <?php echo xlj("Cancel"); ?>;
            let title = <?php echo xlj("Message Reply"); ?>;
            let url = top.webroot_url + '/interface/modules/custom_modules/oe-module-faxsms/contact.php?type=sms&isSMS=1&recipient=' +
                encodeURIComponent(phone);
            // leave dialog name param empty so send dialogs can cascade.
            dlgopen(url, '', 'modal-sm', 600, '', title, {
                buttons: [
                    {text: btnClose, close: true, style: 'secondary btn-sm'}
                ]
            });
        }

        function toggleDetail(id) {
            if (id === 'collapse') {
                $(".collapse-all").addClass("d-none");
                $(".fa-eye-slash").removeClass('fa-eye-slash').addClass('fa-eye');
                return false;
            }
            $(id).toggleClass("d-none");
            $(event.currentTarget).toggleClass('fa-eye-slash fa-eye');
            return false;
        }

        function notifyUser(e, faxId, recordId, pid = 0) {
            e.preventDefault();
            let btnClose = <?php echo xlj("Exit"); ?>;
            let url = top.webroot_url +
                '/interface/main/messages/messages.php?showall=no&task=addnew&form_active=1&gptype=9&attach=' +
                encodeURIComponent(recordId) + "&jobId=" + encodeURIComponent(faxId) + "&pid=" + encodeURIComponent(pid);
            dlgopen(url, 'attach_fax', 'modal-mlg', 800, '', '', {buttons: [{text: btnClose, close: true, style: 'primary'}]});
            return false;
        }

        function createPatient(e, faxId, recordId, data) {
            e.preventDefault();
            let btnClose = <?php echo xlj("Exit"); ?>;
            let url = './library/utility.php?pop_add_new=1&recId=' +
                encodeURIComponent(recordId) + "&jobId=" + encodeURIComponent(faxId) + "&data=" + encodeURIComponent(data);
            dlgopen(url, 'create_patient', 'modal-md', 'full', '', '', {
                    buttons: [{text: btnClose, close: true, style: 'primary'}],
                    sizeHeight: 'full'
                }
            );
            return false;
        }
        // drop bucket
        const queueMsg = '' + <?php echo xlj('Fax Queue. Drop files or Click here for Fax Contact form.') ?>;
        Dropzone.autoDiscover = false;
        $(function () {
            var fileTypes = '';
            if (currentService === '3') {
                fileTypes = "application/pdf, image/*";
            }
            const faxQueue = new Dropzone("#faxQueue", {
                paramName: 'fax',
                url: 'faxProcessUploads?type=fax',
                dictDefaultMessage: queueMsg,
                clickable: true,
                enqueueForUpload: true,
                maxFilesize: 100,
                acceptedFiles: fileTypes,
                uploadMultiple: false,
                addRemoveLinks: true,
                init: function (e) {
                    let ofile = '';
                    this.on("addedfile", function (file) {
                        console.log('new file added ', file);
                        ofile = file;
                    });
                    this.on("sending", function (file) {
                        console.log('upload started ', file);
                        $('.meter').show();
                    });
                    this.on("success", function (file, response) {
                        let thisFile = response;
                        console.log('upload success ', thisFile);
                        sendFax(thisFile, 'queue');
                    });
                    this.on("queuecomplete", function (progress) {
                        $('.meter').delay(999).slideUp(999);
                    });
                    this.on("removedfile", function (file) {
                        console.log(file);
                    });
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            const deleteSelectedFaxesButton = document.querySelectorAll('.delete-selected-items');
            deleteSelectedFaxesButton.forEach(button => {
                button.addEventListener('click', function () {
                    const selectedFaxes = document.querySelectorAll('.delete-fax-checkbox:checked');
                    const faxIds = Array.from(selectedFaxes).map(checkbox => checkbox.value);

                    if (faxIds.length === 0) {
                        alert('No faxes selected for deletion.');
                        return;
                    }

                    if (!confirm(`Are you sure you want to delete ${faxIds.length} faxes?`)) {
                        return;
                    }

                    faxIds.forEach(id => {
                        getDocument('', null, id, 'false', 'true', true);
                    });

                    setTimeout(retrieveMsgs, 1000);
                    return false;
                });
            });
        });
    </script>
</head>
<body class="body_top">
    <div class="sticky-top">
        <nav class="navbar navbar-expand-xl navbar-light bg-light">
            <div class="container">
                <a class="navbar-brand" href="#"><h4><?php echo $title; ?><i class="brand ml-1" id="brand-top"></i></h4></a>
                <button type="button" class="bg-primary navbar-toggler mr-auto" data-toggle="collapse" data-target="#nav-header-collapse">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="nav-header-collapse">
                    <form class="navbar-form navbar-left form-inline" method="GET" role="search">
                        <div class="form-group">
                            <label for="fromdate" class="mx-1 font-weight-bolder" for="formdate"><?php echo xlt('Activities From Date') ?>:</label>
                            <input type="text" id="fromdate" name="fromdate" class="form-control input-sm datepicker" placeholder="YYYY-MM-DD" value=''>
                        </div>
                        <div class="form-group">
                            <label class="mx-1 font-weight-bolder" for="todate"><?php echo xlt('To Date') ?>:</label>
                            <input type="text" id="todate" name="todate" class="form-control input-sm datepicker" placeholder="YYYY-MM-DD" value=''>
                        </div>
                        <div class="form-group">
                            <button type="button" class="btn btn-primary btn-search" onclick="retrieveMsgs(event,this)" title="<?php echo xla('Click to get current history.') ?>"></button>
                        </div>
                    </form>
                    <div class="nav-item dropdown ml-auto">
                        <button class="btn btn-lg btn-link dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                            <?php echo xlt('Account Actions'); ?><span class="caret"></span>
                        </button>
                        <div class="dropdown-menu" role="menu">
                            <a class="dropdown-item" href="#" onclick="doSetup(event)"><?php echo xlt('Account Credentials'); ?></a>
                            <?php if ($serviceType == 'sms') { ?>
                                <a class="dropdown-item" href="#" onclick="popNotify('', './library/rc_sms_notification.php?dryrun=1&site=<?php echo attr($_SESSION['site_id']) ?>')"><?php echo xlt('Test SMS Reminders'); ?></a>
                                <a class="dropdown-item" href="#" onclick="popNotify('live', './library/rc_sms_notification.php?site=<?php echo attr($_SESSION['site_id']) ?>')"><?php echo xlt('Send SMS Reminders'); ?></a>
                            <?php } ?>
                            <a class="dropdown-item etherfax" href="#" onclick="docInfo(event, portalUrl)"><?php echo xlt('Portal Gateway'); ?></a>
                        </div>
                        <button type="button" class="nav-item etherfax d-none btn btn-secondary btn-transmit" onclick="docInfo(event, portalUrl)"><?php echo xlt('Account Portal'); ?>
                        </button>
                    </div>
                </div><!-- /.navbar-collapse -->
        </nav>
    </div>
    <div class="container-fluid main-container mt-3">
        <div class="row">
            <div class="col-md-10 offset-md-1 content">
                <h3><?php echo xlt("Activities") ?><i class="brand ml-1" id="brand"></i></h3>
                <div id="dashboard" class="card">
                    <!-- Nav tabs -->
                    <ul id="tab-menu" class="nav nav-pills" role="tablist">
                        <li class="nav-item" role="tab">
                            <a class="nav-link active" href="#received" aria-controls="received" role="tab" data-toggle="tab"><?php echo xlt("Received") ?>
                                <span class="fa fa-redo ml-1" onclick="retrieveMsgs('', this)"
                                    title="<?php echo xla('Click to refresh using current date range. Refreshing just this tab.') ?>">
                                </span>
                            </a>
                        </li>
                        <li class="nav-item" role="tab"><a class="nav-link" href="#sent" aria-controls="sent" role="tab" data-toggle="tab"><?php echo xlt("Sent") ?></a></li>
                        <li class="nav-item rc-fax-hide etherfax-hide" role="tab"><a class="nav-link" href="#messages" aria-controls="messages" role="tab" data-toggle="tab"><?php echo xlt("SMS Log") ?></a></li>
                        <li class="nav-item" role="tab"><a class="nav-link" href="#logs" aria-controls="logs" role="tab" data-toggle="tab"><?php echo xlt("Call Log") ?></a></li>
                        <li class="nav-item etherfax-hide rc-fax-hide" role="tab">
                            <a class="nav-link" href="#alertlogs" aria-controls="alertlogs" role="tab" data-toggle="tab"><?php echo xlt("Reminder Notifications Log") ?><span class="fa fa-redo ml-1" onclick="getNotificationLog(event, this)"
                                    title="<?php echo xla('Click to refresh using current date range. Refreshing just this tab.') ?>"></span></a>
                        </li>
                        <li class="nav-item sms-hide etherfax" role="tab"><a class="nav-link" href="#upLoad" aria-controls="logs" role="tab" data-toggle="tab"><?php echo xlt("Fax Drop Box") ?></a></li>
                    </ul>
                    <!-- Tab panes -->
                    <?php if ($service != '1') { ?>
                        <div class="tab-content">
                            <div role="tabpanel" class="container-fluid tab-pane fade" id="received">
                                <?php if ($service == '3') { ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm" id="rcvdetails">
                                            <thead>
                                            <tr>
                                                <th><?php echo xlt("Time") ?></th>
                                                <th><?php echo xlt("Caller #") ?></th>
                                                <th><?php echo xlt("Caller Id") ?></th>
                                                <th><?php echo xlt("To") ?></th>
                                                <th><?php echo xlt("Pages") ?></th>
                                                <th><?php echo xlt("Length") ?></th>
                                                <th><?php echo xlt("Status") ?></th>
                                                <th><a role='button' href='javascript:void(0)' class='btn btn-link fa fa-eye' onclick="toggleDetail('collapse')"></a><?php echo xlt("Extracted") ?></th>
                                                <th><?php echo xlt("MRN Match") ?></th>
                                                <th><?php echo xlt("Actions") ?></th>
                                                <th><i role="button" id="delete-selected-received" title="<?php echo xla("Delete selected fax documents") ?>" class="delete-selected-items text-danger fa fa-trash"></i></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td><?php echo xlt("No Items Try Refresh") ?></td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php } else { // all others ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm" id="rcvdetails">
                                            <thead>
                                            <tr>
                                                <th><?php echo xlt("Time") ?></th>
                                                <th><?php echo xlt("Type") ?></th>
                                                <th class=""><?php echo xlt("Message") ?></th>
                                                <th><?php echo xlt("From") ?></th>
                                                <th><?php echo xlt("To") ?></th>
                                                <th><?php echo xlt("Result") ?></th>
                                                <th><?php echo xlt("Reply") ?></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td><?php echo xlt("No Items Try Refresh") ?></td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php } ?>
                            </div>
                            <div role="tabpanel" class="container-fluid tab-pane fade" id="sent">
                                <?php if ($service == '3') { ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm" id="sent-details">
                                            <thead>
                                            <tr>
                                                <th><?php echo xlt("Time") ?></th>
                                                <th><?php echo xlt("Caller #") ?></th>
                                                <th><?php echo xlt("Caller Id") ?></th>
                                                <th><?php echo xlt("To") ?></th>
                                                <th><?php echo xlt("Pages") ?></th>
                                                <th><?php echo xlt("Length") ?></th>
                                                <th><?php echo xlt("Status") ?></th>
                                                <th><?php echo xlt("Actions") ?></th>
                                                <th><i role="button" id="delete-selected-sent" title="<?php echo xlt("Delete selected fax documents") ?>" class="delete-selected-items text-danger fa fa-trash"></i></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td><?php echo xlt("No Items Try Refresh") ?></td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php } else { ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped" id="sent-details">
                                            <thead>
                                            <tr>
                                                <th><?php echo xlt("Start Time") ?></th>
                                                <th class="twilio"><?php echo xlt("Price") ?></th>
                                                <th class="etherfax"><?php echo xlt("Type") ?></th>
                                                <th><?php echo xlt("Message") ?></th>
                                                <th><?php echo xlt("From") ?></th>
                                                <th><?php echo xlt("To") ?></th>
                                                <th><?php echo xlt("Result") ?></th>
                                                <th class="etherfax"><?php echo xlt("Download") ?></th>
                                                <th class="twilio"><?php echo xlt("Reply") ?></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td><?php echo xlt("No Items Try Refresh") ?></td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php } ?>
                            </div>
                            <div role="tabpanel" class="container-fluid tab-pane fade" id="messages">
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped" id="msgdetails">
                                        <thead>
                                        <tr>
                                            <th><?php echo xlt("Date") ?></th>
                                            <th><?php echo xlt("Type") ?></th>
                                            <th><?php echo xlt("From") ?></th>
                                            <th><?php echo xlt("To") ?></th>
                                            <th><?php echo xlt("Result") ?></th>
                                            <th class="other"><?php echo xlt("Download") ?></th>
                                            <th><?php echo xlt("View") ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td><?php echo xlt("No Items Try Refresh") ?></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div role="tabpanel" class="container-fluid tab-pane fade" id="logs">
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped" id="logdetails">
                                        <thead>
                                        <tr>
                                            <th><?php echo xlt("Date") ?></th>
                                            <th><?php echo xlt("Type") ?></th>
                                            <th><?php echo xlt("From") ?></th>
                                            <th><?php echo xlt("To") ?></th>
                                            <th><?php echo xlt("Action") ?></th>
                                            <th><?php echo xlt("Result") ?></th>
                                            <th><?php echo xlt("Id") ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td><?php echo xlt("No Items Try Refresh") ?></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div role="tabpanel" class="container-fluid tab-pane fade etherfax-hide" id="alertlogs">
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped" id="alertdetails">
                                        <thead>
                                        <tr>
                                            <th><?php echo xlt("Id") ?></th>
                                            <th><?php echo xlt("Date Sent") ?></th>
                                            <th><?php echo xlt("Appt Date Time") ?></th>
                                            <th><?php echo xlt("Patient") ?></th>
                                            <th><?php echo xlt("Message") ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td><?php echo xlt("No Items") ?></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div role="tabpanel" class="container-fluid tab-pane fade" id="upLoad">
                                <div class="panel container-fluid">
                                    <div id="fax-queue-container">
                                        <div id="fax-queue">
                                            <form id="faxQueue" method="post" enctype="multipart/form-data" class="dropzone"></form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php }
                    if ($service == '1') { ?>
                        <div class="tab-content">
                            <div role="tabpanel" class="container-fluid tab-pane fade" id="received">
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped" id="rcvdetails">
                                        <thead>
                                        <tr>
                                            <th><?php echo xlt("Start Time") ?></th>
                                            <th><?php echo xlt("End Time") ?></th>
                                            <th><?php echo xlt("Pages") ?></th>
                                            <th><?php echo xlt("From") ?></th>
                                            <th><?php echo xlt("To") ?></th>
                                            <th><?php echo xlt("Status") ?></th>
                                            <th><?php echo xlt("Actions") ?></th>
                                            <th><i role="button" id="delete-selected-received" title="<?php echo xla("Delete selected fax documents") ?>" class="delete-selected-items text-danger fa fa-trash"></i></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td><?php echo xlt("No Items Try Refresh") ?></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div role="tabpanel" class="container-fluid tab-pane fade" id="sent">
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped" id="sent-details">
                                        <thead>
                                        <tr>
                                            <th><?php echo xlt("Start Time") ?></th>
                                            <th><?php echo xlt("End Time") ?></th>
                                            <th><?php echo xlt("Pages") ?></th>
                                            <th><?php echo xlt("From") ?></th>
                                            <th><?php echo xlt("To") ?></th>
                                            <th><?php echo xlt("Status") ?></th>
                                            <th><?php echo xlt("Actions") ?></th>
                                            <th><i role="button" id="delete-selected-received" title="<?php echo xla("Delete selected fax documents") ?>" class="delete-selected-items text-danger fa fa-trash"></i></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td><?php echo xlt("No Items Try Refresh") ?></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div role="tabpanel" class="container-fluid tab-pane fade d-none" id="messages">
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped" id="msgdetails">
                                        <thead>
                                        <tr>
                                            <th><?php echo xlt("Date") ?></th>
                                            <th><?php echo xlt("Type") ?></th>
                                            <th><?php echo xlt("From") ?></th>
                                            <th><?php echo xlt("To") ?></th>
                                            <th><?php echo xlt("Result") ?></th>
                                            <th class="twilio"><?php echo xlt("Download") ?></th>
                                            <th class="ringcentral"><?php echo xlt("Message") ?></th>
                                            <th><?php echo xlt("View") ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td><?php echo xlt("No Items Try Refresh") ?></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div role="tabpanel" class="container-fluid tab-pane fade" id="logs">
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped" id="logdetails">
                                        <thead>
                                        <tr>
                                            <th><?php echo xlt("Date") ?></th>
                                            <th><?php echo xlt("Type") ?></th>
                                            <th><?php echo xlt("From") ?></th>
                                            <th><?php echo xlt("To") ?></th>
                                            <th><?php echo xlt("Action") ?></th>
                                            <th><?php echo xlt("Result") ?></th>
                                            <th><?php echo xlt("Id") ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td><?php echo xlt("No Items Try Refresh") ?></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div role="tabpanel" class="container-fluid tab-pane fade rc-fax-hide" id="alertlogs">
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped" id="alertdetails">
                                        <thead>
                                        <tr>
                                            <th><?php echo xlt("Id") ?></th>
                                            <th><?php echo xlt("Date Sent") ?></th>
                                            <th><?php echo xlt("Appt Date Time") ?></th>
                                            <th><?php echo xlt("Patient") ?></th>
                                            <th><?php echo xlt("Message") ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td><?php echo xlt("No Items") ?></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div role="tabpanel" class="container-fluid tab-pane sms-hide fade in active" id="upLoad">
                                <div class="panel container-fluid">
                                    <div id="fax-queue-container">
                                        <div id="fax-queue">
                                            <form id="faxQueue" method="post" enctype="multipart/form-data" class="dropzone"></form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    </div><!-- /.navbar-container -->
</body>
</html>
