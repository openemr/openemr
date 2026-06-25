<?php

/**
 * FQHC module — UDS Patient Snapshot host page (Steps 2–3).
 *
 * Renders the essential UDS fields for the currently selected patient using the
 * design-system shell: reused demographics as data, an interactive income & FPL
 * card (#15), and the remaining UDS sections as empty-states until their capture
 * steps (#16–#17) land.
 *
 * Session state (patient id) is read here at the entry point and parsed into
 * typed values passed to the service layer — superglobals do not leak past this
 * boundary.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once __DIR__ . '/../../../../globals.php';

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\FQHC\DesignSystem\DesignSystemAssets;
use OpenEMR\FQHC\Fpl\FplGuidelineRepository;
use OpenEMR\FQHC\Fpl\FplRegion;
use OpenEMR\FQHC\Income\IncomeSummaryFactory;
use OpenEMR\FQHC\Income\PatientIncomeRepository;
use OpenEMR\FQHC\Snapshot\PatientDemographicsRepository;
use OpenEMR\FQHC\Snapshot\UdsSnapshotAssembler;
use OpenEMR\FQHC\SpecialPopulation\PatientSpecialPopulationRepository;
use OpenEMR\FQHC\SpecialPopulation\SpecialPopulation;
use OpenEMR\FQHC\SpecialPopulation\SpecialPopulationStatus;

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

// Income & FPL (#15). The center's FPL region is contiguous by default; this
// becomes a configurable setting in a later step.
$income = (new PatientIncomeRepository())->findByPid($pid);
$guideline = (new FplGuidelineRepository())->findLatestForRegion(FplRegion::Contiguous);
$incomeSummary = ($income !== null && $guideline !== null)
    ? (new IncomeSummaryFactory())->create($income, $guideline)
    : null;

// Special populations (#16).
$specialStatuses = (new PatientSpecialPopulationRepository())->findByPid($pid);
$specialPopulations = array_map(
    static fn(SpecialPopulationStatus $status): array => [
        'label' => $status->displayLabel(),
        'population' => $status->population->value,
    ],
    $specialStatuses,
);
$populationChoices = array_map(
    static fn(SpecialPopulation $population): array => [
        'value' => $population->value,
        'label' => $population->label(),
    ],
    SpecialPopulation::cases(),
);
$subtypeGroups = [];
foreach (SpecialPopulation::cases() as $population) {
    $options = $population->subtypeOptions();
    if ($options === []) {
        continue;
    }
    $grouped = [];
    foreach ($options as $value => $label) {
        $grouped[] = ['value' => $value, 'label' => $label];
    }
    $subtypeGroups[] = ['label' => $population->label(), 'options' => $grouped];
}

$session = SessionWrapperFactory::getInstance()->getActiveSession();

$content = (new TwigContainer(__DIR__ . '/../templates', $globals->getKernel()))
    ->getTwig()
    ->render('fqhc/snapshot.html.twig', [
        'snapshot' => $snapshot,
        'incomeSummary' => $incomeSummary,
        'incomeForm' => [
            'householdSize' => $income?->householdSize,
            'annualIncome' => $income?->annualIncome,
            'unknown' => $income !== null && $income->unknown,
        ],
        'guidelineLoaded' => $guideline !== null,
        'specialPopulations' => $specialPopulations,
        'populationChoices' => $populationChoices,
        'subtypeGroups' => $subtypeGroups,
        'csrfToken' => CsrfUtils::collectCsrfToken(session: $session),
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
