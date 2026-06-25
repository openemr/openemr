<?php

/**
 * FQHC module — UDS Patient Snapshot host page (Step 2).
 *
 * Renders the essential UDS fields for the currently selected patient using the
 * design-system shell. Reused demographics are shown as data; the new UDS
 * sections appear as empty-states until their capture steps (#15–#17) land.
 *
 * The session patient id is read here, at the entry point, and immediately
 * parsed into a typed pid passed to the service layer — superglobals do not
 * leak past this boundary.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once __DIR__ . '/../../../../globals.php';

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\FQHC\DesignSystem\DesignSystemAssets;
use OpenEMR\FQHC\Snapshot\PatientDemographicsRepository;
use OpenEMR\FQHC\Snapshot\UdsSnapshotAssembler;

if (!AclMain::aclCheckCore('patients', 'demo')) {
    echo xlt('Access denied');
    exit;
}

$globals = OEGlobalsBag::getInstance();
$publicBaseUrl = $globals->getString('webroot') . '/interface/modules/custom_modules/oe-module-fqhc/public';
$assets = new DesignSystemAssets(__DIR__, $publicBaseUrl);

$sessionPid = $_SESSION['pid'] ?? 0;
$pid = is_numeric($sessionPid) ? (int) $sessionPid : 0;
$demographics = (new PatientDemographicsRepository())->findByPid($pid);
$snapshot = $demographics !== null
    ? (new UdsSnapshotAssembler())->assemble($demographics)
    : null;

$twigContainer = new TwigContainer(__DIR__ . '/../templates', $globals->getKernel());
$content = $twigContainer->getTwig()->render('fqhc/snapshot.html.twig', [
    'snapshot' => $snapshot,
]);
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('UDS Patient Snapshot'); ?></title>
    <?php Header::setupHeader(['common']); ?>
    <?php foreach ($assets->styleUrls() as $styleUrl) { ?>
        <link rel="stylesheet" href="<?php echo attr($styleUrl); ?>">
    <?php } ?>
</head>
<body class="body_top">
    <?php echo $content; ?>
    <?php foreach ($assets->scriptUrls() as $scriptUrl) { ?>
        <script type="module" src="<?php echo attr($scriptUrl); ?>"></script>
    <?php } ?>
</body>
</html>
