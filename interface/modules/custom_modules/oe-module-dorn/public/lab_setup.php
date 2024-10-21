<?php

/**
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
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

//this is needed along with setupHeader() to get the pop up to appear

$tab = "lab setup";
if (!AclMain::aclCheckCore('admin', 'users')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Edit/Add Procedure Provider")]);
    exit;
}

if (!empty($_POST)) {
    if (isset($_POST['SubmitButton'])) {
        //check if form was submitted
        $datas = ConnectorApi::searchLabs($_POST['form_labName'], $_POST['form_phone'], $_POST['form_fax'], $_POST['form_city'], $_POST['form_state'], $_POST['form_zip'], $_POST['form_active'], $_POST['form_connected']);
        if ($datas == null) {
            $datas = [];
        }
    }
}
?>
<html>
<head>
    <?php Header::setupHeader(); ?>
    <title> <?php echo xlt("DORN Configuration"); ?></title>
</head>
<script>
    function createRouteclick_edit(labGuid) {
        // dialog open calls restoreSession()
        let addTitle = '<i class="fa fa-plus" style="width:20px;" aria-hidden="true"></i> ' + <?php echo xlj("Create Route"); ?>;
        let scriptTitle = 'route_edit.php?labGuid=' + encodeURIComponent(labGuid) + '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>;
        dlgopen(scriptTitle, '_blank', 800, 750, false, addTitle);
    }

    function installCompendiumClick(labGuid) {
        let addTitle = '<i class="fa fa-plus" style="width:20px;" aria-hidden="true"></i> ' + <?php echo xlj("Edit Mode"); ?>;
        let scriptTitle = 'compendium_install.php?labGuid=' + encodeURIComponent(labGuid) + '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>;
        dlgopen(scriptTitle, '_blank', 800, 750, false, addTitle);
    }
</script>
<body>
    <div class="row">
        <div class="col">
            <?php
            require '../templates/navbar.php';
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <h1><?php echo xlt("DORN - Lab Setup"); ?></h1>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <form method="post" action="lab_setup.php">
                <div class="card">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="form_labName"><?php echo xlt("Lab Name") ?>:</label>
                                <input type="text" class="form-control" id="form_labName" name="form_labName" value="<?php echo isset($_POST['form_labName']) ? attr($_POST['form_labName']) : '' ?>" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="form_labType"><?php echo xlt("Lab Type") ?>:</label>
                                <input type="text" class="form-control" id="form_labType" name="form_labType" value="<?php echo isset($_POST['form_labType']) ? attr($_POST['form_labType']) : '' ?>" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="form_phone"><?php echo xlt("Phone") ?>:</label>
                                <input type="text" class="form-control" id="form_phone" name="form_phone" value="<?php echo isset($_POST['form_phone']) ? attr($_POST['form_phone']) : '' ?>" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="form_fax"><?php echo xlt("Fax") ?>:</label>
                                <input type="text" class="form-control" id="form_fax" name="form_fax" value="<?php echo isset($_POST['form_fax']) ? attr($_POST['form_fax']) : '' ?>" />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="form_city"><?php echo xlt("City") ?>:</label>
                                <input type="text" class="form-control" id="form_city" name="form_city" value="<?php echo isset($_POST['form_city']) ? attr($_POST['form_city']) : '' ?>" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="form_state"><?php echo xlt("State") ?>:</label>
                                <input type="text" class="form-control" id="form_state" name="form_state" value="<?php echo isset($_POST['form_state']) ? attr($_POST['form_state']) : '' ?>" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="form_zip"><?php echo xlt("Zip") ?>:</label>
                                <input type="text" class="form-control" id="form_zip" name="form_zip" value="<?php echo isset($_POST['form_zip']) ? attr($_POST['form_zip']) : '' ?>" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="form_connected"><?php echo xlt("Is Connected") ?>:</label>
                                <select id="form_connected" name="form_connected">
                                    <option value=""><?php echo xlt("All") ?></option>
                                    <option value="yes" <?php echo isset($_POST['form_connected']) ? attr($_POST['form_connected']) == 'yes' ? ' selected ' : '' : '' ?> ><?php echo xlt("Yes"); ?></option>
                                    <option value="no" <?php echo isset($_POST['form_connected']) ? attr($_POST['form_connected']) == 'no' ? ' selected ' : '' : '' ?> ><?php echo xlt("No"); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="form_active"><?php echo xlt("Is Active") ?>:</label>
                                <select id="form_active" name="form_connected">
                                    <option value=""><?php echo xlt("All") ?></option>
                                    <option value="yes" <?php echo isset($_POST['form_active']) ? attr($_POST['form_active']) == 'yes' ? ' selected ' : '' : '' ?> ><?php echo xlt("Yes"); ?></option>
                                    <option value="no" <?php echo isset($_POST['form_active']) ? attr($_POST['form_active']) == 'no' ? ' selected ' : '' : '' ?> ><?php echo xlt("No"); ?></option>
                                </select>
                            </div>

                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <button type="submit" onsubmit="return top.restoreSession()" name="SubmitButton" class="btn btn-primary"><?php echo xlt("Submit") ?></button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th scope="col"><?php echo xlt("Lab Name") ?></th>
                                    <th scope="col"><?php echo xlt("Lab Type") ?></th>
                                    <th scope="col"><?php echo xlt("Phone Number") ?></th>
                                    <th scope="col"><?php echo xlt("Fax Number") ?></th>
                                    <th scope="col"><?php echo xlt("Address") ?></th>
                                    <th scope="col"><?php echo xlt("City") ?></th>
                                    <th scope="col"><?php echo xlt("State") ?></th>
                                    <th scope="col"><?php echo xlt("Zip") ?></th>
                                    <th scope="col"><?php echo xlt("Compendium Update Date") ?></th>
                                    <th scope="col"><?php echo xlt("Compendium Download Date") ?></th>
                                    <th scope="col"><?php echo xlt("Active Routes") ?></th>
                                    <th scope="col"> <?php echo xlt("Actions") ?> </th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                foreach ($datas as $data) {
                                    ?>
                                    <tr>
                                        <td scope="row"><?php echo text($data->name); ?></td>
                                        <td scope="row"><?php echo text($data->labTypeName); ?></td>
                                        <td scope="row"><?php echo text($data->phoneNumber); ?></td>
                                        <td scope="row"><?php echo text($data->faxNumber); ?></td>
                                        <td scope="row"><?php echo text($data->address1); ?><?php echo text($data->address2); ?> </td>
                                        <td scope="row"><?php echo text($data->city); ?></td>
                                        <td scope="row"><?php echo text($data->state); ?></td>
                                        <td scope="row"><?php echo text($data->zipCode); ?></td>
                                        <td scope="row"><?php echo text(substr($data->lastCompendiumUpdateDate, 0, 10)); ?></td>
                                        <td scope="row"><?php echo text($data->compendiumDownloadDateTime); ?></td>
                                        <td scope="row"><?php echo text($data->numberOfActiveRoutes); ?></td>
                                        <td scope="row">
                                            <button type="button" class="btn btn-primary" onclick="createRouteclick_edit(<?php echo attr_js($data->labGuid); ?>)"><?php echo xlt('Create Route'); ?></button>
                                            <button type="button" class="btn btn-primary" onclick="installCompendiumClick(<?php echo attr_js($data->labGuid); ?>)"><?php echo xlt('Install Compendium'); ?></button>
                                        </td>
                                    </tr>

                                <?php } //end foreach ?>
                                </tbody>
                            </table>

                        </div>

                    </div>
                </div> <!-- end card -->
            </form>
        </div>

    </div>

</body>
</html>
