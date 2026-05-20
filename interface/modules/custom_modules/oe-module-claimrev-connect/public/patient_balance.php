<?php

/**
 * Patient Balance Queue - surfaces encounters with outstanding patient responsibility.
 *
 * Shows encounters where insurance has responded (last_level_closed >= 1) and
 * there is a remaining patient balance. Provides ERA-derived PR breakdown,
 * statement tracking, and links to OpenEMR's statement tools.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once "../../../../globals.php";

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\ClaimRevConnector\CsrfHelper;
use OpenEMR\Modules\ClaimRevConnector\ModuleInput;
use OpenEMR\Modules\ClaimRevConnector\PatientBalanceService;

$tab = "patient_balance";

if (!AclMain::aclCheckCore('acct', 'bill')) {
    AccessDeniedHelper::denyWithTemplate(
        "ACL check failed for acct/bill: ClaimRev Connect - Patient Balance",
        xl("ClaimRev Connect - Patient Balance")
    );
}

$csrfToken = CsrfHelper::collectCsrfToken('patient_balance');
$webRoot = OEGlobalsBag::getInstance()->getString('webroot');

$dateStart = ModuleInput::postString('dateStart');
$dateEnd = ModuleInput::postString('dateEnd');
$patientName = ModuleInput::postString('patientName');
$payerName = ModuleInput::postString('payerName');
$minAmount = ModuleInput::postString('minAmount');
$stmtFilter = ModuleInput::postString('stmtFilter');
$searchFilters = [
    'dateStart' => $dateStart,
    'dateEnd' => $dateEnd,
    'patientName' => $patientName,
    'payerName' => $payerName,
    'minAmount' => $minAmount,
    'stmtFilter' => $stmtFilter,
    'pageIndex' => ModuleInput::postInt('pageIndex'),
];

$encounters = [];
$totalRecords = 0;
$pageIndex = ModuleInput::postInt('pageIndex');
$pageSize = 50;
$searched = false;
$stats = null;

if (ModuleInput::isPostRequest() && ModuleInput::postExists('SubmitButton')) {
    $searched = true;
    $result = PatientBalanceService::getPatientBalanceQueue($searchFilters);
    $encounters = $result['encounters'];
    $totalRecords = $result['totalRecords'];
    $stats = PatientBalanceService::getQueueStats($searchFilters);
}

$totalPages = ($totalRecords > 0) ? (int) ceil($totalRecords / $pageSize) : 0;
?>

<html>
    <head>
        <title><?php echo xlt("ClaimRev Connect - Patient Balance"); ?></title>
        <?php Header::setupHeader(); ?>
        <style>
            .bal-row { cursor: pointer; }
            .bal-row:hover { background-color: rgba(0,0,0,.05); }
            .bal-row.row-never-sent { background-color: #fff9e5; }
            .bal-row.row-in-collection { background-color: #ffe5e5; }
            .bal-detail-row { display: none; }
            .bal-detail-row.show { display: table-row; }
            .detail-label { font-weight: bold; color: #666; font-size: 0.85em; }
            .detail-value { font-size: 0.85em; }
            .summary-cards .card { min-width: 120px; }
            .summary-cards .card-body { padding: 10px; text-align: center; }
            .summary-cards h5 { margin: 0; }
            .summary-cards small { color: #666; }
            .pr-badge { font-size: 0.75em; padding: 2px 5px; }
            .stmt-history-table { font-size: 0.85em; }
            .stmt-history-table td, .stmt-history-table th { padding: 3px 6px; }
        </style>
    </head>
    <body class="body_top">
        <div class="container-fluid">
            <?php require '../templates/navbar.php'; ?>
            <form method="post" action="patient_balance.php" id="balSearchForm">
                <input type="hidden" name="pageIndex" id="pageIndex" value="<?php echo attr((string) $pageIndex); ?>"/>
                <div class="card mt-3">
                    <div class="card-header">
                        <?php echo xlt("Patient Balance Queue"); ?>
                    </div>
                    <div class="card-body pb-0">
                        <div class="form-row">
                            <div class="form-group col-md-2">
                                <label for="dateStart"><?php echo xlt("Service Date Start"); ?></label>
                                <input type="date" class="form-control form-control-sm" id="dateStart" name="dateStart" value="<?php echo attr($dateStart); ?>"/>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="dateEnd"><?php echo xlt("Service Date End"); ?></label>
                                <input type="date" class="form-control form-control-sm" id="dateEnd" name="dateEnd" value="<?php echo attr($dateEnd); ?>"/>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="patientName"><?php echo xlt("Patient Name"); ?></label>
                                <input type="text" class="form-control form-control-sm" id="patientName" name="patientName" value="<?php echo attr($patientName); ?>"/>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="payerName"><?php echo xlt("Payer"); ?></label>
                                <input type="text" class="form-control form-control-sm" id="payerName" name="payerName" value="<?php echo attr($payerName); ?>"/>
                            </div>
                            <div class="form-group col-md-1">
                                <label for="minAmount"><?php echo xlt("Min Amt"); ?></label>
                                <input type="number" step="0.01" class="form-control form-control-sm" id="minAmount" name="minAmount" value="<?php echo attr($minAmount); ?>" placeholder="0.01"/>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="stmtFilter"><?php echo xlt("Statement Status"); ?></label>
                                <select class="form-control form-control-sm" id="stmtFilter" name="stmtFilter">
                                    <option value="" <?php echo $stmtFilter === '' ? 'selected' : ''; ?>><?php echo xlt("All"); ?></option>
                                    <option value="never_sent" <?php echo $stmtFilter === 'never_sent' ? 'selected' : ''; ?>><?php echo xlt("Never Sent"); ?></option>
                                    <option value="sent_1x" <?php echo $stmtFilter === 'sent_1x' ? 'selected' : ''; ?>><?php echo xlt("Sent 1x"); ?></option>
                                    <option value="sent_2plus" <?php echo $stmtFilter === 'sent_2plus' ? 'selected' : ''; ?>><?php echo xlt("Sent 2+"); ?></option>
                                    <option value="in_collection" <?php echo $stmtFilter === 'in_collection' ? 'selected' : ''; ?>><?php echo xlt("In Collection"); ?></option>
                                </select>
                            </div>
                            <div class="form-group col-md-1 d-flex align-items-end">
                                <button type="submit" name="SubmitButton" class="btn btn-primary btn-sm btn-block"><?php echo xlt("Search"); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        <?php if ($searched && $encounters === []) { ?>
            <div class="mt-3"><?php echo xlt("No encounters with patient balances found."); ?></div>
        <?php } elseif ($encounters !== [] && $stats !== null) { ?>

            <!-- Summary cards -->
            <div class="d-flex summary-cards mt-3 mb-2" style="gap: 10px;">
                <div class="card">
                    <div class="card-body">
                        <h5><?php echo text((string) $stats['totalWithBalance']); ?></h5>
                        <small><?php echo xlt("Total w/ Balance"); ?></small>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h5 class="text-danger">$<?php echo text(number_format($stats['totalAmount'], 2)); ?></h5>
                        <small><?php echo xlt("Total Amount"); ?></small>
                    </div>
                </div>
                <div class="card border-warning">
                    <div class="card-body">
                        <h5 class="text-warning"><?php echo text((string) $stats['neverSent']); ?></h5>
                        <small><?php echo xlt("Never Sent"); ?></small>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h5><?php echo text((string) $stats['sent1x']); ?></h5>
                        <small><?php echo xlt("Sent 1x"); ?></small>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h5><?php echo text((string) $stats['sent2plus']); ?></h5>
                        <small><?php echo xlt("Sent 2+"); ?></small>
                    </div>
                </div>
                <div class="card border-danger">
                    <div class="card-body">
                        <h5 class="text-danger"><?php echo text((string) $stats['inCollection']); ?></h5>
                        <small><?php echo xlt("In Collection"); ?></small>
                    </div>
                </div>
                <?php if ($totalPages > 1) { ?>
                <div class="ml-auto d-flex align-items-center">
                    <small class="text-muted mr-2"><?php echo xlt("Page"); ?> <?php echo text((string) ($pageIndex + 1)); ?>/<?php echo text((string) $totalPages); ?></small>
                    <?php if ($pageIndex > 0) { ?>
                        <button type="button" class="btn btn-sm btn-outline-secondary mr-1" onclick="goToPage(<?php echo attr((string) ($pageIndex - 1)); ?>)">&laquo;</button>
                    <?php } ?>
                    <?php if ($pageIndex < $totalPages - 1) { ?>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="goToPage(<?php echo attr((string) ($pageIndex + 1)); ?>)">&raquo;</button>
                    <?php } ?>
                </div>
                <?php } ?>
            </div>

            <table class="table table-sm table-bordered mt-1">
                <thead class="thead-light">
                    <tr>
                        <th><?php echo xlt("Patient"); ?></th>
                        <th><?php echo xlt("Encounter"); ?></th>
                        <th><?php echo xlt("Service Date"); ?></th>
                        <th><?php echo xlt("Payer"); ?></th>
                        <th class="text-right"><?php echo xlt("Charges"); ?></th>
                        <th class="text-right"><?php echo xlt("Ins Paid"); ?></th>
                        <th class="text-right"><?php echo xlt("Patient Owes"); ?></th>
                        <th class="text-center"><?php echo xlt("Stmts"); ?></th>
                        <th><?php echo xlt("Last Statement"); ?></th>
                        <th><?php echo xlt("Actions"); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($encounters as $idx => $enc) {
                        $rowClass = 'bal-row';
                        if ($enc['inCollection']) {
                            $rowClass .= ' row-in-collection';
                        } elseif ($enc['stmtCount'] === 0) {
                            $rowClass .= ' row-never-sent';
                        }
                        ?>
                    <tr class="<?php echo attr($rowClass); ?>" onclick="toggleDetail(<?php echo attr((string) $idx); ?>, <?php echo attr((string) $enc['pid']); ?>, <?php echo attr((string) $enc['encounter']); ?>)">
                        <td>
                            <?php echo text($enc['patientName']); ?>
                            <br/><small class="text-muted"><?php echo text($enc['patientDob']); ?></small>
                        </td>
                        <td><?php echo text((string) $enc['encounter']); ?></td>
                        <td><?php echo text($enc['encounterDate']); ?></td>
                        <td>
                            <?php echo text($enc['payerName']); ?>
                            <?php if ($enc['payerNumber'] !== '') { ?>
                                <br/><small class="text-muted"><?php echo text($enc['payerNumber']); ?></small>
                            <?php } ?>
                        </td>
                        <td class="text-right"><?php echo text(number_format($enc['totalCharges'], 2)); ?></td>
                        <td class="text-right"><?php echo text(number_format($enc['insPaid'], 2)); ?></td>
                        <td class="text-right"><strong><?php echo text(number_format($enc['balance'], 2)); ?></strong></td>
                        <td class="text-center">
                            <?php if ($enc['inCollection']) { ?>
                                <span class="badge badge-danger"><?php echo xlt("Collections"); ?></span>
                            <?php } elseif ($enc['stmtCount'] === 0) { ?>
                                <span class="badge badge-warning"><?php echo xlt("Never Sent"); ?></span>
                            <?php } else { ?>
                                <span class="badge badge-info"><?php echo text((string) $enc['stmtCount']); ?></span>
                            <?php } ?>
                        </td>
                        <td><?php echo $enc['lastStmtDate'] !== '' ? text($enc['lastStmtDate']) : '<span class="text-muted">—</span>'; ?></td>
                        <td onclick="event.stopPropagation();">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary btn-sm" title="<?php echo xla("Generate Statement"); ?>"
                                    onclick="generateStatement(<?php echo attr((string) $enc['pid']); ?>, <?php echo attr((string) $enc['encounter']); ?>)">
                                    <i class="fa fa-file-invoice"></i>
                                </button>
                                <button type="button" class="btn btn-outline-success btn-sm mark-sent-btn" title="<?php echo xla("Mark Sent"); ?>"
                                    data-pid="<?php echo attr((string) $enc['pid']); ?>"
                                    data-encounter="<?php echo attr((string) $enc['encounter']); ?>"
                                    data-amount="<?php echo attr((string) $enc['balance']); ?>">
                                    <i class="fa fa-check"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm note-btn" title="<?php echo xla("Add Note"); ?>"
                                    data-pid="<?php echo attr((string) $enc['pid']); ?>"
                                    data-encounter="<?php echo attr((string) $enc['encounter']); ?>">
                                    <i class="fa fa-sticky-note"></i>
                                </button>
                                <button type="button" class="btn btn-outline-info btn-sm" title="<?php echo xla("View Ledger"); ?>"
                                    onclick="openLedger(<?php echo attr((string) $enc['pid']); ?>, <?php echo attr((string) $enc['encounter']); ?>)">
                                    <i class="fa fa-book"></i>
                                </button>
                                <button type="button" class="btn btn-outline-info btn-sm" title="<?php echo xla("Open Encounter"); ?>"
                                    onclick="openEncounterTab(<?php echo attr((string) $enc['pid']); ?>, <?php echo attr((string) $enc['encounter']); ?>)">
                                    <i class="fa fa-folder-open"></i>
                                </button>
                                <button type="button" class="btn btn-outline-light btn-sm text-muted" title="<?php echo xla("Generate via ClaimRev (Coming Soon)"); ?>" disabled>
                                    <i class="fa fa-cloud-upload-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr class="bal-detail-row" id="detail-<?php echo attr((string) $idx); ?>">
                        <td colspan="10" style="background-color: rgba(0,0,0,.02); padding: 15px 25px;">
                            <div id="detail-content-<?php echo attr((string) $idx); ?>">
                                <div class="text-center text-muted"><i class="fa fa-spinner fa-spin"></i> <?php echo xlt("Loading..."); ?></div>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>

            <?php if ($totalPages > 1) { ?>
                <div class="d-flex justify-content-center mb-3">
                    <?php if ($pageIndex > 0) { ?>
                        <button type="button" class="btn btn-sm btn-outline-secondary mr-2" onclick="goToPage(<?php echo attr((string) ($pageIndex - 1)); ?>)">&laquo; <?php echo xlt("Prev"); ?></button>
                    <?php } ?>
                    <?php if ($pageIndex < $totalPages - 1) { ?>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="goToPage(<?php echo attr((string) ($pageIndex + 1)); ?>)"><?php echo xlt("Next"); ?> &raquo;</button>
                    <?php } ?>
                </div>
            <?php } ?>
        <?php } ?>
        </div>

        <script>
            var csrfToken = <?php echo json_encode($csrfToken); ?>;
            var loadedDetails = {};

            function openEncounterTab(pid, encounter) {
                top.restoreSession();
                top.RTop.location = '<?php echo attr($webRoot); ?>/interface/patient_file/summary/demographics.php?set_pid=' + encodeURIComponent(pid) + '&set_encounterid=' + encodeURIComponent(encounter);
            }

            function openLedger(pid, encounter) {
                top.restoreSession();
                top.RTop.location = '<?php echo attr($webRoot); ?>/interface/billing/sl_eob_invoice.php?is498=1&pid=' + encodeURIComponent(pid) + '&encounter=' + encodeURIComponent(encounter);
            }

            function generateStatement(pid, encounter) {
                top.restoreSession();
                top.RTop.location = '<?php echo attr($webRoot); ?>/interface/billing/sl_eob_search.php';
            }

            function toggleDetail(idx, pid, encounter) {
                var row = document.getElementById('detail-' + idx);
                if (!row) return;

                if (row.classList.contains('show')) {
                    row.classList.remove('show');
                    return;
                }

                row.classList.add('show');

                if (loadedDetails[idx]) return;
                loadedDetails[idx] = true;

                $.post('patient_balance_api.php', {
                    csrf_token: csrfToken,
                    action: 'get_detail',
                    pid: pid,
                    encounter: encounter
                }, function(response) {
                    if (response.error) {
                        $('#detail-content-' + idx).html('<div class="text-danger">' + response.error + '</div>');
                        return;
                    }
                    renderDetail(idx, response);
                }, 'json').fail(function() {
                    $('#detail-content-' + idx).html('<div class="text-danger">' + <?php echo xlj("Failed to load details"); ?> + '</div>');
                    loadedDetails[idx] = false;
                });
            }

            function renderDetail(idx, data) {
                var detail = data.detail;
                var history = data.history;
                var pr = detail.prMemos;

                var html = '<div class="row">';

                // PR Breakdown
                html += '<div class="col-md-3">';
                html += '<div class="detail-label">' + <?php echo xlj("Patient Responsibility Breakdown"); ?> + '</div>';
                if (pr.deductible > 0 || pr.coinsurance > 0 || pr.copay > 0 || pr.ptresp > 0) {
                    if (pr.deductible > 0) html += '<div class="detail-value"><span class="badge badge-warning pr-badge">' + <?php echo xlj("Deductible"); ?> + '</span> $' + pr.deductible.toFixed(2) + '</div>';
                    if (pr.coinsurance > 0) html += '<div class="detail-value"><span class="badge badge-info pr-badge">' + <?php echo xlj("Coinsurance"); ?> + '</span> $' + pr.coinsurance.toFixed(2) + '</div>';
                    if (pr.copay > 0) html += '<div class="detail-value"><span class="badge badge-secondary pr-badge">' + <?php echo xlj("Copay"); ?> + '</span> $' + pr.copay.toFixed(2) + '</div>';
                    if (pr.ptresp > 0) html += '<div class="detail-value"><span class="badge badge-dark pr-badge">' + <?php echo xlj("Pt Resp"); ?> + '</span> $' + pr.ptresp.toFixed(2) + '</div>';
                } else {
                    html += '<div class="detail-value text-muted">' + <?php echo xlj("No PR memos found"); ?> + '</div>';
                }
                html += '</div>';

                // Per-code breakdown
                html += '<div class="col-md-5">';
                html += '<div class="detail-label">' + <?php echo xlj("Per-Code Breakdown"); ?> + '</div>';
                html += '<table class="table table-sm table-borderless mb-0" style="font-size:0.85em;">';
                html += '<tr><th>' + <?php echo xlj("Code"); ?> + '</th><th class="text-right">' + <?php echo xlj("Charge"); ?> + '</th><th class="text-right">' + <?php echo xlj("Adj"); ?> + '</th><th class="text-right">' + <?php echo xlj("Balance"); ?> + '</th></tr>';
                detail.codes.forEach(function(c) {
                    var balClass = c.balance > 0 ? 'text-danger font-weight-bold' : '';
                    html += '<tr>';
                    html += '<td>' + escHtml(c.code) + (c.codeText ? ' <small class="text-muted">' + escHtml(c.codeText) + '</small>' : '') + '</td>';
                    html += '<td class="text-right">' + c.charge.toFixed(2) + '</td>';
                    html += '<td class="text-right">' + c.adjustment.toFixed(2) + '</td>';
                    html += '<td class="text-right ' + balClass + '">' + c.balance.toFixed(2) + '</td>';
                    html += '</tr>';
                });
                html += '<tr class="border-top"><td><strong>' + <?php echo xlj("Total"); ?> + '</strong></td><td></td><td></td><td class="text-right text-danger font-weight-bold">' + detail.totalBalance.toFixed(2) + '</td></tr>';
                html += '</table>';
                html += '</div>';

                // Statement history
                html += '<div class="col-md-4">';
                html += '<div class="detail-label">' + <?php echo xlj("Statement History"); ?> + '</div>';
                if (history && history.length > 0) {
                    html += '<table class="table table-sm stmt-history-table mb-0">';
                    html += '<tr><th>' + <?php echo xlj("Date"); ?> + '</th><th>' + <?php echo xlj("Method"); ?> + '</th><th>' + <?php echo xlj("Amount"); ?> + '</th><th>' + <?php echo xlj("By"); ?> + '</th></tr>';
                    history.forEach(function(s) {
                        html += '<tr>';
                        html += '<td>' + escHtml(s.statement_date) + '</td>';
                        html += '<td>' + escHtml(s.statement_method) + '</td>';
                        html += '<td>$' + parseFloat(s.amount_due).toFixed(2) + '</td>';
                        html += '<td>' + escHtml(s.created_by) + '</td>';
                        html += '</tr>';
                        if (s.notes) {
                            html += '<tr><td colspan="4" class="text-muted pl-3"><small>' + escHtml(s.notes) + '</small></td></tr>';
                        }
                    });
                    html += '</table>';
                } else {
                    html += '<div class="detail-value text-muted">' + <?php echo xlj("No statements recorded"); ?> + '</div>';
                }
                html += '</div>';

                html += '</div>';

                $('#detail-content-' + idx).html(html);
            }

            function escHtml(str) {
                if (!str) return '';
                var div = document.createElement('div');
                div.appendChild(document.createTextNode(str));
                return div.innerHTML;
            }

            function goToPage(page) {
                document.getElementById('pageIndex').value = page;
                document.getElementById('balSearchForm').querySelector('[name="SubmitButton"]').click();
            }

            $(document).ready(function() {
                // Mark Sent
                $('.mark-sent-btn').on('click', function(e) {
                    e.stopPropagation();
                    var $btn = $(this);
                    if (!confirm(<?php echo xlj("Mark this statement as sent?"); ?>)) return;

                    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
                    $.post('patient_balance_api.php', {
                        csrf_token: csrfToken,
                        action: 'log_statement',
                        pid: $btn.data('pid'),
                        encounter: $btn.data('encounter'),
                        method: 'openemr_print',
                        amount: $btn.data('amount'),
                        notes: ''
                    }, function(response) {
                        if (response.success) {
                            $btn.replaceWith('<span class="text-success"><i class="fa fa-check"></i></span>');
                        } else {
                            alert(response.error || <?php echo xlj("Failed"); ?>);
                            $btn.prop('disabled', false).html('<i class="fa fa-check"></i>');
                        }
                    }, 'json').fail(function() {
                        alert(<?php echo xlj("Failed"); ?>);
                        $btn.prop('disabled', false).html('<i class="fa fa-check"></i>');
                    });
                });

                // Add Note
                $('.note-btn').on('click', function(e) {
                    e.stopPropagation();
                    var $btn = $(this);
                    var note = prompt(<?php echo xlj("Enter note:"); ?>);
                    if (!note) return;

                    $btn.prop('disabled', true);
                    $.post('patient_balance_api.php', {
                        csrf_token: csrfToken,
                        action: 'add_note',
                        pid: $btn.data('pid'),
                        encounter: $btn.data('encounter'),
                        notes: note
                    }, function(response) {
                        if (response.success) {
                            alert(<?php echo xlj("Note added"); ?>);
                        } else {
                            alert(response.error || <?php echo xlj("Failed"); ?>);
                        }
                        $btn.prop('disabled', false);
                    }, 'json').fail(function() {
                        alert(<?php echo xlj("Failed"); ?>);
                        $btn.prop('disabled', false);
                    });
                });
            });
        </script>
    </body>
</html>
