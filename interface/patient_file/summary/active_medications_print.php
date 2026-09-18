<?php

/**
 * Printable medication list for the current patient.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once("../../globals.php");

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Services\ActiveMedicationListService;

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$pid = ActiveMedicationListService::requestedPatientId(
    CurrentRequest::get()->query->get('pid'),
    $session->get('pid', 0)
);

if ($pid < 1) {
    AccessDeniedHelper::deny('No patient selected');
}

if (!AclMain::aclCheckCore('patients', 'med')) {
    AccessDeniedHelper::deny('Unauthorized access to medication list');
}

$prow = getPatientData($pid, "squad, title, fname, mname, lname");
$squad = is_string($prow['squad'] ?? null) ? $prow['squad'] : '';
if ($squad !== '' && !AclMain::aclCheckCore('squads', $squad)) {
    AccessDeniedHelper::deny('Not authorized for squad: ' . $squad);
}

$nameParts = [];
foreach (['title', 'fname', 'mname', 'lname'] as $nameCol) {
    $part = $prow[$nameCol] ?? '';
    if (is_string($part) && trim($part) !== '') {
        $nameParts[] = trim($part);
    }
}
$ptname = implode(' ', $nameParts);
$svc = new ActiveMedicationListService();
$active = $svc->getActiveList($pid);
$inactive = $svc->getInactiveList($pid, $active);
$formatMedDate = static function (?string $d): string {
    if ($d === null) {
        return '';
    }
    $formatted = oeFormatShortDate($d);
    return is_string($formatted) ? $formatted : '';
};
?>
<html>
<head>
<?php Header::setupHeader(); ?>
<title><?php echo xlt('Medications'); ?></title>
</head>
<body class="body_top">
<p><b><?php echo xlt('Medications'); ?></b>
    <?php echo ' ' . xlt('for') . ' ' . text($ptname); ?></p>

<p><b><?php echo xlt('Active'); ?></b></p>
<?php if ($active === []) { ?>
<p><?php echo xlt('None{{Issues}}'); ?></p>
<?php } else { ?>
<table class="table table-bordered table-sm">
    <tr>
        <th><?php echo xlt('Medication'); ?></th>
        <th><?php echo xlt('Dose'); ?></th>
        <th><?php echo xlt('Start'); ?></th>
        <th><?php echo xlt('End'); ?></th>
        <th><?php echo xlt('Comments'); ?></th>
    </tr>
    <?php foreach ($active as $row) { ?>
    <tr>
        <td><?php echo text($row['title']); ?></td>
        <td><?php echo text($row['dose']); ?></td>
        <td><?php echo text($formatMedDate($row['start'])); ?></td>
        <td><?php echo text($formatMedDate($row['end'])); ?></td>
        <td><?php echo text($row['comments']); ?></td>
    </tr>
    <?php } ?>
</table>
<?php } ?>

<p><b><?php echo xlt('Inactive'); ?></b></p>
<?php if ($inactive === []) { ?>
<p><?php echo xlt('None{{Issues}}'); ?></p>
<?php } else { ?>
<table class="table table-bordered table-sm">
    <tr>
        <th><?php echo xlt('Medication'); ?></th>
        <th><?php echo xlt('Dose'); ?></th>
        <th><?php echo xlt('Start'); ?></th>
        <th><?php echo xlt('End'); ?></th>
        <th><?php echo xlt('Comments'); ?></th>
    </tr>
    <?php foreach ($inactive as $row) { ?>
    <tr>
        <td><?php echo text($row['title']); ?></td>
        <td><?php echo text($row['dose']); ?></td>
        <td><?php echo text($formatMedDate($row['start'])); ?></td>
        <td><?php echo text($formatMedDate($row['end'])); ?></td>
        <td><?php echo text($row['comments']); ?></td>
    </tr>
    <?php } ?>
</table>
<?php } ?>

<script>
if (window.opener && window.opener.top && typeof window.opener.top.printLogPrint === "function") {
    window.opener.top.printLogPrint(window);
} else {
    window.print();
}
</script>
</body>
</html>
