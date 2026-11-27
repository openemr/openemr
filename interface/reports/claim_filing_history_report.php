<?php

/**
 * Claim Filing History: what the X12 files show about a claim, for a timely filing appeal.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Steve Wiig <https://github.com/Steve-Wiig>
 * @copyright Copyright (c) 2026 Steve Wiig
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once \OpenEMR\Core\OEGlobalsBag::getInstance()->getSrcDir() . "/options.inc.php";

use OpenEMR\Billing\EdiHistory\ClaimFilingFacts;
use OpenEMR\Billing\EdiHistory\ClaimFilingStatements;
use OpenEMR\Billing\EdiHistory\EncounterEvent;
use OpenEMR\Billing\EdiHistory\EncounterHistoryRenderer;
use OpenEMR\Billing\EdiHistory\EncounterPostedLookup;
use OpenEMR\Billing\EdiHistory\TimelyFilingCandidates;
use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;

if (!AclMain::aclCheckCore('acct', 'rep_a')) {
    AccessDeniedHelper::denyWithTemplate("ACL check failed for acct/rep_a: Claim Filing History", xl("Claim Filing History"));
}

$session = SessionWrapperFactory::getInstance()->getActiveSession();
if (!empty($_POST)) {
    CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);
}

// The facts come from EDI History; this report is a view on them, not a second parser.
$srcDir = OEGlobalsBag::getInstance()->getSrcDir();
require_once($srcDir . '/edihistory/edih_csv_inc.php');
require_once($srcDir . '/edihistory/edih_csv_data.php');

$encounter = trim((string) ($_POST['form_encounter'] ?? ''));
$payer = trim((string) ($_POST['form_payer'] ?? ''));
$fromDate = isset($_POST['form_from_date']) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-m-d', strtotime('-1 year'));
$toDate = isset($_POST['form_to_date']) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');

$events = [];
$candidates = [];
$serviceDate = '';
$firstReceipt = '';
$numbers = [];
$pid = 0;
$ackRows = [];
$refRows = [];

if ($encounter !== '' && ctype_digit($encounter)) {
    $events = edih_encounter_events($encounter);
    try {
        $row = QueryUtils::querySingleRow('SELECT pid, `date` FROM form_encounter WHERE encounter = ? LIMIT 1', [$encounter]);
        if (is_array($row)) {
            $pid = (int) ($row['pid'] ?? 0);
            $serviceDate = substr((string) ($row['date'] ?? ''), 0, 10);
        }
        if (QueryUtils::existsTable('x12_claim_ack_277')) {
            $ackRows = QueryUtils::fetchRecords(
                'SELECT source_kind, source_received_date FROM `x12_claim_ack_277` WHERE encounter = ?',
                [$encounter]
            );
            $refRows = QueryUtils::fetchRecords(
                'SELECT r.ref_value FROM `x12_claim_ack_277` a'
                . ' JOIN `x12_claim_ref_277` r ON r.ack_id = a.id'
                . ' WHERE r.qualifier = ? AND a.encounter = ?',
                ['1K', $encounter]
            );
        }
    } catch (\RuntimeException) {
        // The history still renders from what the CSV tables hold.
    }
    $remit = csv_file_by_enctr($encounter, 'f835');
    $numbers = ClaimFilingFacts::payerClaimNumbers(
        $refRows,
        is_array($remit) ? $remit : [],
        EncounterPostedLookup::forEncounter((int) $encounter, $pid)
    );
    $firstReceipt = ClaimFilingFacts::firstPayerReceipt($ackRows);
} else {
    $encounter = '';
    $fileRows = csv_assoc_array('f835', 'file');
    $claimRows = csv_assoc_array('f835', 'claim');
    $fileRows = is_array($fileRows) ? $fileRows : [];
    $claimRows = is_array($claimRows) ? $claimRows : [];
    // Only the remittances in the range are read; a year of files is not parsed to show one page.
    $inRange = [];
    foreach ($fileRows as $f) {
        $d = (string) ($f['Date'] ?? '');
        $d = strlen($d) === 8 ? substr($d, 0, 4) . '-' . substr($d, 4, 2) . '-' . substr($d, 6, 2) : '';
        if ($d !== '' && (($fromDate !== '' && $d < $fromDate) || ($toDate !== '' && $d > $toDate))) {
            continue;
        }
        $inRange[(string) ($f['FileName'] ?? '')] = true;
    }
    $keep = [];
    foreach ($claimRows as $c) {
        if (isset($inRange[(string) ($c['FileName'] ?? '')])) {
            $keep[] = $c;
        }
    }
    $p835 = csv_parameters('f835');
    $found = array_merge(
        TimelyFilingCandidates::fromPostings($fromDate, $toDate, $payer),
        TimelyFilingCandidates::fromRemittances($keep, $fileRows, (string) ($p835['directory'] ?? ''))
    );
    $seen = [];
    foreach ($found as $c) {
        if ($payer !== '' && stripos($c['payer'], $payer) === false) {
            continue;
        }
        if ($c['date'] !== '' && (($fromDate !== '' && $c['date'] < $fromDate) || ($toDate !== '' && $c['date'] > $toDate))) {
            continue;
        }
        $key = $c['pid'] . '-' . $c['encounter'];
        if (isset($seen[$key])) {
            continue;
        }
        $seen[$key] = true;
        $candidates[] = $c;
    }
}

$days = ClaimFilingStatements::daysFromService($serviceDate, $events);
$statements = ClaimFilingStatements::statements($events);
$firstDays = ClaimFilingStatements::daysFromService(
    $serviceDate,
    [new EncounterEvent($firstReceipt, 'status', 'first payer receipt')]
);
$csrf = CsrfUtils::collectCsrfToken(session: $session);
?>
<html>
<head>
<title><?php echo xlt('Claim Filing History'); ?></title>
<?php Header::setupHeader('datetime-picker'); ?>
<style>
@media print { .no-print { display: none !important; } }
.stmt { width: 100%; height: 14em; font-family: monospace; }
</style>
<script>
$(function () {
    <?php require(OEGlobalsBag::getInstance()->getSrcDir() . "/restoreSession.php"); ?>
    var win = top.printLogSetup ? top : opener.top;
    win.printLogSetup(document.getElementById('printbutton'));
    $('.datepicker').datetimepicker({
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = true; ?>
        <?php require(OEGlobalsBag::getInstance()->getSrcDir() . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
    });
});
function pick(enc) {
    document.getElementById('form_encounter').value = enc;
    document.getElementById('reportform').submit();
}
</script>
</head>
<body class="body_top">
<div class="container-fluid">
<h2><?php echo xlt('Claim Filing History'); ?></h2>
<form method="post" id="reportform" action="claim_filing_history_report.php">
<input type="hidden" name="csrf_token_form" value="<?php echo attr($csrf); ?>" />
<div class="form-row no-print">
    <div class="col">
        <label><?php echo xlt('Encounter'); ?></label>
        <input type="text" class="form-control" id="form_encounter" name="form_encounter" value="<?php echo attr($encounter); ?>" />
    </div>
    <div class="col">
        <label><?php echo xlt('Payer contains'); ?></label>
        <input type="text" class="form-control" name="form_payer" value="<?php echo attr($payer); ?>" />
    </div>
    <div class="col">
        <label><?php echo xlt('From'); ?></label>
        <input type="text" class="form-control datepicker" name="form_from_date" value="<?php echo attr($fromDate); ?>" />
    </div>
    <div class="col">
        <label><?php echo xlt('To'); ?></label>
        <input type="text" class="form-control datepicker" name="form_to_date" value="<?php echo attr($toDate); ?>" />
    </div>
    <div class="col align-self-end">
        <button type="submit" class="btn btn-primary btn-sm"><?php echo xlt('Search'); ?></button>
        <input type="button" class="btn btn-secondary btn-sm" id="printbutton" value="<?php echo xla('Print'); ?>" />
    </div>
</div>
</form>
<?php if ($encounter === '') { ?>
<h4><?php echo xlt('Encounters with a timely filing adjustment'); ?></h4>
<table class="table table-sm">
<thead><tr>
<th><?php echo xlt('Patient'); ?></th><th><?php echo xlt('Encounter'); ?></th>
<th><?php echo xlt('Payer'); ?></th><th><?php echo xlt('Date'); ?></th>
<th><?php echo xlt('Said by'); ?></th><th></th>
</tr></thead>
<tbody>
<?php if ($candidates === []) { ?>
<tr><td colspan="6"><?php echo xlt('Nothing found in this range.'); ?></td></tr>
<?php }
foreach ($candidates as $c) { ?>
<tr>
<td><?php echo text($c['pid']); ?></td>
<td><?php echo text($c['encounter']); ?></td>
<td><?php echo text($c['payer']); ?></td>
<td><?php echo text($c['date']); ?></td>
<td><?php echo text($c['source']); ?></td>
<td class="no-print"><button type="button" class="btn btn-link btn-sm" onclick="pick(<?php echo (int) $c['encounter']; ?>)"><?php echo xlt('Open'); ?></button></td>
</tr>
<?php } ?>
</tbody>
</table>
<?php } else { ?>
<h4><?php echo xlt('Encounter'); ?> <?php echo text($encounter); ?></h4>
<p>
<?php echo xlt('Date of service'); ?>: <?php echo text($serviceDate !== '' ? $serviceDate : xl('not on file')); ?><br />
<?php echo xlt('First payer receipt'); ?>: <?php echo text($firstReceipt !== '' ? $firstReceipt : xl('none on file')); ?>
<?php if ($firstDays !== []) {
    echo ' (' . text($firstDays[0]['days']) . ' ' . xlt('days from service') . ')';
} ?><br />
<?php echo xlt('Payer claim numbers'); ?>: <?php echo text($numbers === [] ? xl('none on file') : implode(', ', $numbers)); ?>
</p>
<?php echo EncounterHistoryRenderer::html($events, $encounter); ?>
<h4><?php echo xlt('Days from the date of service'); ?></h4>
<table class="table table-sm">
<thead><tr><th><?php echo xlt('Date'); ?></th><th><?php echo xlt('Days'); ?></th><th><?php echo xlt('Event'); ?></th></tr></thead>
<tbody>
<?php foreach ($days as $d) { ?>
<tr><td><?php echo text($d['date']); ?></td><td><?php echo text($d['days']); ?></td><td><?php echo text($d['title']); ?></td></tr>
<?php } ?>
</tbody>
</table>
<h4><?php echo xlt('Statements of fact'); ?></h4>
<p class="no-print"><?php echo xlt('Copy these into a reconsideration letter. They state what the files show.'); ?></p>
<textarea class="stmt" readonly><?php echo text(implode("\n", $statements)); ?></textarea>
<?php } ?>
</div>
</body>
</html>