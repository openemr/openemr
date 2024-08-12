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

if (!AclMain::aclCheckCore('patients', 'demo', '', 'write')) {
    echo xlt("Not Authorized to use this widget.");
    return;
}

$validate = new TransmitProperties(true);
$validate_errors = "";
$cite = '';

if (stripos($validate->getWenoProviderId(), 'Weno User Id missing') !== false) {
    echo xlt("Not Authorized! Missing Weno Prescriber Id. See User Settings Weno tab to configure Weno Prescriber Id.");
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
$validate_errors = $validate->errors['string'];

$pid = ($pid ?? '') ?: $_SESSION['pid'] ?? '';
$res = sqlStatement("SELECT * FROM prescriptions WHERE patient_id = ? AND indication IS NOT NULL", array($pid));

$pharmacyService = new PharmacyService();
$prim_pharmacy = $pharmacyService->getWenoPrimaryPharm($_SESSION['pid']) ?? false;
$alt_pharmacy = $pharmacyService->getWenoAlternatePharm($_SESSION['pid']) ?? false;

$primary_pharmacy = ($prim_pharmacy['business_name'] ?? false) ? ($prim_pharmacy['business_name'] . ' - ' .
    ($prim_pharmacy['address_line_1'] ?? '') . ' ' . ($prim_pharmacy['city'] ?? '') .
    ', ' . ($prim_pharmacy['state'] ?? '')) : '';

$alternate_pharmacy = ($alt_pharmacy['business_name'] ?? false) ? ($alt_pharmacy['business_name'] . ' - ' .
($alt_pharmacy['address_line_1'] ?? '') . ' ' . ($alt_pharmacy['city'] ?? '') .
', ' . $alt_pharmacy['state'] ?? '') : '';

$res = sqlStatement(
    "SELECT DISTINCT wp.ncpdp_safe, wp.business_name, wp.address_line_1, wp.city, wp.state FROM weno_assigned_pharmacy wap INNER JOIN weno_pharmacy wp ON wap.primary_ncpdp = wp.ncpdp_safe OR wap.alternate_ncpdp = wp.ncpdp_safe;
"
);
$pharmacies = array();
foreach ($res as $row) {
    $pharmacies[] = $row;
}
$pharmacyCount = count($pharmacies);

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
<script src="<?php echo $GLOBALS['webroot'] ?>/interface/modules/custom_modules/oe-module-weno/public/assets/js/synch.js"></script>
<style>
  .dialog-alert {
    font-size: 14px;
  }
</style>
<div class="row float-right mr-1">
    <div id="widget-button-set">
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
if ($hasErrors) { ?>
    <div class="container-fluid m-0 p-0">
        <div id="error-alert" class="col alert alert-danger mt-2 px-0 py-1" role="alert">
            <span class="text-danger"><span><?php echo xlt("Problems!"); ?></span></span> <span class="text-dark"><?php echo xlt("Weno eRx is not fully configured. Details"); ?></span>
            <a role="button" class="btn btn-link p-0 pl-1" onclick="$('.dialog-alert').toggleClass('d-none')"><i class="fa fa-question-circle close"></i></a>
            <div id="dialog-alert" class="dialog-alert m-0 p-0 pt-1 small d-none">
                <div id="dialog-content" class="dialog-content text-danger" style="background-color: #fff"><?php echo $validate_errors; ?></div>
            </div>
        </div>
    </div>
<?php } ?>
<button class="btn btn-link btn-refresh p-0 mr-4 float-right" onclick="refreshDemographics();"><?php echo xlt("Refresh Chart"); ?></button>
<?php if ($pharmacyCount > 0) {
    $titleMessage = xla("Quick Pharmacy Assignment");
    $popoverContent = xla("Convenience feature for assigning pharmacy without having to edit Demographics. Click the label or existing pharmacy name, if assigned, to select a pharmacy from a list of all currently assigned pharmacies. The selected pharmacy will be assigned to this patient.");
    ?>
    <div id="trigger-debug" class="form-group mb-0 small">
        <div class="input-group">
            <label role="button" id="label-primary" class="text-primary mb-0 mr-2" for="select-primary" title="<?php echo $titleMessage ?>" data-toggle="popover" data-content="<?php echo $popoverContent ?>">
                <b><?php echo xlt("Assigned Primary"); ?>:</b>
            </label>
            <input type="hidden" id="prim_ncpdp" name="prim_ncpdp" value="<?php echo attr($prim_pharmacy['ncpdp_safe'] ?? ''); ?>" />
            <cite>
                <span role="button" id="primary-pharmacy" title="<?php echo $titleMessage ?>"><?php echo text($primary_pharmacy); ?></span>
            </cite>
            <select id="select-primary" class="d-none">
                <option value=""><?php echo xlt("Select for No Pharmacy or Click for a list"); ?></option>
                <?php foreach ($pharmacies as $pharmacy) {
                    if (empty($pharmacy['ncpdp_safe'] ?? '')) {
                        continue;
                    }
                    $primary = ($pharmacy['business_name'] ?? false) ? ($pharmacy['business_name'] . ' - ' . ($pharmacy['address_line_1'] ?? '') . ' ' . ($pharmacy['city'] ?? '') . ', ' . ($pharmacy['state'] ?? '')) : '';
                    $isSelected = ($pharmacy['ncpdp_safe'] == $prim_pharmacy['ncpdp_safe']) ? 'selected' : '';
                    ?>
                    <option value="<?php echo attr($pharmacy['ncpdp_safe']); ?>" <?php echo $isSelected; ?>><?php echo text($primary); ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="input-group">
            <label role="button" id="label-alternate" class="text-primary mb-1 mr-1" for="select-alternate" title="<?php echo $titleMessage ?>" data-toggle="popover" data-content="<?php echo $popoverContent ?>">
                <b><?php echo xlt("Assigned Alternate"); ?>:</b>
            </label>
            <input type="hidden" id="alt_ncpdp" name="alt_ncpdp" value="<?php echo attr($alt_pharmacy['ncpdp_safe'] ?? ''); ?>" />
            <cite>
                <span role="button" id="alternate-pharmacy" title="<?php echo $titleMessage ?>"><?php echo text($alternate_pharmacy); ?></span>
            </cite>
            <select id="select-alternate" class="d-none">
                <option value=""><?php echo xlt("Select for No Pharmacy or Click for a list"); ?></option>
                <?php foreach ($pharmacies as $pharmacy) {
                    if (empty($pharmacy['ncpdp_safe'] ?? '')) {
                        continue;
                    }
                    $alternate = ($pharmacy['business_name'] ?? false) ? ($pharmacy['business_name'] . ' - ' . ($pharmacy['address_line_1'] ?? '') . ' ' . ($pharmacy['city'] ?? '') . ', ' . ($pharmacy['state'] ?? '')) : '';
                    $isSelected = ($pharmacy['ncpdp_safe'] == $alt_pharmacy['ncpdp_safe']) ? 'selected' : '';
                    ?>
                    <option value="<?php echo attr($pharmacy['ncpdp_safe']); ?>" <?php echo $isSelected; ?>><?php echo text($alternate); ?></option>
                <?php } ?>
            </select>
        </div>
        <script>
            $(document).ready(function () {
                $('[data-toggle="popover"]').popover({
                    trigger: 'hover',
                    placement: 'top'
                });
            });
        </script>
    </div>
<?php } ?>
<script>

    function refreshDemographics() {
        top.restoreSession();
        window.location.href = './demographics.php';
    }

    document.addEventListener("DOMContentLoaded", function () {
        const csrfToken = document.getElementById('csrf_token_form').value;
        const pid = <?php echo $pid; ?>;
        const url = `${top.webroot_url}/interface/modules/custom_modules/oe-module-weno/scripts/update_pharmacy.php`;

        // Function to handle label click
        function handleLabelClick(span, select) {
            span.classList.add("d-none");
            select.classList.remove("d-none");
            select.focus();
        }

        // Function to handle select change and auto-save
        async function handleSelectChange(span, select, input) {
            const selectedOption = select.options[select.selectedIndex].text;
            const selectedValue = select.value;

            span.textContent = selectedOption;
            span.classList.remove("d-none");
            select.classList.add("d-none");
            input.value = selectedValue;
            top.restoreSession();
            // Auto-save the selected option
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        csrf_token_form: csrfToken,
                        pid: pid,
                        primary: document.getElementById('prim_ncpdp').value,
                        alternate: document.getElementById('alt_ncpdp').value
                    })
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const result = await response.json();
                top.restoreSession();
                console.log('Save successful:', result);
                if (!document.getElementById('prim_ncpdp').value) {
                    document.getElementById('widget-button-set').classList.add('d-none');
                } else {
                    document.getElementById('widget-button-set').classList.remove('d-none');
                }
            } catch (error) {
                console.error('Error saving selection:', error);
            }
        }

        // Function to handle select blur (losing focus) to restore the span
        function handleSelectBlur(span, select) {
            span.classList.remove("d-none");
            select.classList.add("d-none");
        }

        function addEventListeners(labelId, spanId, selectId, inputId) {
            const label = document.getElementById(labelId);
            const span = document.getElementById(spanId);
            const select = document.getElementById(selectId);
            const input = document.getElementById(inputId);

            label.addEventListener("click", function () {
                handleLabelClick(span, select);
            });
            span.addEventListener("click", function () {
                handleLabelClick(span, select);
            });
            select.addEventListener("change", function () {
                handleSelectChange(span, select, input);
            });
            select.addEventListener("blur", function () {
                handleSelectBlur(span, select);
            });
        }

        addEventListeners("label-primary", "primary-pharmacy", "select-primary", "prim_ncpdp");
        addEventListeners("label-alternate", "alternate-pharmacy", "select-alternate", "alt_ncpdp");
    });
</script>

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
