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
use OpenEMR\Modules\WenoModule\Services\PharmacyService;
use OpenEMR\Modules\WenoModule\Services\TransmitProperties;
use OpenEMR\Modules\WenoModule\Services\WenoLogService;

if (!AclMain::aclCheckCore('patients', 'med')) {
    echo xlt("Not Authorized to use this widget.");
    return;
}

$validate = new TransmitProperties(true);
$validate_errors = "";
$cite = '';

if (stripos($validate->getWenoProviderId(), 'Weno User Id missing') !== false) {
    echo xlt("Not Authorized! Missing Weno Prescriber Id. See User Settings to configure Weno Prescriber Id.");
    return "Fail";
}

$logService = new WenoLogService();
$pharmacyLog = $logService->getLastPharmacyDownloadStatus('Success');

$status = xlt("Last pharmacy update") . ": " . text($pharmacyLog['status'] ?? '') . ". " . xlt("Number Pharmacies available") . ": " . text($pharmacyLog['count'] ?? 0);
$cite = <<<CITE
<cite class="h6 text-danger p-1 mt-1">
    <span>$status</span>
</cite>
CITE;
if (str_starts_with($pharmacyLog['status'], 'Success')) {
    $cite = '';
}

$hasErrors = !empty($validate->errors['errors']);
$hasWarnings = !empty($validate->errors['warnings']);
$justWarnings = $hasWarnings && empty($validate->errors['errors']);
$validate_errors = $validate->errors['string'];

$pid = ($pid ?? '') ?: $_SESSION['pid'] ?? '';
$res = sqlStatement("SELECT * FROM prescriptions WHERE patient_id = ? AND indication IS NOT NULL", array($pid));

$pharmacyService = new PharmacyService();
$prim_pharmacy = $pharmacyService->getWenoPrimaryPharm($_SESSION['pid']) ?? false;
$alt_pharmacy = $pharmacyService->getWenoAlternatePharm($_SESSION['pid']) ?? false;
$primary_pharmacy = ($prim_pharmacy['business_name'] ?? false) ? ($prim_pharmacy['business_name'] . ' - ' . ($prim_pharmacy['address_line_1'] ?? '') . ' ' . ($prim_pharmacy['city'] ?? '') . ', ' . ($prim_pharmacy['state'] ?? '')) : '';
$alternate_pharmacy = ($alt_pharmacy['business_name'] ?? false) ? ($alt_pharmacy['business_name'] . ' - ' . ($alt_pharmacy['address_line_1'] ?? '') . ' ' . ($alt_pharmacy['city'] ?? '') . ', ' . $alt_pharmacy['state'] ?? '') : '';

function getProviderByWenoId($external_id, $provider_id = ''): string
{
    $provider = sqlQuery("SELECT fname, mname, lname FROM users WHERE weno_prov_id = ? OR id = ?", array($external_id, $provider_id));
    if ($provider) {
        return $provider['fname'] . " " . $provider['mname'] . " " . $provider['lname'];
    } else {
        return xlt("Weno User Id missing.");
    }
}

?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Get the labels and select elements
        const primaryLabel = document.getElementById("label-primary");
        const alternateLabel = document.getElementById("label-alternate");

        const primaryPharmacySpan = document.getElementById("primary-pharmacy");
        const alternatePharmacySpan = document.getElementById("alternate-pharmacy");

        const primarySelect = document.getElementById("select-primary");
        const alternateSelect = document.getElementById("select-alternate");

        // Event listener for the primary label
        primaryLabel.addEventListener("click", function() {
            primaryPharmacySpan.classList.add("d-none");
            primarySelect.classList.remove("d-none");
        });

        // Event listener for the alternate label
        alternateLabel.addEventListener("click", function() {
            alternatePharmacySpan.classList.add("d-none");
            alternateSelect.classList.remove("d-none");
        });
    });

</script>
<script src="<?php echo $GLOBALS['webroot'] ?>/interface/modules/custom_modules/oe-module-weno/public/assets/js/synch.js"></script>
<style>
    .dialog-alert {
        font-size:14px;
    }
</style>
<div class="row float-right mr-1">
    <div>
        <a class="mr-2" href="#" onclick="top.restoreSession(); sync_weno();"><span><i id="sync-icon" class="fa-solid fa-rotate-right mr-1"></i><?php echo xlt("Sync"); ?></span></a>
        <a class="mr-2" onclick="top.restoreSession();" href="<?php echo $GLOBALS['webroot'] ?>/interface/modules/custom_modules/oe-module-weno/templates/indexrx.php"><span><i class="fa fa fa-pencil-alt mr-1"></i><?php echo xlt("Prescribe"); ?></span></a>
    </div>
</div>
<input type="hidden" id="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken('default')); ?>" />

<div id="sync-alert" class=""><?php echo $cite; ?></div>
<?php if (!$hasErrors) { ?>
    <div id="sync-alert" class="d-none"></div>
    <br>
<?php }
if (!$hasWarnings || $hasErrors) { ?>
    <div class="container-fluid m-0 p-0">
        <div id="error-alert" class="col alert <?php echo !$justWarnings ? 'alert-danger' : 'alert-warning'; ?> mt-2 px-0 py-1" role="alert">
            <span class="text-danger"><span><?php echo xlt("Problems!"); ?></span></span> <span class="text-dark"><?php echo xlt("Weno eRx is not fully configured. Details"); ?></span>
            <a role="button" class="btn btn-link p-0 pl-1" onclick="$('.dialog-alert').toggleClass('d-none')"><i class="fa fa-question-circle close"></i></a>
            <div id="dialog-alert" class="dialog-alert m-0 p-0 pt-1 small d-none">
                <div id="dialog-content" class="dialog-content text-danger" style="background-color: #fff"><?php echo $validate_errors; ?></div>
            </div>
        </div>
    </div>
<?php } ?>
<div class="form-group mb-0 small">
    <div class="input-group">
        <label id="label-primary" class="text-primary mb-0 mr-2" for="select-primary"><b><?php echo xlt("Assigned Primary"); ?>:</b></label>
        <cite><span id="primary-pharmacy"><?php echo text($primary_pharmacy); ?></span></cite>
        <!-- Placeholder for the select element -->
        <select id="select-primary" class="d-none">
            <?php foreach ($pharmacies as $pharmacy) : ?>
                <option value="<?php echo htmlspecialchars($pharmacy); ?>"><?php echo htmlspecialchars($pharmacy); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="input-group">
        <label id="label-alternate" class="text-primary mb-1 mr-1" for="select-alternate"><b><?php echo xlt("Assigned Alternate"); ?>:</b></label>
        <cite><span id="alternate-pharmacy"><?php echo text($alternate_pharmacy); ?></span></cite>
        <!-- Placeholder for the select element -->
        <select id="select-alternate" class="d-none">
            <?php foreach ($pharmacies as $pharmacy) : ?>
                <option value="<?php echo htmlspecialchars($pharmacy); ?>"><?php echo htmlspecialchars($pharmacy); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
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
