<?php

/**
 * Printable medication list for the current patient.
 *
 * Active rows first, then a labeled Inactive / historical section.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   GNU General Public License 3
 */

require_once("../../globals.php");

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Services\ActiveMedicationListService;

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$pid = (int) $session->get('pid', 0);

if ($pid < 1) {
    AccessDeniedHelper::deny('No patient selected');
}

if (!AclMain::aclCheckCore('patients', 'med')) {
    AccessDeniedHelper::deny('Unauthorized access to medication list');
}

$prow = getPatientData($pid, "squad, title, fname, mname, lname");
if (!empty($prow['squad']) && !AclMain::aclCheckCore('squads', $prow['squad'])) {
    AccessDeniedHelper::deny('Not authorized for squad: ' . $prow['squad']);
}

$ptname = trim(($prow['title'] ?? '') . ' ' . ($prow['fname'] ?? '') . ' ' . ($prow['mname'] ?? '') . ' ' . ($prow['lname'] ?? ''));
$svc = new ActiveMedicationListService();
$active = $svc->getActiveList($pid);
$inactive = $svc->getInactiveList($pid, $active);

/**
 * @param list<array{title: string, dose: string, start: ?string, end: ?string, comments: string}> $rows
 */
function oemr_print_medication_table(array $rows, bool $showEnd): void
{
    if ($rows === []) {
        echo '<p>' . xlt('None{{Issues}}') . '</p>';
        return;
    }
    echo '<table class="table table-bordered table-sm">';
    echo '<thead><tr>';
    echo '<th>' . xlt('Medication') . '</th>';
    echo '<th>' . xlt('Dose') . '</th>';
    echo '<th>' . xlt('Start') . '</th>';
    if ($showEnd) {
        echo '<th>' . xlt('End') . '</th>';
    }
    echo '<th>' . xlt('Comments') . '</th>';
    echo '</tr></thead><tbody>';
    foreach ($rows as $row) {
        echo '<tr>';
        echo '<td>' . text($row['title']) . '</td>';
        echo '<td>' . text($row['dose']) . '</td>';
        echo '<td>' . text($row['start'] !== null ? oeFormatShortDate($row['start']) : '') . '</td>';
        if ($showEnd) {
            echo '<td>' . text($row['end'] !== null ? oeFormatShortDate($row['end']) : '') . '</td>';
        }
        echo '<td>' . text($row['comments']) . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
}
?>
<html>
<head>
<?php Header::setupHeader(); ?>
<title><?php echo xlt('Medication List'); ?></title>
</head>
<body class="body_top">
<h4><?php echo xlt('Medication List'); ?>
    <?php echo ' ' . xlt('for') . ' ' . text($ptname); ?></h4>
<h5><?php echo xlt('Active'); ?></h5>
<?php oemr_print_medication_table($active, false); ?>
<h5><?php echo xlt('Inactive / historical'); ?></h5>
<?php oemr_print_medication_table($inactive, true); ?>
<script>
if (window.opener && window.opener.top && typeof window.opener.top.printLogPrint === "function") {
    window.opener.top.printLogPrint(window);
} else {
    window.print();
}
</script>
</body>
</html>
