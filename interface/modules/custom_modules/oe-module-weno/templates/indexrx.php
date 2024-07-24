<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author    Kofi Appiah <kkappiah@medsov.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2023 omega systems group international <info@omegasystemsgroup.com>
 * @copyright Copyright (c) 2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../../../globals.php");
require_once("$srcdir/patient.inc");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Modules\WenoModule\Services\PharmacyService;
use OpenEMR\Modules\WenoModule\Services\TransmitProperties;
use OpenEMR\Modules\WenoModule\Services\WenoValidate;

//ensure user has proper access permissions.
if (!AclMain::aclCheckCore('patients', 'rx')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Weno eRx")]);
    exit;
}

// Let's see if letting user decide to reset fly's!
// We really don't need because we can do transparently but Weno requested so...
$wenoValidate = new WenoValidate();
if (isset($_GET['form_reset_key'])) {
    unset($_GET['form_reset_key']);
    // if we are here then we need to reset the key.
    $newKey = $wenoValidate->requestEncryptionKeyReset();
    $wenoValidate->setNewEncryptionKey($newKey);
    // Redirect to the same page to refresh the page with the new key.
    $isValidKey = true;
} else {
    // Validate if the user has a valid encryption key.
    // If not, show a reset button below.
    // This is a manual process for now.
    $isValidKey = $wenoValidate->verifyEncryptionKey();
}
/*
// We can automate! If the key is not valid, request a new key and set it
// for the user. This will be transparent to the user.
// This is easier, but for now we want to alert user by showing button.
// Clicking will do the same as the below function.
    $isKey = $wenoValidate->validateAdminCredentials(true);
*/

$cryptoGen = new CryptoGen();

// set up the dependencies for the page.
$pharmacyService = new PharmacyService();
$wenoProperties = new TransmitProperties();
$primary_pharmacy = $pharmacyService->getWenoPrimaryPharm($_SESSION['pid']) ?? [];
$alt_pharmacy = $pharmacyService->getWenoAlternatePharm($_SESSION['pid']) ?? [];
$provider_info = $wenoProperties->getProviderEmail();
$urlParam = $wenoProperties->cipherPayload();
$vitals = $wenoProperties->getVitals();
$provider_name = $wenoProperties->getProviderName();
$patient_name = $wenoProperties->getPatientName();
$facility_name = $wenoProperties->getFacilityInfo();
//set the url for the iframe
$newRxUrl = "https://online.wenoexchange.com/en/NewRx/ComposeRx?useremail=";
if ($urlParam == 'error') {   //check to make sure there were no errors
    echo TransmitProperties::styleErrors(xlt("Cipher failure check encryption key"));
    exit;
}

$urlOut = $newRxUrl . urlencode($provider_info['email']) . "&data=" . urlencode($urlParam);

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo xlt('Weno eRx') ?></title>
    <?php Header::setupHeader(); ?>
    <style>
      .row {
        display: flex;
        flex-direction: row;
      }

      .col {
        display: flex;
        flex-direction: column;
      }

      .center {
        justify-content: center;
      }

      /* Styling for sticky container */
      .sticky-container {
        position: sticky;
        top: 0;
        z-index: 1000;
      }

      b {
        font-weight: 500;
        color: var(--primary);
      }
    </style>
    <script>
        $(function () {
            $('#form_reset_key').addClass('d-none');
            /* Toggle reset button. */
            <?php if ((int)$isValidKey > 997) { ?>
            $(function () {
                const warnMsg = "<?php echo xlt('Internet connection problem. Returning to Patient chart when alert closes!'); ?>";
                syncAlertMsg(warnMsg, 8000, 'danger', 'lg').then(() => {
                    window.location.href = "<?php echo $GLOBALS['web_root'] ?>/interface/patient_file/summary/demographics.php?set_pid=<?php echo urlencode(attr($_SESSION['pid'] ?? $pid ?? '')) ?>";
                });
            });
            <?php } elseif (!$isValidKey) { ?>
            $(function () {
                $('#form_reset_key').removeClass('d-none');
                const warnMsg = "<?php
                    echo xlt('Decryption failed! The Encryption key is incorrect') . "<br>" .
                        xlt('Click newly shown top Reset button to reset your account encryption key.') . "<br>" .
                        xlt('Afterwards you may continue and no other action is required by you.'); ?>";
                syncAlertMsg(warnMsg, 8000, 'danger', 'lg');
            });
            <?php } else { ?>
            $(function () {
                $('#form_reset_key').addClass('d-none');
            });
            <?php } ?>
        });
        $(function () {
            // Function to generate debug info and create a downloadable file
            function generateDebugInfo() {
                let debugInfo = 'Debug Information:';
                debugInfo += '\n- User Agent:' + navigator.userAgent;
                debugInfo += '\n- Platform:' + navigator.platform;
                debugInfo += '\n- Language:' + navigator.language;
                debugInfo += '\n\n- URL:\n <?php echo js_escape($urlOut); ?>';
                debugInfo += '\n\n- Data Raw:\n <?php echo js_escape($urlParam); ?>';
                debugInfo += '\n\n- Encoded Data:\n <?php echo js_escape(urlencode($urlParam)); ?>';

                const blob = new Blob([debugInfo], {type: 'text/plain'});
                const url = URL.createObjectURL(blob);
                $('#downloadLink').attr('href', url);
            }

            // Event handler for double-click on the trigger button
            $('#trigger-debug').dblclick(function () {
                generateDebugInfo();
                $('#debugModal').modal('show');
            });
            $('#triggerButton').click(function () {
                generateDebugInfo();
                $('#debugModal').modal('show');
            });
            $('#downloadLink').click(function () {
                $('#debugModal').modal('hide');
            });
        });
    </script>
</head>
<body>
    <div id="trigger-debug" class="container-xl">
        <div class="container-xl sticky-container bg-light text-dark">
            <form>
                <header class="bg-light text-dark text-center">
                    <h3>
                        <a href="<?php echo $GLOBALS['web_root'] ?>/interface/patient_file/summary/demographics.php?set_pid=<?php echo urlencode(attr($_SESSION['pid'] ?? $pid)) ?>" class="text-primary" title="<?php echo xla("Return to Patient Demographics"); ?>"><?php echo xlt("e-Prescribe"); ?>
                            <cite class="small font-weight-bold text-primary"><span class="h6"><?php echo xla("Return to Patient"); ?></span></cite>
                        </a>
                        <button type="submit" id="form_reset_key" name="form_reset_key" class="btn btn-danger btn-sm btn-refresh p-1 m-0 mt-1 mr-2 float-right d-none" value="Save" title="<?php echo xla("The Encryption key did not pass validation. Clicking this button will reset your encryption key so you may continue."); ?>"><?php echo xlt("Session is invalid!. Click to Reset?"); ?></button>
                    </h3>
                </header>
            </form>
            <div class="row mx-1 center">
                <div class="col">
                    <div class="row">
                        <div><b><?php echo xlt("Prescriber"); ?></b>: <?php echo text($provider_name); ?> </div>
                    </div>
                    <div class="row">
                        <div><b><?php echo xlt("Patient Name"); ?></b>: <?php echo text($patient_name) ?></div>
                    </div>
                    <div class="row">
                        <div>
                            <b><?php echo xlt("Current Facility/Location"); ?></b>: <?php echo text($facility_name["name"]) ?>
                        </div>
                    </div>
                </div>
                <!-- Only show vitals when patient is under 19 yo. Not required to be sent otherwise so why show! -->
                <?php if ($vitals['height'] > 0 && $vitals['weight'] > 0) { ?>
                    <div class="col" style="margin-top: -4px !important">
                        <table>
                            <tr>
                                <th><?php echo xlt("Vitals"); ?></th>
                                <th><?php echo xlt("Date Observed"); ?></th>
                            </tr>
                            <tr>
                                <td><?php echo xlt("Height"); ?>:<?php echo text(number_format($vitals['height'], 2)); ?> </td>
                                <td><?php echo text(oeFormatShortDate(date("Y-m-d", strtotime($vitals['date'])))); ?></td>
                            </tr>
                            <tr>
                                <td><?php echo xlt("Weight: "); ?><?php echo text(number_format($vitals['weight'], 2)); ?> </td>
                                <td><?php echo text(oeFormatShortDate(date("Y-m-d", strtotime($vitals['date'])))); ?></td>
                            </tr>
                        </table>
                    </div>
                <?php } ?>
                <div class="col">
                    <div><b><?php echo xlt("Primary Pharmacy"); ?> : </b><?php echo text($primary_pharmacy['business_name'] . " / " . $primary_pharmacy['address_line_1'] . " / " . $primary_pharmacy['city']); ?></div>
                    <div><b><?php echo xlt("Weno Alt"); ?> : </b><?php echo text($alt_pharmacy['business_name'] ?? '' . " / " . $alt_pharmacy['address_line_1'] ?? '' . " / " . $alt_pharmacy['city'] ?? ''); ?></div>
                </div>
            </div>
        </div>
        <div class="container-xl mt-3">
            <iframe id="wenoIfram" title="Weno IFRAME" width="100%" height="900" src="<?php echo attr($urlOut); ?>"></iframe>
        </div>
        <footer>
            <a href="<?php echo $GLOBALS['web_root'] ?>/interface/patient_file/summary/demographics.php?set_pid=<?php echo urlencode(attr($_SESSION['pid'] ?? $pid)) ?>" class="btn btn-primary float-right mt-2 mb-4 mr-3"><?php echo xlt("Return to Demographics"); ?></a>
            <button id="triggerButton" class="btn btn-primary btn-sm m-2 ml-3" title="<?php echo xla("Download debug information to send to Weno support."); ?>"><i class="fa-solid fa-bug"></i></button>
        </footer>
        <!-- Modal Structure -->
        <div class="modal fade" id="debugModal" tabindex="-1" role="dialog" aria-labelledby="debugModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="debugModalLabel"><?php echo xlt("Weno Debug Information"); ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p><?php echo xlt("Debug information has been generated. Click below to download."); ?></p>
                        <a id="downloadLink" class="btn btn-success" download="debug_info_<?php echo md5($provider_info['email']); ?>.txt"><?php echo xlt("Download Debug File"); ?></a>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo xlt("Close"); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
