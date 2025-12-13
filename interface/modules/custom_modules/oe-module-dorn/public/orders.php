<?php

/**
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022-2025 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

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
use OpenEMR\Modules\Dorn\ConnectorApi;
use OpenEMR\Core\Header;

//this is needed along with setupHeader() to get the pop up to appear

$tab = "orders";
$pageTitle = xl("DORN Orders");
if (!AclMain::aclCheckCore('patients', 'lab')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => $pageTitle]);
    exit;
}
$primaryInfos = ConnectorApi::getPrimaryInfos('');
if (!empty($_POST)) {
    if (isset($_POST['SubmitButton'])) {
        //check if form was submitted
        $datas = ConnectorApi::searchOrderStatus($_POST['form_orderNumber'], $_POST['form_primaryId'], $_POST['form_startDateTime'], $_POST['form_endDateTime']);
        if ($datas == null) {
            $datas = [];
        }
    }
}
?>
<!DOCTYPE html>
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
<body class="container-fluid">
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
                    <h5 class="card-title"><?php echo xlt("DORN - Lab Pending or Queued Orders"); ?></h5>
                    <div class="row">
                        <div class="col">
                            <form method="post" action="orders.php">
                                <div class="row">
                                    <div class="col-md-4">
                                        <select name="form_primaryId">
                                            <?php foreach ($primaryInfos as $primaryInfo) {
                                                $selected = $primaryInfo->primaryId === $_POST['form_primaryId'] ? "selected" : "";
                                                ?>
                                                <option value='<?php echo attr($primaryInfo->primaryId); ?>' <?php echo $selected; ?>>
                                                    <?php echo text($primaryInfo->primaryName); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="form_orderNumber"><?php echo xlt("Order Number") ?>:</label>
                                            <input type="text" class="form-control" id="form_orderNumber" name="form_orderNumber" value="<?php echo isset($_POST['form_orderNumber']) ? attr($_POST['form_orderNumber']) : '' ?>" />
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="form_startDateTime"><?php echo xlt("Start Date") ?>:</label>
                                            <input type="date" class="form-control datepicker" id="form_startDateTime" name="form_startDateTime" value="<?php echo isset($_POST['form_startDateTime']) ? attr($_POST['form_startDateTime']) : '' ?>" />
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="form_endDateTime"><?php echo xlt("End Date") ?>:</label>
                                            <input type="date" class="form-control datepicker" id="form_endDateTime" name="form_endDateTime" value="<?php echo isset($_POST['form_endDateTime']) ? attr($_POST['form_endDateTime']) : '' ?>" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <button type="submit" name="SubmitButton" class="btn btn-primary mb-1" onclick="$('#loading').removeClass(('d-none'));"><?php echo xlt("Submit") ?></button>
                                        <i class="fa fa-gear fa-spin fa-2x text-primary d-none" id="loading" role="status" aria-hidden="true"></i>
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
                            } else {
                                ?>
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th scope="col"><?php echo xlt("Lab Name") ?></th>
                                        <th scope="col"><?php echo xlt("Create Date") ?></th>
                                        <th scope="col"><?php echo xlt("Order Number") ?></th>
                                        <th scope="col"><?php echo xlt("Order Status") ?></th>
                                        <th scope="col"><?php echo xlt("Is Pending") ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    foreach ($datas as $data) {
                                        ?>
                                        <tr>
                                            <td scope="row"><?php echo text($data->labName); ?></td>
                                            <td scope="row"><?php echo text(date('Y-m-d H:i:s', strtotime((string) $data->createdDateTimeUtc))); ?></td>
                                            <td scope="row"><?php echo text($data->orderNumber); ?></td>
                                            <td scope="row"><?php echo text($data->orderStatusLong); ?></td>
                                            <td scope="row"><?php echo text($data->isPending); ?></td>
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
                </div>
            </div>
        </div>
    </div>
</body>
</html>
