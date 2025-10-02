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
<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(); ?>
    <title> <?php echo xlt("DORN Configuration"); ?></title>
</head>
<script>
    // todo allow message pass in to bypass file load.
    function notifyUserWithPersist(message, timer = 5000, type = 'alert-success', size = '', persist = '', value = '', labToken = '') {
        return new Promise((resolve, reject) => {
            $('#notice').remove();
            const gotIt = xl("I agree to these terms.");
            const title = persist + ' ' + xl("Terms of Use");
            const dismiss = xl("I Decline. If you decline, you will not be able to use this feature.");
            const hiddenAttr = persist ? '' : 'hidden';
            const oSize = (size === 'lg') ? 'left:15%;width:70%;max-height:70vh;' : 'left:25%;width:50%;';
            const style = `position:fixed;top:1%;${oSize}bottom:0;z-index:9999;`;

            $("body").prepend(`<div class='container-fluid' id='notice' style='${style}'></div>`);

            const mHtml = `
                <div id="notice-msg" class="alert ${type} alert-dismissable">
                    <h4 class="alert-heading text-center">${title}!</h4>
                    <hr>
                    <div class="p-2 bg-light text-dark" style="max-height:70vh; overflow-y:auto;" id="eula"></div>
                    <div class="text-center">
                        <button type="button" id="alertDismissButton" class="btn btn-outline-danger mt-2" data-dismiss="alert">${dismiss}</button>
                        <button type="button" class="btn btn-outline-success mt-2 ${hiddenAttr}" id="acceptButton" data-dismiss="alert">${gotIt}&nbsp;<i class="fa fa-thumbs-up"></i></button>
                </div>
                </div>`;
            // Append the HTML to the notice div
            $('#notice').append(mHtml);
            let where = "./lab-eulas/" + labToken + "_eula.php";
            $("#eula").load(where, {}, function () {
            });

            const cleanUp = () => {
                clearTimeout(AlertMsg);
                $('#notice').remove();
            };
            const AlertMsg = setTimeout(() => {
                $('#notice-msg').fadeOut(800, () => {
                    cleanUp();
                    resolve('timeout');
                });
            }, timer);
            $('#notice-msg').on('closed.bs.alert', () => {
                cleanUp();
                resolve('dismissed');
                return false;
            });
            $('#acceptButton').on('click', async () => {
                cleanUp();
                if (persist) {
                    try {
                        await persistUserOption(labToken, value);
                    } catch (e) {
                        console.warn("Persist failed:", e);
                    }
                }
                resolve('accepted');
            });
            $('#alertDismissButton').on('click', () => {
                cleanUp();
                resolve('dismissed');
            });
        });
    }

    async function doLabEULA(labName) {
        let labToken = normalizeToFilename(labName);
        if (labToken.search('labcorp') !== -1) {
            labToken = 'labcorp';
        }
        return await notifyUserWithPersist('', 45000, 'alert-dark', 'lg', labName, 'dorn_lab_eula', labToken);
    }

    function createRouteClickEdit(labGuid, labName = '', isEulaRequired = false) {
        // dialog open calls restoreSession()
        let addTitle = '<i class="fa fa-plus" style="width:20px;" aria-hidden="true"></i> ' + <?php echo xlj("Create Route"); ?>;
        let scriptTitle = 'route_edit.php?labGuid=' + encodeURIComponent(labGuid) + '&isEula=' + encodeURIComponent(isEulaRequired) + '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>;
        // Call the doEULA function then continue with route dialog open if accepted.
        if (isEulaRequired) {
            doLabEULA(labName).then((result) => {
                if (result === 'accepted') {
                    dlgopen(scriptTitle, '_blank', 800, 750, false, addTitle, {
                            sizeHeight: 'auto',
                            allowResize: true,
                            allowDrag: false,
                    });
                }
            }).catch(error => {
                console.log("Error in EULA dialog:", error);
            });
        } else {
            dlgopen(scriptTitle, '_blank', 800, 750, false, addTitle, {
                sizeHeight: 'auto',
                allowResize: true,
                allowDrag: false,
            });
        }
    }

    function installCompendiumClick(labGuid) {
        let addTitle = '<i class="fa fa-plus" style="width:20px;" aria-hidden="true"></i> ' + <?php echo xlj("Edit Mode"); ?>;
        let scriptTitle = 'compendium_install.php?labGuid=' + encodeURIComponent(labGuid) + '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>;
        dlgopen(scriptTitle, '_blank', 500, 650, false, addTitle);
    }
</script>
<body class="container-fluid">
    <div class="row">
        <div class="col">
            <?php
            require '../templates/navbar.php';
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col text-center mt-1">
            <h3><?php echo xlt("DORN - Lab Search and Setup"); ?></h3>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <form method="post" action="lab_setup.php">
                <div class="card">
                    <div class="container mt-3 mb-2">
                        <legend><?php echo xlt("Search for Labs"); ?></legend>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="form_labName"><?php echo xlt("Lab Name") ?>:</label>
                                    <input type="text" class="form-control" id="form_labName" name="form_labName" placeholder="<?php echo xla("Search by Lab name"); ?>" value="<?php echo isset($_POST['form_labName']) ? attr($_POST['form_labName']) : '' ?>" />
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
                                    <input type="text" class="form-control" id="form_city" name="form_city" placeholder="<?php echo xla("Search City for Lab"); ?>" value="<?php echo isset($_POST['form_city']) ? attr($_POST['form_city']) : '' ?>" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="form_state"><?php echo xlt("State") ?>:</label>
                                    <input type="text" class="form-control" id="form_state" name="form_state" placeholder="<?php echo xla("Search State for Lab"); ?>" value="<?php echo isset($_POST['form_state']) ? attr($_POST['form_state']) : '' ?>" />
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
                                    <select id="form_active" name="form_active">
                                        <option value=""><?php echo xlt("All") ?></option>
                                        <option value="yes" <?php echo isset($_POST['form_active']) ? attr($_POST['form_active']) == 'yes' ? ' selected ' : '' : '' ?> ><?php echo xlt("Yes"); ?></option>
                                        <option value="no" <?php echo isset($_POST['form_active']) ? attr($_POST['form_active']) == 'no' ? ' selected ' : '' : '' ?> ><?php echo xlt("No"); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <button type="submit" onsubmit="return top.restoreSession()" name="SubmitButton" class="btn btn-primary mb-2" onclick="$('#loading').removeClass(('d-none'));"><?php echo xlt("Search") ?></button>
                                <i class="fa fa-gear fa-spin fa-2x text-primary d-none" id="loading" role="status" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <legend><?php echo xlt("Search Results"); ?></legend>
                            <div class="col table-responsive">
                                <table class="table table-sm table-striped">
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
                                        <th scope="col"><?php echo xlt("EULA") ?></th>
                                        <th scope="col"> <?php echo xlt("Actions") ?> </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($datas as $data) { ?>
                                    <tr>
                                        <td scope="row"><?php echo text($data->name); ?></td>
                                        <td scope="row"><?php echo text($data->labTypeName); ?></td>
                                        <td scope="row"><?php echo text($data->phoneNumber); ?></td>
                                        <td scope="row"><?php echo text($data->faxNumber); ?></td>
                                        <td scope="row"><?php echo text($data->address1); ?><?php echo text($data->address2); ?> </td>
                                        <td scope="row"><?php echo text($data->city); ?></td>
                                        <td scope="row"><?php echo text($data->state); ?></td>
                                        <td scope="row"><?php echo text($data->zipCode); ?></td>
                                        <td scope="row"><?php echo text(substr((string) $data->lastCompendiumUpdateDate, 0, 10)); ?></td>
                                        <td scope="row"><?php echo text($data->compendiumDownloadDateTime); ?></td>
                                        <td scope="row"><?php echo text($data->numberOfActiveRoutes); ?></td>
                                        <td scope="row"><?php echo text($data->isEulaRequired ? 'Yes' : ''); ?></td>
                                        <td scope="row">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-primary" onclick="createRouteClickEdit(
                                                <?php echo attr_js($data->labGuid) . ', ' . attr_js($data->name) . ', ' . attr_js($data->isEulaRequired); ?>)"><?php echo xlt('Create Route'); ?></button>
                                                <button type="button" class="btn btn-primary" onclick="installCompendiumClick(<?php echo attr_js($data->labGuid); ?>)"><?php echo xlt('Install Compendium'); ?></button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php } //end foreach ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div> <!-- end card -->
            </form>
        </div>

    </div>

</body>
</html>
