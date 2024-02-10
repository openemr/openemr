<?php

/**
 * weno_fragment.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kofi Appiah <kkappiah@medsov.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2023 omega systems group international <info@omegasystemsgroup.com>
 * @copyright Copyright (c) 2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Modules\WenoModule\Services\TransmitProperties;

if (!AclMain::aclCheckCore('patients', 'med')) {
    exit;
}

$validate = new TransmitProperties(true);
$validate_errors = "";
if (!empty($validate->errors)) {
    $validate_errors = ($validate->errors);
}
$pid = $_SESSION['pid'];
$res = sqlStatement("SELECT * FROM prescriptions WHERE patient_id = ? AND indication IS NOT NULL", array($pid));

function getProviderByWenoId($external_id): string
{
    $provider = sqlQuery("SELECT fname, mname, lname FROM users WHERE weno_prov_id = ? OR id = ?", array($external_id, $external_id));
    if ($provider) {
        return $provider['fname'] . " " . $provider['mname'] . " " . $provider['lname'];
    } else {
        return "ERROR:" . xlt("Missing Weno Provider Id.");
    }
}

?>

<script src="<?php echo $GLOBALS['webroot'] ?>/interface/modules/custom_modules/oe-module-weno/public/assets/js/synch.js"></script>

<div class="row float-right mr-2 mb-2">
    <div class="mr-3" role="button">
        <u><span class="click" onclick="sync_weno()"><i id="sync-icon" class="fa-solid fa-rotate-right mr-1"></i><?php echo xlt("Sync"); ?></span></u>
        <a href="<?php echo $GLOBALS['webroot'] ?>/interface/modules/custom_modules/oe-module-weno/templates/indexrx.php">
            <span><i class="fa-solid fa-pen mx-2"></i></span>
        </a>
    </div>
</div>
<?php if (empty($validate_errors)) { ?>
    <div id="sync-alert" class="d-none"></div>
<?php } else { ?>
    <div id="sync-alert" class="alert alert-danger p-1">
        <span><strong><?php echo text("Problems!"); ?></strong> <?php echo xlt("Weno eRx is not fully configured. Details"); ?></span>
        <a role="button" class="btn btn-link pl-0" onclick="$('.dialog-alert').toggleClass('d-none')"><i class="fa fa-info-circle close"></i></a>
        <div id="dialog-alert" class="dialog-alert alert alert-danger m-0 p-0 d-none" role="alert">
            <div id="dialog-content" class="dialog-content p-2" style="color: white;"><?php echo $validate_errors; ?></div>
        </div>
    </div>
<?php } ?>
<div class="table-responsive">
    <table class="table w-100">
        <thead class="thead-light border-bottom">
        <tr>
            <th><?php echo xlt("Drug Name"); ?></th>
            <th><?php echo xlt("Prescriber"); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php
        while ($row = sqlFetchArray($res)) { ?>
            <tr>
                <td><?php echo text($row["drug"]); ?></td>
                <td><?php echo text(getProviderByWenoId($row['external_id'])); ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
