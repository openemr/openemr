<?php

/**
 * FQHC module — eligibility/care-management data-quality worklist (issue #28).
 *
 * The first purpose-built role workspace (epic #6): lists every patient in a
 * reporting year with a concrete UDS data-quality gap (missing age/sex,
 * unknown FPL band, or an insurance code that doesn't map to a UDS payer
 * category) so eligibility/care-management staff can follow up, rather than
 * the gap only showing up as a cross-table reconciliation mismatch on the UDS
 * report (epic #4).
 *
 * The reporting year is read from the query string at this entry point and
 * parsed into a typed value; superglobals do not leak past this boundary.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once __DIR__ . '/../../../../globals.php';

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\FQHC\DesignSystem\DesignSystemAssets;
use OpenEMR\FQHC\Reporting\DataQuality\DataQualityWorklistGenerator;
use OpenEMR\FQHC\Reporting\DataQuality\DataQualityWorklistPresenter;
use OpenEMR\FQHC\Reporting\ReportingPatientRepository;

if (!AclMain::aclCheckCore('patients', 'demo')) {
    echo xlt('Access denied');
    exit;
}

$globals = OEGlobalsBag::getInstance();
$publicBaseUrl = $globals->getString('webroot') . '/interface/modules/custom_modules/oe-module-fqhc/public';
$assets = new DesignSystemAssets(__DIR__, $publicBaseUrl);

// Reporting year: matches the UDS report page's year selection/default.
$currentYear = (int) date('Y');
$yearInput = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT);
$year = is_int($yearInput) && $yearInput >= 2000 && $yearInput <= $currentYear
    ? $yearInput
    : $currentYear - 1;
$yearOptions = range($currentYear, $currentYear - 6);

$worklist = (new DataQualityWorklistGenerator(new ReportingPatientRepository()))->generateForYear($year);
$view = (new DataQualityWorklistPresenter())->present($worklist);

$patientBaseUrl = $globals->getString('webroot') . '/interface/patient_file/summary/demographics.php';

$content = (new TwigContainer(__DIR__ . '/../templates', $globals->getKernel()))
    ->getTwig()
    ->render('fqhc/eligibility-worklist.html.twig', [
        'year' => $year,
        'yearOptions' => $yearOptions,
        'worklist' => $view,
        'patientBaseUrl' => $patientBaseUrl,
    ]);
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('Eligibility Worklist'); ?></title>
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
