<?php

/**
 * dynamic_finder.php
 *
 * Sponsored by David Eschelbacher, MD
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2012-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019, 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @copyright Copyright (c) 2026 Open Plan IT Ltd
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\UserInterface\PageHeadingRenderEvent;
use OpenEMR\OeUI\OemrUI;
use OpenEMR\Services\Globals\UserSettingsService;
use OpenEMR\Services\PatientFinder\PatientFinderColumnRegistryFactory;
use OpenEMR\Services\PatientFinder\PatientFinderDetailService;
use OpenEMR\Services\PatientFinder\PatientFinderPageService;
use OpenEMR\Services\PatientFinder\PatientFinderRecentService;
use OpenEMR\Services\PatientFinder\PatientFinderValue;
use Symfony\Component\EventDispatcher\EventDispatcher;

require_once(__DIR__ . "/../../globals.php");

require_once OEGlobalsBag::getInstance()->getSrcDir() . "/options.inc.php";

$uspfx = 'patient_finder.'; //substr(__FILE__, strlen($webserver_root)) . '.';
$patient_finder_exact_search = UserSettingsService::prevSetting($uspfx, 'patient_finder_exact_search', 'patient_finder_exact_search', ' ');

$searchAny = PatientFinderValue::string(empty($_GET['search_any']) ? '' : $_GET['search_any']);
unset($_GET['search_any']);
// Build the finder's column data from the shared registry (the core ptlistcols
// columns plus any a module contributed via PatientFinderColumnRegistrationEvent).
// The server-side endpoint (dynamic_finder_ajax.php) builds the same registry, so
// the displayed and queried columns agree. The column HTML is rendered by the
// Twig template; the DataTables column config is serialised to JSON below.
$eventDispatcher = OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher();
$registry = (new PatientFinderColumnRegistryFactory($eventDispatcher))->create();
$columns = (new PatientFinderPageService())->getColumns($registry);
// A row can be expanded to show the Patient Finder Detail layout, when that layout shows anything.
$detailEnabled = (new PatientFinderDetailService($eventDispatcher))->isEnabled();

// Top-controls state for the finder form (rendered by the Twig template).
$newWindowChecked = OEGlobalsBag::getInstance()->getBoolean('gbl_pt_list_new_window');
$exactChecked = trim(PatientFinderValue::string($patient_finder_exact_search)) === 'checked';

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$cacheBust = attr_url(OEGlobalsBag::getInstance()->getString('v_js_includes'));
$pageSize = OEGlobalsBag::getInstance()->getInt('gbl_pt_list_page_size');
?>
<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(['datatables', 'datatables-colreorder', 'datatables-dt', 'datatables-bs']); ?>
    <title><?php echo xlt("Patient Finder"); ?></title>
    <link rel="stylesheet" href="dynamic_finder.css?v=<?php echo $cacheBust; ?>">
    <script>
        // Server-provided configuration consumed by dynamic_finder.js.
        window.finderConfig = {
            uspfx: <?php echo js_escape($uspfx); ?>,
            webroot: <?php echo js_escape(OEGlobalsBag::getInstance()->getWebRoot()); ?>,
            csrfToken: <?php echo js_escape(CsrfUtils::collectCsrfToken(session: $session)); ?>,
            searchAny: <?php echo js_escape($searchAny); ?>,
            pageLength: <?php echo $pageSize > 0 ? $pageSize : 10; ?>,
            // Column definitions from the shared registry (the Name column renders as a link).
            columns: <?php echo json_encode($columns, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>,
            // Expanding a row to show the Patient Finder Detail layout.
            detail: {
                enabled: <?php echo $detailEnabled ? 'true' : 'false'; ?>,
                url: <?php echo js_escape(OEGlobalsBag::getInstance()->getWebRoot() . '/interface/main/finder/patient_detail_ajax.php'); ?>,
                showLabel: <?php echo xlj('Show details'); ?>,
                hideLabel: <?php echo xlj('Hide details'); ?>,
                errorText: <?php echo xlj('The details could not be loaded.'); ?>
            },
            <?php // DataTables translations; emits a "language": { ... } property. ?>
            <?php $translationsDatatablesOverride = ['processing' => '']; ?>
            <?php require(OEGlobalsBag::getInstance()->getSrcDir() . '/js/xl/datatables-net.js.php'); ?>
        };
    </script>
    <script src="dynamic_finder.js?v=<?php echo $cacheBust; ?>"></script>
<?php
    /** @var EventDispatcher $eventDispatcher (resolved above when building the column registry) */
    $arrOeUiSettings = [
    'heading_title' => xl('Patient Finder'),
    'include_patient_name' => false,
    'expandable' => true,
    'expandable_files' => ['dynamic_finder_xpd'],//all file names need suffix _xpd
    'action' => "search",//conceal, reveal, search, reset, link or back
    'action_title' => "",//only for action link, leave empty for conceal, reveal, search
    'action_href' => "",//only for actions - reset, link or back
    'show_help_icon' => false,
    'help_file_name' => "",
    'page_id' => 'dynamic_finder',
    ];
    $oemr_ui = new OemrUI($arrOeUiSettings);

    $eventDispatcher->addListener(PageHeadingRenderEvent::EVENT_PAGE_HEADING_RENDER, function (PageHeadingRenderEvent $event): void {
        if ($event->getPageId() !== 'dynamic_finder') {
            return;
        }

        // One heading row: the list tabs, page-length menu, search box and Add Patient,
        // then the OemrUI margins and search-row icons on the right.
        $event->setTitleNavContent(ServiceContainer::getTwig()->render('patient_finder/finder_heading.html.twig', [
            'webroot' => OEGlobalsBag::getInstance()->getWebRoot(),
            'canAddPatient' => AclMain::aclCheckCore('patients', 'demo', '', ['write', 'addonly']),
        ]));
    });
    ?>
</head>
<body>
<?php

$recent = (new PatientFinderRecentService())->build();

$templateVars = [
    'oeContainer' => $oemr_ui->oeContainer(),
    'oeBelowContainerDiv' => $oemr_ui->oeBelowContainerDiv(),
    'pageHeading' => $oemr_ui->pageHeading(),
    'columns' => $columns,
    'detailEnabled' => $detailEnabled,
    'newWindowChecked' => $newWindowChecked,
    'exactSearch' => $patient_finder_exact_search,
    'exactChecked' => $exactChecked,
    'headers' => $recent['headers'],
    'rp' => $recent['rp'],
];

$t = ServiceContainer::getTwig();
echo $t->render('patient_finder/finder.html.twig', $templateVars);

?>
</body>
</html>
