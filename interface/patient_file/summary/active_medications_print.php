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

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Services\ActiveMedicationListService;
use OpenEMR\Services\ActiveMedicationSectionView;

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$pid = ActiveMedicationListService::printPatientId(
    CurrentRequest::get()->query->get('pid'),
    $session->get('pid', 0)
);

if ($pid < 1) {
    AccessDeniedHelper::deny('No patient selected');
}

if (!AclMain::aclCheckCore('patients', 'med')) {
    AccessDeniedHelper::deny('Unauthorized access to medication list');
}

$patient = [];
foreach (getPatientData($pid, "squad, title, fname, mname, lname") as $column => $value) {
    if (!is_string($column)) {
        continue;
    }
    $patient[$column] = $value;
}
$squadValue = $patient['squad'] ?? null;
$squad = is_string($squadValue) ? $squadValue : '';
if ($squad !== '' && !AclMain::aclCheckCore('squads', $squad)) {
    AccessDeniedHelper::deny('Not authorized for squad: ' . $squad);
}

$ptname = ActiveMedicationListService::patientName($patient);
$svc = new ActiveMedicationListService();
$active = $svc->getActiveList($pid);
$inactive = $svc->getInactiveList($pid, $active);
$formatMedDate = static fn (?string $d): string => ActiveMedicationListService::formatListDate(
    $d,
    static fn (string $raw) => oeFormatShortDate($raw)
);
?>
<html>
<head>
<?php Header::setupHeader(); ?>
<title><?php echo xlt('Medications'); ?></title>
</head>
<body class="body_top">
<p><b><?php echo xlt('Medications'); ?></b>
    <?php echo ' ' . xlt('for') . ' ' . text($ptname); ?></p>

<?php
$medSection = new ActiveMedicationSectionView(ServiceContainer::getTwig());
echo $medSection->render('Active', $active, xl(...), $formatMedDate, 'table table-bordered table-sm');
echo $medSection->render('Inactive', $inactive, xl(...), $formatMedDate, 'table table-bordered table-sm');
?>

<script>
if (window.opener && window.opener.top && typeof window.opener.top.printLogPrint === "function") {
    window.opener.top.printLogPrint(window);
} else {
    window.print();
}
</script>
</body>
</html>
