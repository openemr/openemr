/*
 * messageUI.js
 *
 * Client logic for the Fax/SMS message dashboard (messageUI.php). Extracted
 * from the page's inline <script> so it can be linted/cached on its own.
 *
 * Server-provided globals (defined by the inline bootstrap in messageUI.php
 * before this file loads): pid, portalUrl, currentService, serviceType,
 * csrfToken, and the ServiceType constants. Translations use the global
 * xl() helper. The datetimepicker init and the email JAVASCRIPT_READY
 * notification dispatch stay inline because they require server rendering.
 *
 * @package   OpenEMR
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

const sendFax = function (filePath, from = '') {
    let btnClose = xl("Cancel");
    let title = xl("Send To Contact");
    let url = top.webroot_url + '/interface/modules/custom_modules/oe-module-faxsms/contact.php?type=fax&isDocuments=0&isQueue=' +
        encodeURIComponent(from) + '&file=' + encodeURIComponent(filePath);
    // leave dialog name param empty so send dialogs can cascade.
    dlgopen(url, '', 'modal-sm', 600, '', title, { // dialog auto restores session cookie
        buttons: [
            {text: btnClose, close: true, style: 'secondary btn-sm'}
        ],
        resolvePromiseOn: 'close',
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
    let msg = xl('Your Account Portal');
    dlgopen(ppath, '_blank', 1240, 900, true, msg)
};

const popNotify = function (e, ppath) {
    try {
        top.restoreSession();
    } catch (error) {
        console.log('Session restore failed!');
    }
    let msg = xl('Are you sure you wish to send all scheduled reminders now?');
    if (e === 'live') {
        let yn = confirm(msg);
        if (!yn) {
            return false
        }
    }
    let msg1 = xl('Appointment Reminder Alerts!');
    dlgopen(ppath, '_blank', 1240, 900, true, msg1)
};

const doSetup = function (e) {
    try {
        top.restoreSession();
    } catch (error) {
        console.log('Session restore failed!');
    }
    e.preventDefault();
    let url = top.webroot_url + '/interface/modules/custom_modules/oe-module-faxsms/' +
        (currentService === ServiceType.RINGCENTRAL ? 'setup_rc.php' : 'setup.php');
    let msg = xl('Credentials and Notifications');
    dlgopen('', 'setup', 'modal-md', 700, '', msg, {
        buttons: [
            {text: 'Cancel', close: true, style: 'secondary  btn-sm'}
        ],
        url: url + "?type=" + encodeURIComponent(serviceType)
    });
};

const forwardFax = function (e, docid = '', filePath = '', details = []) {
    let btnClose = xl("Cancel");
    let title = xl("Forward Fax to Email, Fax recipient or both.");
    let url = top.webroot_url +
        '/interface/modules/custom_modules/oe-module-faxsms/contact.php?type=fax&mode=forward&isDocuments=0&docid=' +
        encodeURIComponent(docid);
    // leave dialog name param empty so send dialogs can cascade.
    dlgopen(url, '', 'modal-md', 800, '', title, { // dialog restores session
        buttons: [{text: btnClose, close: true, style: 'secondary btn-sm'}]
    });
    return false;
};

// Store the current fax id for assignment; used by setpatient callback
let currentFaxForAssignment = null;

// Callback that patient finder calls on opener
function setpatient(pid, lname, fname, dob) {
    if (!currentFaxForAssignment) {
        alertMsg(xl('No fax selected for assignment'));
        return;
    }
    $.post('assignFax?type=fax', {
        'fax_id': currentFaxForAssignment,
        'patient_id': pid,
        'csrf_token_form': csrfToken
    }, function (response) {
        if (response && response.success) {
            alertMsg(xl('Fax assigned successfully'));
            retrieveMsgs();
        } else {
            alertMsg((response && response.error) || xl('Failed to assign fax'));
        }
    }, 'json').fail(function () {
        alertMsg(xl('Error assigning fax'));
    }).always(function () {
        currentFaxForAssignment = null;
    });
}

const assignFaxToPatient = function (faxQueueId) {
    try {
        top.restoreSession();
    } catch (error) {
        console.log('Session restore failed!');
    }
    currentFaxForAssignment = faxQueueId;
    // Open standard patient finder which calls opener.setpatient(...)
    dlgopen('../../../main/calendar/find_patient_popup.php', '_blank', 750, 550, false, xl('Select Patient'));
};

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
            'delete': deleteFlag,
            'csrf_token_form': csrfToken
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
                    content: base64,
                    csrf_token_form: csrfToken
                })
            }).then(response => response.json()).then(result => {
                // Download the file. result.url is temp file path of tiff to pdf by image conversion in JS or imagick.
                if (result.success) {
                    location.href = "disposeDocument?type=fax&action=download&file_path=" + encodeURIComponent(result.url) + "&csrf_token_form=" + encodeURIComponent(csrfToken);
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

// Helper: Convert base64 to ArrayBuffer.
function base64ToArrayBuffer(base64) {
    const binaryString = window.atob(base64);
    const len = binaryString.length;
    const bytes = new Uint8Array(len);
    for (let i = 0; i < len; i++) {
        bytes[i] = binaryString.charCodeAt(i);
    }
    return bytes.buffer;
}

async function convertTiffToImages(tiffData, mime = 'image/jpeg') {
    try {
        // Convert base64 string to ArrayBuffer if necessary.
        if (typeof tiffData === 'string') {
            if (tiffData.indexOf('base64,') > -1) {
                tiffData = tiffData.split('base64,')[1];
            }
            tiffData = base64ToArrayBuffer(tiffData);
        }

        console.log("TIFF ArrayBuffer byteLength:", tiffData.byteLength);

        // Create a copy of the buffer to avoid issues if UTIF.js detaches the original.
        const bufferCopy = tiffData.slice(0);

        // Decode the TIFF file to get IFDs (pages).
        const ifds = UTIF.decode(bufferCopy);
        if (!ifds || ifds.length === 0) {
            throw new Error("No IFDs found in TIFF data.");
        }
        console.log("Decoded IFDs:", ifds);

        // Attempt to decode images for all IFDs.
        UTIF.decodeImage(bufferCopy, ifds);

        const imagePromises = ifds.map((ifd, index) => {
            return new Promise((resolve, reject) => {
                try {
                    // If the IFD's data is empty, force decode this IFD.
                    if (!ifd.data || ifd.data.length === 0) {
                        UTIF.decodeImage(bufferCopy, ifd);
                    }

                    // Get RGBA pixel data.
                    const rgba = UTIF.toRGBA8(ifd);
                    if (!rgba || rgba.length === 0) {
                        return reject(new Error(`No pixel data for IFD index ${index}.`));
                    }

                    const width = ifd.t256 || ifd.width;
                    const height = ifd.t257 || ifd.height;
                    if (!width || !height) {
                        return reject(new Error(`Missing dimensions for IFD index ${index}.`));
                    }

                    // Create a canvas and draw the image.
                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    const imageData = new ImageData(new Uint8ClampedArray(rgba), width, height);
                    ctx.putImageData(imageData, 0, 0);

                    // Convert the canvas to a data URL.
                    const dataURL = canvas.toDataURL(mime);
                    resolve(dataURL);
                } catch (error) {
                    console.error(`Error processing IFD index ${index}:`, error);
                    reject(error);
                }
            });
        });

        return Promise.all(imagePromises);
    } catch (error) {
        console.error("Failed to convert TIFF to images using UTIF.js:", error);
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

    const actionUrl = {
        fax: 'getPending?type=fax',
        sms: currentService === ServiceType.RINGCENTRAL ? 'getPending?type=sms' : 'fetchSMSList?type=sms',
        email: 'fetchEmailList?type=email',
    }[serviceType] ?? '';

    const datefrom = $('#fromdate').val();
    const dateto = $('#todate').val();

    // Cache DOM elements
    const rcvDetailsBody = $("#rcvdetails tbody");
    const sentDetailsBody = $("#sent-details tbody");
    const msgDetailsBody = $("#msgdetails tbody");

    // Start loading animation
    $(".brand").addClass('fa fa-spinner fa-spin');
    rcvDetailsBody.empty();
    sentDetailsBody.empty();
    msgDetailsBody.empty();

    $.post(actionUrl, {
        'type': serviceType,
        'pid': pid,
        'datefrom': datefrom,
        'dateto': dateto
    }, null, 'json').done(function (data) {
        if (data.error) {
            $(".brand").removeClass('fa fa-spinner fa-spin');
            alertMsg(data.error);
            return false;
        }
        // Populate our cards
        rcvDetailsBody.append(data[0]);
        sentDetailsBody.append(data[1]);
        msgDetailsBody.append(data[2]);

        if (serviceType) {
            getLogs();
        }
    }).fail(function (xhr, status, error) {
        const message = `Error: ${error || 'Request to fetch pendings failed with Unknown error!'}`;
        alertMsg(message, 10000);
    }).always(function () {
        $(".brand").removeClass('fa fa-spinner fa-spin');
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
        // Get appointments notifications
        if (serviceType === 'sms' || serviceType === 'email') {
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
    let actionUrl = 'getNotificationLog?type=' + serviceType;
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

function messageShow(id, uri) {
    try {
        top.restoreSession();
    } catch (error) {
        console.log('Session restore failed!');
    }

    $(".brand").addClass('fa fa-spinner fa-spin');
    let actionUrl = 'fetchTextMessage?type=sms';
    $.post(actionUrl, {
        'id': id,
        'uri': uri
    }, null, 'json').done(function (data) {
        $(".brand").removeClass('fa fa-spinner fa-spin');
        if (data.error) {
            alertMsg(data.error);
            return false;
        }
        $("." + id).empty().append(data);
    }).fail(function (xhr, status, error) {
        const message = `Error: ${error || 'Request to fetch message failed with Unknown error!'}`;
        alertMsg(message, 10000);
        console.error('Request failed: ', status, error);
    }).always(function () {
        $(".brand").removeClass('fa fa-spinner fa-spin');
    });
}

function messageReply(phone) {
    let btnClose = xl("Cancel");
    let title = xl("Message Reply");
    let url = top.webroot_url + '/interface/modules/custom_modules/oe-module-faxsms/contact.php?type=sms&isSMS=1&recipient=' +
        encodeURIComponent(phone);
    // leave dialog name param empty so send dialogs can cascade.
    dlgopen(url, '', 'modal-sm', 700, '', title, {
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
    let btnClose = xl("Exit");
    let url = top.webroot_url +
        '/interface/main/messages/messages.php?showall=no&task=addnew&form_active=1&gptype=9&attach=' +
        encodeURIComponent(recordId) + "&jobId=" + encodeURIComponent(faxId) + "&pid=" + encodeURIComponent(pid);
    dlgopen(url, 'attach_fax', 'modal-mlg', 800, '', '', {buttons: [{text: btnClose, close: true, style: 'primary'}]});
    return false;
}

function createPatient(e, faxId, recordId, data) {
    e.preventDefault();
    let btnClose = xl("Exit");
    let url = './library/utility.php?pop_add_new=1&recId=' +
        encodeURIComponent(recordId) + "&jobId=" + encodeURIComponent(faxId) + "&data=" + encodeURIComponent(data);
    dlgopen(url, 'create_patient', 'modal-md', 'full', '', '', {
            buttons: [{text: btnClose, close: true, style: 'primary'}],
            sizeHeight: 'full'
        }
    );
    return false;
}

function rc_enable_popup() {
    //alert('RC Testing...');
    $(".rc_enable_popup_btn_loader").addClass('fa fa-spinner fa-spin');

    let actionUrl = 'install?type=' + serviceType;
    let data = [];
    return $.post(actionUrl,
        {'csrf_token_form': csrfToken}, function () {
        }, 'json').done(function (data) {
        console.log(data);
        $(".rc_enable_popup_btn_loader").removeClass('fa fa-spinner fa-spin');
        if (data.error) {
            alertMsg(data.error);
            return false;
        }
        if (data.msg) {
            $(".rc_enable_popup_btn_loader").html(data.msg);
        }
    });
}

// drop bucket
const queueMsg = '' + xl('Fax Queue. Drop files or Click here for Fax Contact form.');
Dropzone.autoDiscover = false;
$(function () {
    if (!document.querySelector('#faxQueue')) {
        return;
    }
    var fileTypes = '';
    if (currentService === ServiceType.ETHERFAX) {
        fileTypes = "application/pdf, image/*";
    }
    const faxQueue = new Dropzone("#faxQueue", {
        paramName: 'fax',
        url: 'faxProcessUploads?type=fax',
        params: {csrf_token_form: csrfToken},
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
