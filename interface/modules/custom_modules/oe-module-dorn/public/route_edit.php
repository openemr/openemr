<?php

/**
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

 require_once "../../../../globals.php";

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

if (!empty($_GET)) {
    if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
    //lets lookup the lab information we want to add a route for!
    $routeData = CreateRouteFromPrimaryViewModel::loadByPost($_POST);
    $apiResponse =  ConnectorApi::createRoute($routeData);//create the route on dorn, we have all we need to do so
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
        $ppid = LabRouteSetup::createUpdateProcedureProviders($ppid, $labData->name, $routeData->npi, $labData->labGuid, $uid);

        //lets add/update to the new dorn route table
        $isLabSetup = LabRouteSetup::createDornRoute($apiResponse->labName, $apiResponse->routeGuid, $apiResponse->labGuid, $ppid, $uid, $labData->textLineBreakCharacter, $routeData->labAccountNumber);
        if ($isLabSetup) {
            $message = "Lab has been setup";
        } else {
            $message = "Failure creating route!";
        }
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

$primaryInfos = ConnectorApi::getPrimaryInfos('');
?>
<html>
<head>
        <?php Header::setupHeader(['opener']);?>
    </head>
    <body>
    <form method='post' name='theform' action="route_edit.php?labGuid=<?php echo attr_url($labGuid); ?>&csrf_token_form=<?php echo attr_url(CsrfUtils::collectCsrfToken()); ?>">
        <div class="row">
            <div class="col-sm-6">
                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                <input type="hidden" name="form_labGuid" value="<?php echo attr($labGuid); ?>" />
            </div>
        </div>        

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="form_primaries"><?php echo xlt("Select NPI") ?>:</label>
                    <select id="form_primaries" name="form_primaries">
                        <?php
                        foreach ($primaryInfos as $pInfo) {
                            ?>
                            <option <?php echo DisplayHelper::SelectOption($_POST['form_primaries'] ?? '', $pInfo->npi ?? '') ?>  value='<?php echo attr($pInfo->npi) ?>' ><?php echo text($pInfo->primaryName); ?> (<?php echo text($pInfo->npi); ?>)</option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
            </div>

       </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="form_labAcctNumber"><?php echo xlt("Lab Account Number") ?>:</label>
                    <input type="text" class="form-control" id="form_labAcctNumber" name="form_labAcctNumber" value="<?php echo isset($_POST['form_labAcctNumber']) ? attr($_POST['form_labAcctNumber']) : '' ?>"/>
                </div>              
            </div>
       </div>
       <div class="row">
            <div class="col-sm-6">
                <button type="submit" name="SubmitButton" class="btn btn-primary"><?php echo xlt("Save") ?></button>
                <?php
                    echo text($message);
                ?>
            </div>
        </div>
     
    </body>
</html>
