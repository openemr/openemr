<?php

/**
 *
 * @package OpenEMR
 * @link    https://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

    require_once "../../../../globals.php";

    use OpenEMR\Common\Acl\AccessDeniedHelper;
    use OpenEMR\Common\Acl\AclMain;
    use OpenEMR\Core\Header;
    use OpenEMR\Modules\ClaimRevConnector\AppointmentsPage;
    use OpenEMR\Modules\ClaimRevConnector\CsrfHelper;
    use OpenEMR\Modules\ClaimRevConnector\EligibilityData;
    use OpenEMR\Modules\ClaimRevConnector\ModuleInput;

    $tab = "appointments";

//ensure user has proper access
if (!AclMain::aclCheckCore('acct', 'bill')) {
    AccessDeniedHelper::denyWithTemplate("ACL check failed for acct/bill: ClaimRev Connect - Appointments", xl("ClaimRev Connect - Appointments"));
}

$csrfToken = CsrfHelper::collectCsrfToken('eligibility');

// Both queue actions perform persistent side effects (delete + insert eligibility
// rows) so a CSRF token is required before either runs. Mirrors
// public/appointment_check_now.php.
if (ModuleInput::postExists('runBulkEligibility') || ModuleInput::postExists('runEligibility')) {
    if (!CsrfHelper::verifyCsrfToken(ModuleInput::postString('csrf_token'), 'eligibility')) {
        http_response_code(403);
        exit(xlt('Invalid CSRF token'));
    }
}

// Handle bulk eligibility queue (Run & Go) — read raw $_POST['eids'] array via filter_input_array.
$bulkEids = filter_input(INPUT_POST, 'eids', FILTER_UNSAFE_RAW, FILTER_REQUIRE_ARRAY);
if (ModuleInput::postExists('runBulkEligibility') && is_array($bulkEids)) {
    foreach ($bulkEids as $eid) {
        if (is_string($eid) && $eid !== '') {
            AppointmentsPage::runEligibilityForAppointment($eid);
        }
    }
}

// Handle single appointment queue (fallback, non-JS)
if (ModuleInput::postExists('runEligibility')) {
    $singleEid = ModuleInput::postString('eid');
    if ($singleEid !== '') {
        AppointmentsPage::runEligibilityForAppointment($singleEid);
    }
}

// Default date range: today through 7 days out
$defaultStart = date('Y-m-d');
$defaultEnd = date('Y-m-d', strtotime('+7 days'));
$startDateRaw = ModuleInput::postString('startDate');
$endDateRaw = ModuleInput::postString('endDate');
$startDate = $startDateRaw !== '' ? $startDateRaw : $defaultStart;
$endDate = $endDateRaw !== '' ? $endDateRaw : $defaultEnd;
$facilityId = ModuleInput::postString('facilityId');
$providerId = ModuleInput::postString('providerId');
$eligFilter = ModuleInput::postString('eligFilter', 'all');

$appointments = AppointmentsPage::getUpcomingAppointments($startDate, $endDate, $facilityId, $providerId, $eligFilter);
$facilities = AppointmentsPage::getFacilities();
$providers = AppointmentsPage::getProviders();

$path = str_replace("public", "templates", __DIR__);
?>

<html>
    <head>
        <title><?php echo xlt("ClaimRev Connect - Appointments"); ?></title>
        <?php Header::setupHeader(); ?>
        <style>
            .elig-active { color: green; font-weight: bold; }
            .elig-inactive { color: red; font-weight: bold; }
            .elig-waiting { color: orange; font-weight: bold; }
            .elig-unknown { color: grey; }
            .btn-check-elig { padding: 2px 8px; font-size: 0.85em; }
        </style>
    </head>
    <body class="body_top">
        <div class="container-fluid">
            <?php require '../templates/navbar.php'; ?>

            <form method="post" action="appointments.php">
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label for="startDate"><?php echo xlt("Start Date") ?></label>
                                    <input type="date" class="form-control" id="startDate" name="startDate" value="<?php echo attr($startDate); ?>"/>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label for="endDate"><?php echo xlt("End Date"); ?></label>
                                    <input type="date" class="form-control" id="endDate" name="endDate" value="<?php echo attr($endDate); ?>"/>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label for="facilityId"><?php echo xlt("Facility"); ?></label>
                                    <select class="form-control" id="facilityId" name="facilityId">
                                        <option value=""><?php echo xlt("All Facilities"); ?></option>
                                        <?php
                                        foreach ($facilities as $fac) {
                                            $selected = ($facilityId == $fac['id']) ? 'selected' : '';
                                            ?>
                                            <option value="<?php echo attr((string) $fac['id']); ?>" <?php echo $selected; ?>><?php echo text($fac['name']); ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label for="providerId"><?php echo xlt("Provider"); ?></label>
                                    <select class="form-control" id="providerId" name="providerId">
                                        <option value=""><?php echo xlt("All Providers"); ?></option>
                                        <?php
                                        foreach ($providers as $prov) {
                                            $selected = ($providerId == $prov['id']) ? 'selected' : '';
                                            ?>
                                            <option value="<?php echo attr((string) $prov['id']); ?>" <?php echo $selected; ?>><?php echo text($prov['provider_name']); ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label for="eligFilter"><?php echo xlt("Eligibility Status"); ?></label>
                                    <select class="form-control" id="eligFilter" name="eligFilter">
                                        <option value="all" <?php echo ($eligFilter == 'all') ? 'selected' : ''; ?>><?php echo xlt("All"); ?></option>
                                        <option value="needs_attention" <?php echo ($eligFilter == 'needs_attention') ? 'selected' : ''; ?>><?php echo xlt("Needs Attention"); ?></option>
                                        <option value="active_coverage" <?php echo ($eligFilter == 'active_coverage') ? 'selected' : ''; ?>><?php echo xlt("Active Coverage"); ?></option>
                                        <option value="not_checked" <?php echo ($eligFilter == 'not_checked') ? 'selected' : ''; ?>><?php echo xlt("Not Checked"); ?></option>
                                        <option value="stale" <?php echo ($eligFilter == 'stale') ? 'selected' : ''; ?>><?php echo xlt("Stale"); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <button type="submit" name="SubmitButton" class="btn btn-primary"><?php echo xlt("Search") ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        <?php
        $appointmentRows = [];
        foreach ($appointments as $row) {
            $appointmentRows[] = $row;
        }

        if ($appointmentRows === []) {
            echo "<div class='mt-3'>" . xlt("No appointments found for the selected date range.") . "</div>";
        } else {
            ?>
            <form method="post" action="appointments.php" id="bulkForm">
                <input type="hidden" name="csrf_token" value="<?php echo attr($csrfToken); ?>"/>
                <input type="hidden" name="startDate" value="<?php echo attr($startDate); ?>"/>
                <input type="hidden" name="endDate" value="<?php echo attr($endDate); ?>"/>
                <input type="hidden" name="facilityId" value="<?php echo attr($facilityId); ?>"/>
                <input type="hidden" name="providerId" value="<?php echo attr($providerId); ?>"/>
                <input type="hidden" name="eligFilter" value="<?php echo attr($eligFilter); ?>"/>

                <div class="row mt-2 mb-2">
                    <div class="col">
                        <button type="submit" name="runBulkEligibility" class="btn btn-outline-success" onclick="return selectAllCheckboxes();">
                            <i class="fa fa-list"></i> <?php echo xlt("Queue Selected") ?>
                        </button>
                        <button type="button" class="btn btn-success ml-2" onclick="checkNowSelected();">
                            <span class="spinner-border spinner-border-sm d-none" id="bulk-spinner" role="status"></span>
                            <i class="fa fa-bolt"></i> <?php echo xlt("Check Now Selected") ?>
                        </button>
                    </div>
                </div>

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col"><input type="checkbox" id="selectAll" onclick="toggleAll(this);"/></th>
                            <th scope="col"><?php echo xlt("Date") ?></th>
                            <th scope="col"><?php echo xlt("Time") ?></th>
                            <th scope="col"><?php echo xlt("Patient") ?></th>
                            <th scope="col"><?php echo xlt("DOB") ?></th>
                            <th scope="col"><?php echo xlt("Provider") ?></th>
                            <th scope="col"><?php echo xlt("Facility") ?></th>
                            <th scope="col"><?php echo xlt("Eligibility Status") ?></th>
                            <th scope="col"><?php echo xlt("Last Checked") ?></th>
                            <th scope="col"><?php echo xlt("Actions") ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($appointmentRows as $appt) {
                            $eligStatus = $appt['elig_status'];
                            $eligLastChecked = $appt['elig_last_checked'];
                            $eligMessage = $appt['elig_response_message'];
                            $eligIndividualJson = $appt['elig_individual_json'];

                            // Determine display status
                            $statusClass = 'elig-unknown';
                            $statusText = xlt("Not Checked");

                            if ($eligStatus != null) {
                                if (strtolower($eligStatus) == 'success') {
                                    $summaries = AppointmentsPage::getEligibilitySummary($eligIndividualJson);
                                    if ($summaries !== null && $summaries !== []) {
                                        $coverageStatus = \OpenEMR\Modules\ClaimRevConnector\TypeCoerce::asString($summaries[0]->status);
                                        if ($coverageStatus === 'Active Coverage') {
                                            $statusClass = 'elig-active';
                                            $statusText = xlt("Active Coverage");
                                        } else {
                                            $statusClass = 'elig-inactive';
                                            $statusText = text($coverageStatus);
                                        }
                                    } else {
                                        $statusClass = 'elig-active';
                                        $statusText = xlt("Complete");
                                    }
                                } elseif (strtolower($eligStatus) == 'waiting' || strtolower($eligStatus) == 'creating') {
                                    $statusClass = 'elig-waiting';
                                    $statusText = xlt("Pending");
                                } elseif (strtolower($eligStatus) == 'senderror' || strtolower($eligStatus) == 'error') {
                                    $statusClass = 'elig-inactive';
                                    $statusText = xlt("Error");
                                } elseif (strtolower($eligStatus) == 'retry' || strtolower($eligStatus) == 'send_retry') {
                                    $statusClass = 'elig-waiting';
                                    $statusText = xlt("Retrying");
                                } else {
                                    $statusText = text($eligStatus);
                                }
                            }

                            $payerName = '';
                            if ($eligIndividualJson !== null) {
                                $summaries = AppointmentsPage::getEligibilitySummary($eligIndividualJson);
                                if ($summaries !== null && $summaries !== []) {
                                    $payerName = \OpenEMR\Modules\ClaimRevConnector\TypeCoerce::asString($summaries[0]->payerName);
                                }
                            }
                            ?>
                            <tr id="appt-row-<?php echo attr((string) $appt['pc_eid']); ?>">
                                <td>
                                    <input type="checkbox" name="eids[]" value="<?php echo attr((string) $appt['pc_eid']); ?>" class="appt-checkbox"/>
                                </td>
                                <td><?php echo text($appt['appointmentDate']); ?></td>
                                <td><?php echo text(substr($appt['pc_startTime'], 0, 5)); ?></td>
                                <td><?php echo text($appt['lname']); ?>, <?php echo text($appt['fname']); ?></td>
                                <td><?php echo text($appt['dob']); ?></td>
                                <td><?php echo text($appt['provider_name']); ?></td>
                                <td><?php echo text($appt['facility_name']); ?></td>
                                <td id="status-<?php echo attr((string) $appt['pc_eid']); ?>">
                                    <span class="<?php echo attr($statusClass); ?>"><?php echo $statusText; ?></span>
                                    <?php if ($payerName != '') { ?>
                                        <br/><small><?php echo text($payerName); ?></small>
                                    <?php } ?>
                                    <?php if (($eligMessage ?? '') !== '') { ?>
                                        <br/><small class="text-muted"><?php echo text((string) $eligMessage); ?></small>
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php
                                    if ($eligLastChecked != null) {
                                        echo text($eligLastChecked);
                                    } else {
                                        echo "<span class='text-muted'>" . xlt("Never") . "</span>";
                                    }
                                    ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-primary btn-check-elig" onclick="checkNowAppointment(<?php echo attr_js((string) $appt['pc_eid']); ?>, this);">
                                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                        <?php echo xlt("Check Now"); ?>
                                    </button>
                                    <button type="submit" name="runEligibility" class="btn btn-outline-secondary btn-check-elig" onclick="document.getElementById('eid').value='<?php echo attr((string) $appt['pc_eid']); ?>';">
                                        <?php echo xlt("Queue"); ?>
                                    </button>
                                </td>
                            </tr>
                            <?php
                            // If we have eligibility results, show expandable detail row
                            if ($eligIndividualJson != null && $eligStatus == 'SUCCESS') {
                                ?>
                            <tr class="elig-detail-row">
                                <td colspan="10">
                                    <div class="ml-4 mr-4">
                                        <?php
                                        $pid = $appt['pc_pid'];
                                        $insurance = EligibilityData::getInsuranceData($pid);
                                        foreach ($insurance as $insRow) {
                                            $eligibilityCheck = EligibilityData::getEligibilityResult($pid, $insRow['payer_responsibility']);
                                            foreach ($eligibilityCheck as $check) {
                                                if ($check["eligibility_json"] == null) {
                                                    continue;
                                                }

                                                $individualJson = $check["individual_json"] ?? '';
                                                $individual = json_decode((string) $individualJson);
                                                if (!is_object($individual) || !property_exists($individual, 'eligibility') || !is_iterable($individual->eligibility)) {
                                                    continue;
                                                }

                                                $results = $individual->eligibility;
                                                foreach ($results as $eligibilityData) {
                                                    $data = null;
                                                    if (is_object($eligibilityData) && property_exists($eligibilityData, 'mapped271')) {
                                                        $data = $eligibilityData->mapped271;
                                                    }
                                                    if ($data == null) {
                                                        continue;
                                                    }
                                                    ?>
                                                    <div class="row mb-1">
                                                        <div class="col">
                                                            <?php
                                                            include $path . '/quick_info.php';
                                                            ?>
                                                        </div>
                                                    </div>
                                                    <?php
                                                }
                                            }
                                        }
                                        ?>
                                    </div>
                                </td>
                            </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
                <input type="hidden" id="eid" name="eid" value=""/>
            </form>
        <?php } ?>

        <script>
            function toggleAll(source) {
                var checkboxes = document.querySelectorAll('.appt-checkbox');
                for (var i = 0; i < checkboxes.length; i++) {
                    checkboxes[i].checked = source.checked;
                }
            }

            function selectAllCheckboxes() {
                var checkboxes = document.querySelectorAll('.appt-checkbox:checked');
                if (checkboxes.length === 0) {
                    var allCheckboxes = document.querySelectorAll('.appt-checkbox');
                    for (var i = 0; i < allCheckboxes.length; i++) {
                        allCheckboxes[i].checked = true;
                    }
                }
                return true;
            }

            function checkNowAppointment(eid, btn) {
                var spinner = btn.querySelector('.spinner-border');
                if (spinner) spinner.classList.remove('d-none');
                btn.disabled = true;

                $.ajax({
                    url: 'appointment_check_now.php',
                    type: 'POST',
                    data: { eid: eid, csrf_token: <?php echo js_escape($csrfToken); ?> },
                    dataType: 'json',
                    success: function(response) {
                        if (spinner) spinner.classList.add('d-none');
                        btn.disabled = false;
                        var statusCell = document.getElementById('status-' + eid);
                        if (response.success) {
                            var coverageStatus = response.coverageStatus || response.message || <?php echo xlj("Complete"); ?>;
                            var statusClass = 'elig-active';
                            if (coverageStatus.toLowerCase().indexOf('active') !== -1) {
                                statusClass = 'elig-active';
                            } else if (coverageStatus.toLowerCase().indexOf('inactive') !== -1 || coverageStatus.toLowerCase().indexOf('not found') !== -1) {
                                statusClass = 'elig-inactive';
                            } else {
                                statusClass = 'elig-unknown';
                            }
                            if (statusCell) {
                                var html = '<span class="' + statusClass + '">' + $('<span>').text(coverageStatus).html() + '</span>';
                                if (response.payerName) {
                                    html += '<br/><small>' + $('<span>').text(response.payerName).html() + '</small>';
                                }
                                statusCell.innerHTML = html;
                            }
                        } else {
                            if (statusCell) {
                                statusCell.innerHTML = '<span class="elig-inactive">' + <?php echo xlj("Error"); ?> + '</span>' +
                                    '<br/><small class="text-muted">' + $('<span>').text(response.message || '').html() + '</small>';
                            }
                        }
                    },
                    error: function() {
                        if (spinner) spinner.classList.add('d-none');
                        btn.disabled = false;
                        alert(<?php echo xlj("Error communicating with server"); ?>);
                    }
                });
            }

            function checkNowSelected() {
                var checkboxes = document.querySelectorAll('.appt-checkbox:checked');
                if (checkboxes.length === 0) {
                    // Select all if none selected
                    checkboxes = document.querySelectorAll('.appt-checkbox');
                    for (var i = 0; i < checkboxes.length; i++) {
                        checkboxes[i].checked = true;
                    }
                    checkboxes = document.querySelectorAll('.appt-checkbox:checked');
                }

                var spinner = document.getElementById('bulk-spinner');
                if (spinner) spinner.classList.remove('d-none');

                var eids = [];
                checkboxes.forEach(function(cb) {
                    eids.push(cb.value);
                });

                var completed = 0;
                var total = eids.length;

                eids.forEach(function(eid) {
                    // Find the Check Now button for this row and show its spinner
                    var row = document.getElementById('appt-row-' + eid);
                    var rowBtn = row ? row.querySelector('.btn-primary.btn-check-elig') : null;
                    var rowSpinner = rowBtn ? rowBtn.querySelector('.spinner-border') : null;
                    if (rowSpinner) rowSpinner.classList.remove('d-none');
                    if (rowBtn) rowBtn.disabled = true;

                    $.ajax({
                        url: 'appointment_check_now.php',
                        type: 'POST',
                        data: { eid: eid, csrf_token: <?php echo js_escape($csrfToken); ?> },
                        dataType: 'json',
                        success: function(response) {
                            if (rowSpinner) rowSpinner.classList.add('d-none');
                            if (rowBtn) rowBtn.disabled = false;

                            var statusCell = document.getElementById('status-' + eid);
                            if (response.success) {
                                var coverageStatus = response.coverageStatus || response.message || <?php echo xlj("Complete"); ?>;
                                var statusClass = 'elig-active';
                                if (coverageStatus.toLowerCase().indexOf('active') !== -1) {
                                    statusClass = 'elig-active';
                                } else if (coverageStatus.toLowerCase().indexOf('inactive') !== -1 || coverageStatus.toLowerCase().indexOf('not found') !== -1) {
                                    statusClass = 'elig-inactive';
                                } else {
                                    statusClass = 'elig-unknown';
                                }
                                if (statusCell) {
                                    var html = '<span class="' + statusClass + '">' + $('<span>').text(coverageStatus).html() + '</span>';
                                    if (response.payerName) {
                                        html += '<br/><small>' + $('<span>').text(response.payerName).html() + '</small>';
                                    }
                                    statusCell.innerHTML = html;
                                }
                            } else {
                                if (statusCell) {
                                    statusCell.innerHTML = '<span class="elig-inactive">' + <?php echo xlj("Error"); ?> + '</span>';
                                }
                            }

                            completed++;
                            if (completed >= total) {
                                if (spinner) spinner.classList.add('d-none');
                            }
                        },
                        error: function() {
                            if (rowSpinner) rowSpinner.classList.add('d-none');
                            if (rowBtn) rowBtn.disabled = false;
                            completed++;
                            if (completed >= total) {
                                if (spinner) spinner.classList.add('d-none');
                            }
                        }
                    });
                });
            }
        </script>
        </div>
    </body>
</html>
