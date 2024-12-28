<?php

/**
 * Encounter form for entering clinical data as a scanned document.
 * Added webcam capture option. 2024
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Tyler Wrenn <tyler@tylerwrenn.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2006-2013 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/api.inc.php");
require_once("$srcdir/forms.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

$row = array();

if (!$encounter) { // comes from globals.php
    die("Internal error: we do not seem to be in an encounter!");
}

if (!empty($_POST['csrf_token_form']) && !CsrfUtils::verifyCsrfToken($_POST['csrf_token_form'])) {
    die("Invalid CSRF token");
}

// Gracefully handle form exit submission
if (($_POST['delete'] ?? null) == 'delete' || ($_POST['back'] ?? null) == 'back') {
    formHeader("Redirecting....");
    formJump();
    formFooter();
    exit;
}

// Is ImageMagick Installed
$extensionLoaded = extension_loaded('imagick');
$isMagickInstalled = false;
$isMagickExtensionInstalled = false;
$magickVersion = $magickExtensionVersion = "";
// Check using exec
exec("magick -version", $output, $execReturnCode);
if ($execReturnCode === 0) {
    $magickVersion .= "System-level ImageMagick is installed.\n";
    $magickVersion .= "Version details:\n" . implode("\n", $output) . "\n";
    $isMagickInstalled = true;
} else {
    $magickVersion .= "System-level ImageMagick is not installed or not accessible.\n";
}
// Check using imagick
if ($extensionLoaded) {
    $imagick = new Imagick();
    $version = $imagick->getVersion();
    $magickExtensionVersion .= "PHP Imagick extension is installed.\n";
    $magickExtensionVersion .= "Version: " . $version['versionString'] . "\n";
    $isMagickExtensionInstalled = true;
} else {
    $magickVersion .= "PHP Imagick extension is not installed or not enabled.\n";
}

$formid = $_GET['id'] ?? '0';
$imagedir = $GLOBALS['OE_SITE_DIR'] . "/documents/" . check_file_dir_name($pid) . "/encounters";
$tmpName = $tmp_name = $imagePath = $imageUrl = "";

// Check if image data is sent. Generally, this is sent when the image is captured from the webcam.
// If the image data is sent, extract the base64 data, validate the file type, decode the base64 data,
// generate a unique file name, and save the file to system temp directory as if uploaded.
if (!empty($_POST['capturedImage'])) {
    $imageData = $_POST['capturedImage'] ?? "";
    // Extract the base64 data
    if (preg_match('/^data:image\/(png|jpg|jpeg);base64,/', $imageData, $type)) {
        $imageData = substr($imageData, strpos($imageData, ',') + 1);
        $type = strtolower($type[1]); // jpg, png, jpeg
        // Validate the file type
        if (!in_array($type, ['png', 'jpg', 'jpeg'])) {
            echo xlt('Invalid image type');
        }
        // Decode the base64 data
        $imageData = base64_decode($imageData);
        if ($imageData === false) {
            echo xlt('Base64 decode failed');
        }
        // Generate a unique file name
        $tempDir = sys_get_temp_dir();
        $tempFile = uniqid() . '.' . 'jpg';
        $tmpName = $tempDir . '/' . $tempFile;
        // Save the file
        if (!file_put_contents($tmpName, $imageData)) {
            echo xlt('Failed to save the image');
        }
    }
}

// If Save was clicked, save the preview image.
if ($_POST['bn_save'] ?? null) {
    // If the form ID is set, update the existing form.
    // else, insert a new form.
    if ($formid) {
        $query = "UPDATE form_scanned_notes SET notes = ? WHERE id = ?";
        sqlStatement($query, array($_POST['form_notes'], $formid));
    } else { // If adding a new form...
        $query = "INSERT INTO form_scanned_notes (notes) VALUES (?)";
        $formid = sqlInsert($query, array($_POST['form_notes']));
        addForm($encounter, "Scanned Notes", $formid, "scanned_notes", $pid, $userauthorized);
    }

    $imagePath = $imagedir . "/" . check_file_dir_name($encounter) . "_" . check_file_dir_name($formid) . ".jpg";

    // Upload new or replacement document.
    // Always convert it to jpeg.
    if ($_FILES['fileUpload']['size'] || !empty($tmpName)) {
        // If the patient's encounter image directory does not yet exist, create it.
        if (!is_dir($imagedir)) {
            mkdir($imagedir, 0755, true);
        }
        // Remove any previous image files for this encounter and form ID.
        for ($i = -1; true; ++$i) {
            $suffix = ($i < 0) ? "" : "-$i";
            $path = $imagedir . "/" . check_file_dir_name($encounter) . "_" . check_file_dir_name($formid) . check_file_dir_name($suffix) . ".jpg";
            if (is_file($path)) {
                unlink($path);
            } else {
                if ($i >= 0) {
                    break;
                }
            }
        }
        // Set the temporary file name between webcam or uploaded.
        $tmp_name = $tmpName;
        // If the file was uploaded, set the temporary file name.
        if ($_FILES['fileUpload']['size']) {
            $tmp_name = $_FILES['fileUpload']['tmp_name'];
        }

        // Save the image file.
        if ($isMagickInstalled) {
            // default density is 72 dpi, we change to 96.  And -append was removed
            // to create a separate image file for each page.
            $cmd = "magick -density 96 " . escapeshellarg($tmp_name) . " " . escapeshellarg($imagePath);
            $tmp0 = exec($cmd, $tmp1, $tmp2);
            // Handle errors
            if ($tmp2 !== 0) {
                // Log the error for debugging
                error_log("ImageMagick command failed: $cmd" . " Command output: " . implode("\n", $tmp1));
                // Attempt to save the temporary file directly to $imagePath
                if (!copy($tmp_name, $imagePath)) {
                    echo xlt("Failed to save the image directly from temporary file.");
                } else {
                    error_log("Image saved directly from temporary file to $imagePath.");
                }
            }
        } else {
            // Attempt to save the temporary file directly to $imagePath so that the image is saved in the system.
            // In case ImageMagick is not installed.
            if (!copy($tmp_name, $imagePath)) {
                echo xlt("Failed to save the image directly from temporary file.");
            } else {
                error_log("Image saved directly from temporary file to $imagePath.");
            }
        }
    }
}

$imagePath = $imagedir . "/" . check_file_dir_name($encounter . "_" . $formid . ".jpg");
$imageUrl = "$web_root/sites/" . $_SESSION['site_id'] .
    "/documents/" . check_file_dir_name($pid) . "/encounters/" . check_file_dir_name($encounter . "_" . $formid . ".jpg");

if ($formid) {
    $row = sqlQuery("SELECT * FROM form_scanned_notes WHERE id = ? AND activity = '1'", array($formid));
    $formrow = sqlQuery("SELECT id FROM forms WHERE form_id = ? AND formdir = 'scanned_notes'", array($formid));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo xlt('Scanned Notes'); ?></title>
    <?php Header::setupHeader(); ?>
    <style>
      .dehead {
        font-family: sans-serif;
        font-weight: bold;
      }

      hr {
        box-sizing: content-box;
        height: 0;
        overflow: visible;
        margin-top: 1rem;
        border: 0;
        border-top: 1px solid #ffffff40;
      }

      video, canvas {
        display: block;
        margin: 10px auto;
      }
    </style>
    <script>
        function newEvt() {
            dlgopen('../../main/calendar/add_edit_event.php?patientid=' + <?php echo js_url($pid); ?>, '_blank', 775, 500);
            return false;
        }

        function deleteme(event) {
            event.stopPropagation();
            dlgopen('../../patient_file/deleter.php?formid=' + <?php echo js_url($formrow['id']); ?> +'&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>, '_blank', 500, 450, '', '', {
                resolvePromiseOn: 'close'
            }).then(function (data) {
                // Restore the session and proceed with form submission
                top.restoreSession();
            }).catch(function (error) {
                // Handle errors if dialog promise is rejected
                console.error("Dialog operation failed:", error);
                alert(xl("Operation was canceled or failed."));
            });
            return false;
        }

        function imdeleted() {
            top.restoreSession();
            $("#delete").val("delete"); // Set the delete flag
            $("#scanned-form").submit(); // Submit the form
        }

        function goBack() {
            top.restoreSession();
            $("#back").val("back");
            $("#scanned-form").submit(); // Submit the form
        }
    </script>
    <script>
        // Wait until the form is fully loaded before initializing webcam
        window.addEventListener('DOMContentLoaded', () => {
            // Elements
            const fileUpload = document.getElementById('fileUpload');
            const preview = document.getElementById('preview');
            const webcamElement = document.getElementById('webcam');
            const canvasElement = document.getElementById('canvas');
            const captureBtn = document.getElementById('capture-btn');
            const webPanel = document.getElementById('webcam-container');
            const webHide = document.getElementById('webcam-hide');
            const capturedImageInput = document.getElementById('capturedImage');
            const toggleWebcamButton = document.getElementById('toggleWebcamButton');
            const errorMessage = document.getElementById('webcamErrorMessage');
            let webcamStream = null;
            let webcamEnabled = false; // Default state

            async function startWebcam() {
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({video: true});
                    webcamElement.srcObject = stream;
                    webcamStream = stream;
                    webPanel.style.display = 'block';
                    webHide.style.display = 'block';
                    toggleWebcamButton.style.color = 'black';
                    toggleWebcamButton.textContent = 'Disable Webcam';
                    toggleWebcamButton.classList.remove('btn-success');
                    toggleWebcamButton.classList.add('btn-warning');
                    captureBtn.style.display = 'block';
                    webcamEnabled = true;
                    // Handle stream events if needed
                    stream.getVideoTracks()[0].onended = () => {
                        alert(xl('Webcam stream ended.'));
                    };
                    errorMessage.textContent = '';
                    webPanel.scrollIntoView({behavior: 'smooth', block: 'center'});
                } catch (err) {
                    // Hide the webcam-related UI and log the error
                    webHide.style.display = 'none';
                    webPanel.style.display = 'none';
                    captureBtn.style.display = 'none';
                    handleWebcamError(err);
                }
            }

            function stopWebcam() {
                if (webcamStream) {
                    const tracks = webcamStream.getTracks();
                    tracks.forEach(track => track.stop());
                    webcamElement.srcObject = null;
                    webcamStream = null;
                }
                webPanel.style.display = 'none';
                webHide.style.display = 'none';
                captureBtn.style.display = 'none';
                toggleWebcamButton.style.color = 'white';
                toggleWebcamButton.textContent = 'Enable Webcam';
                toggleWebcamButton.classList.remove('btn-warning');
                toggleWebcamButton.classList.add('btn-success');
                webcamEnabled = false;
            }

            function handleWebcamError(err) {
                // Customize the error message and actions based on the error type
                if (err.name === 'NotAllowedError') {
                    errorMessage.textContent = jsText(xl('User denied access to the webcam. Please check your browser settings and allow camera access.'));
                } else if (err.name === 'NotFoundError') {
                    errorMessage.textContent = jsText(xl('No webcam found on this device. Please ensure a webcam is connected.'));
                } else if (err.name === 'NotReadableError') {
                    errorMessage.textContent = jsText(xl('Webcam is already in use by another application or browser. Please close other apps and try again.'));
                } else {
                    errorMessage.textContent = jsText(xl(`Unexpected error: ${err.name + err.message}. Try refreshing page or Webcam is already in use by another application or browser.`));
                }
                errorMessage.scrollIntoView({behavior: 'smooth', block: 'center'});
            }

            if (!webcamEnabled) {
                captureBtn.style.display = 'none';
            }

            toggleWebcamButton.addEventListener('click', () => {
                if (webcamEnabled) {
                    stopWebcam();
                } else {
                    startWebcam();
                }
            });
            // Capture image from webcam
            captureBtn.addEventListener('click', () => {
                const context = canvasElement.getContext('2d');
                canvasElement.width = webcamElement.videoWidth;
                canvasElement.height = webcamElement.videoHeight;
                context.drawImage(webcamElement, 0, 0, canvasElement.width, canvasElement.height);
                const picture = canvasElement.toDataURL('image/jpg');
                preview.src = picture;
                capturedImageInput.value = picture;
                webPanel.style.display = 'block';
                preview.scrollIntoView({behavior: 'smooth', block: 'center'});
            });
            // webcam click handler
            webcam.addEventListener('click', () => {
                const context = canvasElement.getContext('2d');
                canvasElement.width = webcamElement.videoWidth;
                canvasElement.height = webcamElement.videoHeight;
                context.drawImage(webcamElement, 0, 0, canvasElement.width, canvasElement.height);
                const picture = canvasElement.toDataURL('image/jpg');
                preview.src = picture;
                capturedImageInput.value = picture;
                webPanel.style.display = 'block';
                preview.scrollIntoView({behavior: 'smooth', block: 'center'});
            });
            // File input change handler
            fileUpload.addEventListener('change', previewFile);
        });

        // Preview uploaded file
        function previewFile() {
            const file = fileUpload.files[0];
            const reader = new FileReader();
            reader.onloadend = () => {
                preview.src = reader.result;
            };
            if (file) {
                reader.readAsDataURL(file);
            } else {
                preview.src = "";
            }
        }
    </script>
</head>
<body class="body_top">
    <div class="container">
        <form method="post" enctype="multipart/form-data" id="scanned-form" class="mt-4" action="<?php echo $rootdir ?>/forms/scanned_notes/new.php?id=<?php echo attr_url($formid); ?>">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
            <div class="card">
                <div class="card-header text-center bg-light">
                    <h4 class="m-0"><?php echo xlt('Scanned Encounter Notes'); ?></h4>
                </div>
                <div class="card-body">
                    <div class="form-group row">
                        <label for="form_notes" class="col-sm-2 col-form-label dehead"><?php echo xlt('Comments'); ?></label>
                        <div class="col-sm-10">
                            <textarea id="form_notes" name="form_notes" rows="4" class="form-control"><?php echo text($row['notes']); ?></textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="fileUpload" class="col-sm-2 col-form-label dehead"><?php echo xlt('Document'); ?>
                            <button type="button" class="btn btn-warning" style="color: #000000;" onclick="return goBack()"><?php echo xlt('Exit'); ?></button>
                        </label>
                        <div class="col-sm-10">
                            <input type="hidden" name="MAX_FILE_SIZE" value="12000000" />
                            <div class="text-center">
                                <img id="preview" class="img-fluid" src="<?php echo attr($imageUrl . "?" . time()); ?>" alt="<?php echo xla("Select an image") ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-3 text-center">
                <?php if (is_file($imagePath)) { ?>
                    <label class="text-success" for="fileUpload" class="m-0"><?php echo xlt('Replace Current Scanned Image:'); ?></label>
                <?php } else { ?>
                    <label class="text-success" for="fileUpload" class="m-0"><?php echo xlt('Upload a jpg, jpeg, png or gif Image:'); ?></label>
                <?php } ?>
                <input class="btn btn-sm btn-success" type="file" id="fileUpload" name="fileUpload" accept="image/*" onchange="previewFile()">
                <hr />
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary" name='bn_save' value="save"><?php echo xlt('Save'); ?></button>
                    <button type="button" class="btn btn-primary" onclick="newEvt()"><?php echo xlt('Add Appointment'); ?></button>
                    <button type="button" id="toggleWebcamButton" class="btn btn-success"><?php echo xlt('Enable Webcam'); ?></button>
                    <?php if ($formrow['id'] && AclMain::aclCheckCore('admin', 'super')) { ?>
                        <input type="hidden" id="delete" name="delete" value="">
                        <button type="button" class="btn btn-danger" onclick="return deleteme(event);"><?php echo xlt('Delete'); ?></button>
                    <?php } ?>
                    <input type="hidden" id="back" name="back" value="">
                    <button type="button" class="btn btn-secondary" onclick="return goBack()"><?php echo xlt('Back'); ?></button>
                </div>
                <p class="text-danger m-1" id="webcamErrorMessage" style="font-size: 1.1rem"></p>
            </div>
            <!-- Webcam Capture Option -->
            <div id="webcam-hide" class="card mt-2 mb-5" style="display: none;">
                <div class="card-header text-center bg-light">
                    <h4 class="m-0"><?php echo xlt('Webcam Preview'); ?></h4>
                </div>
                <div class="card-body">
                    <div class="form-group row text-center">
                        <label for="webcam" class="col-sm-2 col-form-label"><?php echo xlt('Preview'); ?></label>
                        <div id="webcam-container" class="col-sm-10 text-center" style="display: block;">
                            <video id="webcam" autoplay playsinline style="width: 100%; max-width: 640px;"></video>
                            <canvas id="canvas" style="display: none;"></canvas>
                        </div>
                    </div>
                    <button type="button" id="capture-btn" class="btn btn-success mx-1 float-right" style="display: block;"><?php echo xlt('Capture Frame'); ?></button>
                </div>
            </div>
            <input type="hidden" id="capturedImage" name="capturedImage">
        </form>
    </div>
</body>
</html>
