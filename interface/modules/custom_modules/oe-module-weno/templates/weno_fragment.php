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
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Modules\WenoModule\Services\TransmitProperties;
use OpenEMR\Modules\WenoModule\Services\WenoLogService;

if (!AclMain::aclCheckCore('patients', 'med')) {
    exit;
}

$logService = new WenoLogService();
$pharmacy_log = $logService->getLastPharmacyDownloadStatus();

$validate = new TransmitProperties(true);
$validate_errors = "";
$cite = '';

$logService = new WenoLogService();
$pharmacyLog = $logService->getLastPharmacyDownloadStatus();
$status = xlt("Last pharmacy update failed! Current count") . ": " . text($pharmacyLog['count'] ?? 0) . ". " . "Last success was" . ": " . text($pharmacyLog['created_at'] ?? '');
$cite = <<<CITE
<cite class="h6 text-danger p-1 mt-1">
    <span>$status</span>
</cite>
CITE;
if ($pharmacyLog['status'] == 'Success') {
    $cite = '';
}

$hasErrors = !empty($validate->errors['errors']);
$hasWarnings = !empty($validate->errors['warnings']);
$justWarnings = $hasWarnings && empty($validate->errors['errors']);
$validate_errors = $validate->errors['string'];

$pid = ($pid ?? '') ?: $_SESSION['pid'] ?? '';
$res = sqlStatement("SELECT * FROM prescriptions WHERE patient_id = ? AND indication IS NOT NULL", array($pid));

function getProviderByWenoId($external_id, $provider_id = ''): string
{
    $provider = sqlQuery("SELECT fname, mname, lname FROM users WHERE weno_prov_id = ? OR id = ?", array($external_id, $provider_id));
    if ($provider) {
        return $provider['fname'] . " " . $provider['mname'] . " " . $provider['lname'];
    } else {
        return xlt("Weno Provider Id missing.");
    }
}

?>

<script src="<?php echo $GLOBALS['webroot'] ?>/interface/modules/custom_modules/oe-module-weno/public/assets/js/synch.js"></script>

<div class="row float-right mr-1">
    <div role="button">
        <u><span class="click" onclick="sync_weno()"><i id="sync-icon" class="fa-solid fa-rotate-right mr-1"></i><?php echo xlt("Refresh"); ?></span></u>
        <a href="<?php echo $GLOBALS['webroot'] ?>/interface/modules/custom_modules/oe-module-weno/templates/indexrx.php"><span><i class="fa-solid fa-pen ml-2 mb-2"></i></span></a>
    </div>
</div>
<input type="hidden" id="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken('default')); ?>" />

<div id="sync-alert" class=""><?php echo $cite; ?></div>
<?php if (!$hasErrors) { ?>
    <div id="sync-alert" class="d-none"></div>
    <br>
<?php }
if ($hasWarnings || $hasErrors) { ?>
    <div id="error-alert" class="alert <?php echo !$justWarnings ? 'alert-danger' : 'alert-warning'; ?> mt-2 px-0 py-1" role="alert">
        <span class="text-warning"><strong><?php echo xlt("Problems!"); ?></strong></span> <span><?php echo xlt("Weno eRx is not fully configured. Details"); ?></span>
        <a role="button" class="btn btn-link p-0 pl-1" onclick="$('.dialog-alert').toggleClass('d-none')"><i class="fa fa-question-circle close"></i></a>
        <div id="dialog-alert" class="dialog-alert m-0 p-0 pt-1 d-none">
            <div id="dialog-content" class="dialog-content"><?php echo $validate_errors; ?></div>
        </div>
    </div>
<?php } ?>
<div class="table-responsive">
    <table class="table table-sm table-hover table-striped w-100">
        <thead class="thead thead-light border-bottom">
        <tr>
            <th><?php echo xlt("Drug Name"); ?></th>
            <th><?php echo xlt("Prescriber"); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (empty($res->_numOfRows)) {
            echo "<tr>" .
                "<td>" . xlt("No Weno eRx prescriptions found.") . "</td>" .
                "<td>" . xlt("Verify from your Weno account if any are expected.") . "</td>" .
                "</tr>";
        }
        while ($row = sqlFetchArray($res)) { ?>
            <tr>
                <td><?php echo text($row["drug"]); ?></td>
                <td><?php echo text(getProviderByWenoId($row['external_id'], $row['provider_id'])); ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
