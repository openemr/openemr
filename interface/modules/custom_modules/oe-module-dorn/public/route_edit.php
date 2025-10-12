<?php

/**
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2022-2025 Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2024-2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once __DIR__ . "/../../../../globals.php";

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Modules\Dorn\ConnectorApi;
use OpenEMR\Modules\Dorn\models\CreateRouteFromPrimaryViewModel;
use OpenEMR\Modules\Dorn\DisplayHelper;
use OpenEMR\Modules\Dorn\LabRouteSetup;
use OpenEMR\Modules\Dorn\AddressBookAddEdit;

if (!AclMain::aclCheckCore('admin', 'users')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Edit/Add Procedure Provider")]);
    exit;
}

$labGuid = "";
$message = "";

if (!empty($_REQUEST)) {
    if (!CsrfUtils::verifyCsrfToken($_REQUEST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}
if (!empty($_POST)) {
    //lets lookup the lab information we want to add a route for!
    $routeData = CreateRouteFromPrimaryViewModel::loadByPost($_POST);
    $apiResponse =  ConnectorApi::createRoute($routeData); //create the route on dorn, we have all we need to do so
    if ($apiResponse->isSuccess) {
        $ppid = 0;
        $uid = 0;
        $labData = ConnectorApi::getLab($routeData->labGuid);
        $note = "labGuid:" . $labData->labGuid;

        $setupRouteInfo = LabRouteSetup::getRouteSetup($apiResponse->labGuid, $apiResponse->routeGuid);
        if ($setupRouteInfo != null) {
            $ppid = $setupRouteInfo["ppid"];
            $uid = $setupRouteInfo["uid"];
        }

        //we've added this lab to the address book here.
        $uid = AddressBookAddEdit::createOrUpdateRecordInAddressBook($uid, $labData->name, $labData->address1, $labData->address2, $labData->city, $labData->state, $labData->zipCode, $labData->Website, $labData->phoneNumber, $labData->faxNumber, $note);
        $ppid = LabRouteSetup::createUpdateProcedureProviders($ppid, $labData->name, $routeData->npi, $labData->labGuid, $uid, $routeData->labAccountNumber);

        //lets add/update to the new dorn route table
        $isLabSetup = LabRouteSetup::createDornRoute($apiResponse->labName, $apiResponse->routeGuid, $apiResponse->labGuid, $ppid, $uid, $labData->textLineBreakCharacter, $routeData->labAccountNumber);
        $message = $isLabSetup ? "Lab has been setup" : "Failure creating route!";
    } else {
        if ($apiResponse->responseMessage) {
            $message = $apiResponse->responseMessage;
        } else {
            $message = "Error creating route, no information came back though";
        }
    }
} else {
    if (!empty($_GET)) {
        $labGuid = $_REQUEST['labGuid'];
    }
}

$isEula = ($_GET['isEula'] ?? false) == 'true';

$primaryInfos = ConnectorApi::getPrimaryInfos('');
?>
<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(['opener']);?>
    <title><?php echo xlt("Edit or Add Procedure Provider") ?></title>
    <style>
      .required-field {
        color: red;
      }
      .form-section {
        border: 1px solid #ddd;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 5px;
      }
      .form-section h5 {
        margin-top: 0;
        color: #333;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
      }
    </style>
</head>
<body class="container-fluid">
    <form method='post' name='theform' action="route_edit.php?labGuid=<?php echo attr_url($labGuid); ?>&csrf_token_form=<?php echo attr_url(CsrfUtils::collectCsrfToken()); ?>">
        <div class="row">
            <div class="col-sm-12">
                <h2><?php echo xlt("DORN Lab Route Configuration") ?></h2>
                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                <input type="hidden" name="form_labGuid" value="<?php echo attr($labGuid); ?>" />
            </div>
        </div>

        <!-- Customer Account Information Section -->
        <div class="form-section">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="form_customerAcctNumber"><?php echo xlt("Customer Account Number") ?> <span class="required-field">*</span>:</label>
                        <input type="text" class="form-control" id="form_customerAccountNumber" name="form_customerAcctNumber"
                            value="<?php echo isset($_POST['form_customerAcctNumber']) ? attr($_POST['form_customerAcctNumber']) : '' ?>"
                            required/>
                        <small class="form-text text-muted"><?php echo xlt("Your unique customer account identifier") ?></small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="form_clientSiteId"><?php echo xlt("Client Site ID") ?> <span class="required-field">*</span>:</label>
                        <input type="text" class="form-control" id="form_clientSiteId" name="form_clientSiteId"
                            value="<?php echo isset($_POST['form_clientSiteId']) ? attr($_POST['form_clientSiteId']) : '' ?>"
                            required/>
                        <small class="form-text text-muted"><?php echo xlt("Unique identifier for your client site") ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Provider Information Section -->
        <div class="form-section">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="form_primaries"><?php echo xlt("Select NPI") ?> <span class="required-field">*</span>:</label>
                        <select id="form_primaries" name="form_primaries" class="form-control" required>
                            <option value=""><?php echo xlt("-- Select Provider --") ?></option>
                            <?php
                            foreach ($primaryInfos as $pInfo) {
                                ?>
                                <option <?php echo DisplayHelper::SelectOption($_POST['form_primaries'] ?? '', $pInfo->npi ?? '') ?>  value='<?php echo attr($pInfo->npi) ?>' ><?php echo text($pInfo->primaryName); ?> (<?php echo text($pInfo->npi); ?>)</option>
                                <?php
                            }
                            ?>
                        </select>
                        <small class="form-text text-muted"><?php echo xlt("National Provider Identifier for the ordering provider") ?></small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="form_labAcctNumber"><?php echo xlt("Lab Account Number") ?> <span class="required-field">*</span>:</label>
                        <input type="text" class="form-control" id="form_labAcctNumber" name="form_labAcctNumber"
                            value="<?php echo isset($_POST['form_labAcctNumber']) ? attr($_POST['form_labAcctNumber']) : '' ?>"
                            required/>
                        <small class="form-text text-muted"><?php echo xlt("Your account number with the laboratory") ?></small>
                    </div>
                </div>
            </div>
        </div>
        <?php if ($isEula) { ?>
        <div class="form-section">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="form_eulaVersion"><?php echo xlt("EULA Version") ?> <span class="required-field">*</span>:</label>
                        <input type="text" class="form-control" id="form_eulaVersion" name="form_eulaVersion"
                            value="<?php echo isset($_POST['form_eulaVersion']) ? attr($_POST['form_eulaVersion']) : '2024-08-28' ?>"
                            required/>
                        <small class="form-text text-muted"><?php echo xlt("Version of the EULA being accepted") ?></small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="form_eulaAccepterFullName"><?php echo xlt("EULA Accepter Full Name") ?> <span class="required-field">*</span>:</label>
                        <input type="text" class="form-control" id="form_eulaAccepterFullName" name="form_eulaAccepterFullName"
                            value="<?php echo isset($_POST['form_eulaAccepterFullName']) ? attr($_POST['form_eulaAccepterFullName']) : '' ?>"
                            required/>
                        <small class="form-text text-muted"><?php echo xlt("Full name of the person accepting the EULA") ?></small>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="form_eulaAcceptanceDateTimeUtc"><?php echo xlt("EULA Acceptance Date & Time (UTC)") ?> <span class="required-field">*</span>:</label>
                        <input type="text" class="form-control" id="form_eulaAcceptanceDateTimeUtc" name="form_eulaAcceptanceDateTimeUtc"
                            value="<?php echo isset($_POST['form_eulaAcceptanceDateTimeUtc']) ? attr($_POST['form_eulaAcceptanceDateTimeUtc']) : date('Y-m-d\TH:i:s.v\Z') ?>"
                            required/>
                        <small class="form-text text-muted"><?php echo xlt("Date and time when the EULA was accepted (UTC timezone)") ?></small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" checked id="form_eulaAcceptance" name="form_eulaAcceptance" required>
                            <label class="form-check-label" for="form_eulaAcceptance">
                                <strong><?php echo xlt("I accept the End User License Agreement") ?> <span class="required-field">*</span></strong>
                            </label>
                        </div>
                        <small class="form-text text-muted"><?php echo xlt("You must accept the EULA to proceed with route creation") ?></small>
                    </div>
                </div>
            </div>
        </div>
        <?php }  ?>
        <?php if (!empty($labGuid)) { ?>
            <div class="form-section">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label><?php echo xlt("Lab GUID") ?>:</label>
                            <input type="text" class="form-control" value="<?php echo attr($labGuid); ?>" readonly/>
                            <small class="form-text text-muted"><?php echo xlt("Unique identifier for the selected laboratory") ?></small>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
        <!-- Submit Section -->
        <div class="row">
            <div class="col-sm-12">
                <button type="submit" name="SubmitButton" class="btn btn-primary btn-save">
                    <?php echo xlt("Create Lab Route") ?>
                </button>
                <button type="button" class="btn btn-secondary btn-cancel ml-2" onclick="window.close();">
                    <?php echo xlt("Cancel") ?>
                </button>
                <?php if (!empty($message)) { ?>
                    <div class="alert alert-info mt-3">
                        <?php echo text($message); ?>
                    </div>
                <?php } ?>
            </div>
        </div>

        <script>
            // Form validation
            document.querySelector('form').addEventListener('submit', function(e) {
                const requiredFields = document.querySelectorAll('input[required], select[required]');
                let hasErrors = false;

                requiredFields.forEach(function(field) {
                    if (!field.value.trim()) {
                        field.classList.add('is-invalid');
                        hasErrors = true;
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });

                if (hasErrors) {
                    e.preventDefault();
                    alert(<?php echo xlj("Please fill in all required fields") ?>);
                }
            });
        </script>

</body>
</html>
