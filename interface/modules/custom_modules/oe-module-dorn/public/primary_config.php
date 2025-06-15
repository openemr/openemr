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

$tab = "Configure Primary";

if (!AclMain::aclCheckCore('admin', 'users')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Edit/Add Procedure Provider")]);
    exit;
}

if (!empty($_POST)) {
    if (isset($_POST['SubmitButton'])) { //check if form was submitted
        $datas = ConnectorApi::getPrimaryInfos($_POST['npi']);
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
</head>
<title> <?php echo xlt("DORN Configuration"); ?>  </title>
<script>
    // Process click to pop up the add window.
    function doedclick_edit(npi) {
        top.restoreSession();
        var addTitle = '<i class="fa fa-plus" style="width:20px;" aria-hidden="true"></i> ' + <?php echo xlj("Edit Mode"); ?>;
        let scriptTitle = 'primary_config_edit.php?npi=' + encodeURIComponent(npi) + '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>;
        dlgopen(scriptTitle, '_blank', 600, 750, false, addTitle, {
            buttons: [
                {text: '<?php echo xla('Close'); ?>', close: true, style: 'secondary btn-sm'}
            ]
        });
    }

    function doedclick_add() {
        top.restoreSession();
        var addTitle = '<i class="fa fa-plus" style="width:20px;" aria-hidden="true"></i> ' + <?php echo xlj("Edit Mode"); ?>;
        let scriptTitle = 'primary_config_edit.php?csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>;
        dlgopen(scriptTitle, '_blank', 600, 750, false, addTitle, {
            buttons: [
                {text: '<?php echo xla('Close'); ?>', close: true, style: 'secondary btn-sm'}
            ]
        });
    }
</script>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col">
                <?php
                require '../templates/navbar.php';
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <h3><?php echo xlt("DORN - Primary Setup"); ?></h3>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="row">
                        <div class="col">
                            <p>
                                <?php echo xlt("The DORN network requires basic address setup for your clinic. This setup is based on NPI. The first and default entry will be your billing NPI. If there are other NPI's in your clinic that have their own account at a lab, then more than 1 entry can be created here."); ?>
                            </p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <form method="post" action="primary_config.php">
                                <div class="card">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="npi"><?php echo xlt("NPI") ?>:</label>
                                                <input type="text" class="form-control" id="npi" name="npi" value="<?php echo isset($_POST['npi']) ? attr($_POST['npi']) : '' ?>" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <button type="submit" name="SubmitButton" class="btn btn-primary mb-2"><?php echo xlt("Submit") ?></button>
                                        </div>
                                        <div class="col-md-3">
                                            <button type="button" class="btn btn-primary btn-add mb-2" onclick="doedclick_edit()"><?php echo xlt('Add New'); ?></button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?php
                            if (empty($datas)) {
                                echo xlt("No results found");
                            } else { ?>
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th scope="col"><?php echo xlt("NPI") ?></th>
                                        <th scope="col"><?php echo xlt("Name") ?></th>
                                        <th scope="col"><?php echo xlt("Phone") ?></th>
                                        <th scope="col"><?php echo xlt("Email") ?></th>
                                        <th scope="col"><?php echo xlt("Address") ?></th>
                                        <th scope="col"><?php echo xlt("Actions") ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    foreach ($datas as $data) {
                                        ?>
                                        <tr>
                                            <td scope="row"><?php echo text($data->npi); ?></td>
                                            <td scope="row"><?php echo text($data->primaryName); ?></td>
                                            <td scope="row"><?php echo text($data->primaryPhone); ?></td>
                                            <td scope="row"><?php echo text($data->primaryEmail); ?></td>
                                            <td scope="row">
                                                <div class="row">
                                                    <div class="col">
                                                        <?php echo text($data->primaryAddress1); ?>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col">
                                                        <?php echo text($data->primaryAddress2); ?>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col">
                                                        <?php echo text($data->primaryCity); ?><?php echo text($data->primaryState); ?><?php echo text($data->primaryZipCode); ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td scope="row">
                                                <button type="button" class="btn btn-primary btn-add mb-2" onclick="doedclick_edit(<?php echo attr_js($data->npi ?? ''); ?>)"><?php echo xlt('Edit'); ?></button>
                                            </td>
                                        </tr>
                                        <?php
                                    }//end foreach
                                    ?>
                                    </tbody>
                                </table>
                                <?php
                            }//end empty data
                            ?>
                        </div>
                    </div>
                </div> <!-- End Card -->
            </div>
        </div>
        <div class="row">
            <div class="col">
                <?php
                require '../templates/contact.php';
                ?>
            </div>
        </div>
    </div>
</body>
</html>
