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
use OpenEMR\Modules\Dorn\ConnectorApi;
use OpenEMR\Core\Header;

//this is needed along with setupHeader() to get the pop up to appear

$tab = "routes";
$pageTitle = xl("DORN - Routes");
if (!AclMain::aclCheckCore('admin', 'users')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => $pageTitle]);
    exit;
}

if (!empty($_POST)) {
    if (isset($_POST['SubmitButton'])) {
        $datas = ConnectorApi::getRoutesFromDorn();
        if ($datas == null) {
            $datas = [];
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'delete') {
    $result = ConnectorApi::deleteRoutesFromDorn(
        $_POST['labGuid'] ?? '',
        $_POST['accountNumber'] ?? ''
    );
    $datas = ConnectorApi::getRoutesFromDorn();
    if ($datas == null) {
        $datas = [];
    }
}
?>
<html lang="">
<head>
    <?php Header::setupHeader(['datetime-picker']); ?>
    <title> <?php echo text($pageTitle); ?>  </title>
</head>
<script>
    $(function () {
        $('.datepicker').datetimepicker({
            <?php $datetimepicker_timepicker = false; ?>
            <?php $datetimepicker_showseconds = false; ?>
            <?php $datetimepicker_formatInput = false; ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        });
    });

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
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><?php echo xlt("DORN - Routes"); ?></h5>
                    <div class="row">
                        <div class="col">
                            <form method="post" action="routes.php">
                                <div class="row">
                                    <div class="col-md-1">
                                        <button type="submit" name="SubmitButton" class="btn btn-primary" onclick="$('#loading').removeClass(('d-none'));"><?php echo xlt("Submit") ?></button>
                                        <i class="fa fa-gear fa-spin fa-2x text-primary d-none" id="loading" role="status" aria-hidden="true"></i>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?php if (empty($datas)) : ?>
                                <div class="alert alert-info my-3">
                                    <?php xlt("No routes found") ?>
                                </div>
                            <?php else : ?>
                                <table class="table table-hover table-striped">
                                    <thead>
                                    <tr>
                                        <th scope="col"><?php echo xlt("Lab") ?></th>
                                        <th scope="col"><?php echo xlt("Lab Account Number") ?></th>
                                        <th scope="col"><?php echo xlt("Primary") ?></th>
                                        <th scope="col"><?php echo xlt("Phone") ?></th>
                                        <th scope="col"><?php echo xlt("Email") ?></th>
                                        <th scope="col"><?php echo xlt("Status") ?></th>
                                        <th scope="col"><?php echo xlt("Created (UTC)") ?></th>
                                        <th scope="col"><?php echo xlt("Actions") ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($datas as $data) : ?>
                                        <tr>
                                            <td scope="row"><?php echo text($data->labName) ?></td>
                                            <td scope="row"><?php echo text($data->accountNumber) ?></td>
                                            <td scope="row"><?php echo text($data->primaryName) ?></td>
                                            <td scope="row"><?php echo text($data->primaryPhone) ?></td>
                                            <td scope="row">
                                                <?php if (!empty($data->primaryEmail)) : ?>
                                                    <a href="mailto:<?php attr($data->primaryEmail) ?>">
                                                        <?php echo text($data->primaryEmail) ?>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                            <td scope="row"><?php echo text($data->status) ?></td>
                                            <td scope="row">
                                                <?php
                                                // Convert to OpenEMR’s short date format
                                                $created = oeFormatShortDate(date('Y-m-d', strtotime((string) $data->createdDateTimeUtc)));
                                                echo text($created);
                                                ?>
                                            </td>
                                            <td scope="row">
                                                <form method="post" action="routes.php" class="d-inline">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="labGuid" value="<?php echo attr($data->labGuid) ?>">
                                                    <input type="hidden" name="accountNumber" value="<?php echo attr($data->accountNumber) ?>">
                                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this route?')">
                                                        <?php echo xlt('Delete') ?>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>
</html>
