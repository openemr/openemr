<?php

/**
 * load_form.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Forms\EncounterFormAccess;
use OpenEMR\Common\Forms\FormLocator;
use OpenEMR\Common\Session\EncounterSessionUtil;
use OpenEMR\Common\Session\PatientSessionUtil;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\EncounterService;
use OpenEMR\Telemetry\TelemetryService;

/**
 * @gloal $incdir the include directory
 */
$incdir ??= "";

$pageName = "new.php";
if (!str_starts_with((string) $_GET["formname"], 'LBF')) {
    if ((!empty($_GET['pid'])) && ($_GET['pid'] > 0)) {
        $pid = $_GET['pid'];
        $encounter = $_GET['encounter'];
    }

    // ensure the path variable has no illegal characters
    check_file_dir_name($_GET["formname"]);

    // ensure authorized to see the form
    if (!AclMain::aclCheckForm($_GET["formname"])) {
        $formLabel = xl_form_title(getRegistryEntryByDirectory($_GET["formname"], 'name')['name'] ?? '');
        $formLabel = $formLabel !== '' ? (string) $formLabel : (string) $_GET["formname"];
        AccessDeniedHelper::denyWithTemplate("ACL check failed for form: " . $formLabel, $formLabel);
    }
}
// Confirm the target form (if identified by an existing form_id) belongs to the
// session patient. No-ops when creating a new form (id <= 0).
$formnameInput = filter_input(INPUT_GET, 'formname', FILTER_UNSAFE_RAW);
EncounterFormAccess::assertFormBelongsToSessionPatient(
    filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT),
    is_string($formnameInput) ? $formnameInput : '',
);

// Enforce encounter sensitivity ACL so a form load cannot bypass the
// sensitivity filter shown on the encounter dashboard.
$encounterInput = filter_input(INPUT_GET, 'encounter', FILTER_VALIDATE_INT);
$pidInput = filter_input(INPUT_GET, 'pid', FILTER_VALIDATE_INT);
$sensitivityEncounterId = is_int($encounterInput) && $encounterInput > 0
    ? $encounterInput
    : EncounterSessionUtil::getEncounter();
$sensitivityPid = is_int($pidInput) && $pidInput > 0
    ? $pidInput
    : PatientSessionUtil::getPid();
if ($sensitivityEncounterId > 0) {
    $sensitivity = (new EncounterService())->getSensitivity($sensitivityPid, $sensitivityEncounterId);
    // getSensitivity returns string when a row is found, [] when not.
    if (is_string($sensitivity) && $sensitivity !== '' && !AclMain::aclCheckCore('sensitivities', $sensitivity)) {
        AccessDeniedHelper::denyWithTemplate('Not authorized to view encounter form.', 'Not authorized');
    }
}

$formLocator = new FormLocator();
$file = $formLocator->findFile($_GET['formname'], $pageName, 'load_form.php');
require_once($file);

$telemetryService = new TelemetryService();
if ($telemetryService->isTelemetryEnabled()) {
    $telemetryService->reportClickEvent([
        'eventType' => 'encounterForm',
        'eventLabel' => $_GET['formname'] ?? 'Unknown',
        'eventUrl' => str_replace(OEGlobalsBag::getInstance()->getProjectDir(), '', $file),
        'eventTarget' => $pageName,
    ]);
}

if (OEGlobalsBag::getInstance()->getBoolean('text_templates_enabled') && !($_GET['formname'] == 'fee_sheet')) { ?>
    <script src="<?php echo OEGlobalsBag::getInstance()->getWebRoot() ?>/library/js/CustomTemplateLoader.js"></script>
<?php } ?>
