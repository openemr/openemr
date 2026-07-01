<?php

/**
 * FQHC module — UDS patient-characteristics report host page (epic #4).
 *
 * Runs the UDS report generator for a chosen calendar year and renders the
 * patient-characteristics tables (3A, 3B, 4, and the ZIP Code Table) with the
 * cross-table reconciliation, using the design-system shell.
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

require_once __DIR__ . '/../../../../globals.php';

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\FQHC\DesignSystem\DesignSystemAssets;
use OpenEMR\FQHC\Reporting\ReportingPatientRepository;
use OpenEMR\FQHC\Reporting\Table5ReportGenerator;
use OpenEMR\FQHC\Reporting\Table5VisitRepository;
use OpenEMR\FQHC\Reporting\UdsReportGenerator;
use OpenEMR\FQHC\Reporting\UdsReportPresenter;

if (!AclMain::aclCheckCore('patients', 'demo')) {
    echo xlt('Access denied');
    exit;
}

$globals = OEGlobalsBag::getInstance();
$publicBaseUrl = $globals->getString('webroot') . '/interface/modules/custom_modules/oe-module-fqhc/public';
$assets = new DesignSystemAssets(__DIR__, $publicBaseUrl);

// Reporting year: a UDS report covers a calendar year; default to the most
// recently completed one. Anything outside a sane range falls back to that.
$currentYear = (int) date('Y');
$yearInput = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT);
$year = is_int($yearInput) && $yearInput >= 2000 && $yearInput <= $currentYear
    ? $yearInput
    : $currentYear - 1;
$yearOptions = range($currentYear, $currentYear - 6);

$presenter = new UdsReportPresenter();
$report = (new UdsReportGenerator(new ReportingPatientRepository()))->generateForYear($year);
$table5 = (new Table5ReportGenerator(new Table5VisitRepository()))->generateForYear($year);

$content = (new TwigContainer(__DIR__ . '/../templates', $globals->getKernel()))
    ->getTwig()
    ->render('fqhc/report.html.twig', [
        'year' => $year,
        'yearOptions' => $yearOptions,
        'report' => $presenter->present($report),
        'table5' => $presenter->table5($table5),
    ]);
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('UDS Report'); ?></title>
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
