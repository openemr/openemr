/**
 * application_view_model.js
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2016 Kevin Yeh <kevin.y@integralemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

var app_view_model={};

app_view_model.application_data={};

app_view_model.application_data.tabs=new tabs_view_model();

app_view_model.application_data.patient=ko.observable(null);

app_view_model.application_data.user=ko.observable(null);

app_view_model.application_data.therapy_group=ko.observable(null);

app_view_model.attendant_template_type=ko.observable('patient-data-template');

app_view_model.responsiveDisplay=null;

// Webcam capture instance for patient photo
app_view_model.webcamCapture = null;

/**
 * Handle click on patient picture
 * If photo exists, show dialog with View/Update options; otherwise open webcam capture
 */
app_view_model.handlePatientPictureClick = function(data, event) {
    var patient = app_view_model.application_data.patient();
    if (!patient) {
        return;
    }

    var pid = patient.pid();
    if (!pid) {
        return;
    }

    // Check if the image is the default fallback (set by onError handler)
    var img = event.target;
    var isDefaultImage = img.src.indexOf('patient-picture-default.png') !== -1;

    if (isDefaultImage) {
        // No photo exists, open webcam capture directly
        app_view_model.openPatientPhotoCapture();
    } else {
        // Photo exists, show dialog with options
        app_view_model.showPatientPhotoDialog(pid);
    }
};

/**
 * Show dialog with View/Update options for patient photo
 * @param {number} pid - Patient ID
 */
app_view_model.showPatientPhotoDialog = function(pid) {
    var viewLabel = window.top.xl ? window.top.xl('View Photo') : 'View Photo';
    var updateLabel = window.top.xl ? window.top.xl('Update Photo') : 'Update Photo';

    var photoUrl = webroot_url + '/controller.php?document&retrieve' +
        '&patient_id=' + encodeURIComponent(pid) +
        '&document_id=-1' +
        '&as_file=false' +
        '&original_file=true' +
        '&disable_exit=false' +
        '&show_original=true' +
        '&context=patient_picture';

    dlgopen('', 'patientPhotoOptions', 'modal-sm', 200, '', '', {
        buttons: [
            {
                text: viewLabel,
                close: true,
                style: 'primary btn-sm',
                click: function() {
                    window.open(photoUrl, '_blank', 'menubar=no,location=no,resizable=yes,scrollbars=yes,status=no');
                }
            },
            {
                text: updateLabel,
                close: true,
                style: 'secondary btn-sm',
                click: function() {
                    app_view_model.openPatientPhotoCapture();
                }
            }
        ],
        type: 'alert',
        html: '<div class="text-center p-2">' +
              (window.top.xl ? window.top.xl('What would you like to do?') : 'What would you like to do?') +
              '</div>'
    });
};

/**
 * Open the patient photo capture modal
 */
app_view_model.openPatientPhotoCapture = function() {
    var patient = app_view_model.application_data.patient();
    if (!patient) {
        return;
    }

    var pid = patient.pid();
    if (!pid) {
        return;
    }

    // Initialize webcam capture if not already done
    if (!app_view_model.webcamCapture) {
        app_view_model.webcamCapture = new WebcamCapture({
            triggerSelector: null, // No trigger button, we open programmatically
            onPhotoUsed: function(base64Data) {
                app_view_model.savePatientPhoto(pid, base64Data);
            }
        });
    }

    // Open the modal
    app_view_model.webcamCapture.openModal();
};

/**
 * Save patient photo via AJAX
 * @param {number} pid - Patient ID
 * @param {string} base64Data - Base64 encoded image data
 */
app_view_model.savePatientPhoto = function(pid, base64Data) {
    $.ajax({
        url: webroot_url + '/library/ajax/save_patient_photo.php',
        type: 'POST',
        data: {
            csrf_token: csrf_token_js,
            pid: pid,
            photo_data: base64Data
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Refresh the patient picture by updating the observable
                var patient = app_view_model.application_data.patient();
                if (patient) {
                    // Force refresh by resetting the patient_picture computed
                    // The simplest way is to trigger a re-render by updating the patient
                    var currentPid = patient.pid();
                    // Add a cache-busting timestamp to force image reload
                    var newPictureUrl = webroot_url + '/controller.php' +
                        '?document&retrieve' +
                        '&patient_id=' + encodeURIComponent(currentPid) +
                        '&document_id=-1' +
                        '&as_file=false' +
                        '&original_file=true' +
                        '&disable_exit=false' +
                        '&show_original=true' +
                        '&context=patient_picture' +
                        '&_t=' + Date.now();
                    // Update the image directly
                    $('.patientPicture img').attr('src', newPictureUrl);
                }
            } else {
                alert(response.error || 'Failed to save photo');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error saving patient photo:', error);
            alert('Error saving photo: ' + error);
        }
    });
};
