<?php

/**
 * view_form.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2024 Sophisticated Acquisitions <sophisticated.acquisitions@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Forms\EncounterFormAccess;
use OpenEMR\Common\Forms\FormLocator;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\EncounterSessionUtil;
use OpenEMR\Common\Session\PatientSessionUtil;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\EncounterService;
use Symfony\Component\HttpFoundation\Response;

$clean_id = sanitizeNumber($_GET["id"]);

$pageName = 'view.php';
$isLBF = false;
/**
 * @global $incdir
 */
if (!str_starts_with((string) $_GET["formname"], 'LBF')) {
    // ensure the path variable has no illegal characters
    check_file_dir_name($_GET["formname"]);

    // ensure authorized to see the form
    if (!AclMain::aclCheckForm($_GET["formname"])) {
        $formLabel = xl_form_title(getRegistryEntryByDirectory($_GET["formname"], 'name')['name'] ?? '');
        $formLabel = $formLabel !== '' ? (string) $formLabel : (string) $_GET["formname"];
        AccessDeniedHelper::denyWithTemplate("ACL check failed for form: " . $formLabel, $formLabel);
    }
}

// Mirror load_form.php: drive pid/encounter from the session and confirm any
// requested form id belongs to the session's opened patient. The prior code
// let $_GET['pid'] / $_GET['encounter'] override session context directly.
$requestQuery = CurrentRequest::get()->query;
$formId = max(0, $requestQuery->getInt('id'));
$formDir = $requestQuery->getString('formname');

$sessionPid = PatientSessionUtil::getPid();
$sensitivityEncounterId = EncounterSessionUtil::getEncounter();

if ($formId > 0) {
    $formOwner = EncounterFormAccess::fetchFormOwner($formId, $formDir);
    if ($formOwner === null || !EncounterFormAccess::isFormOwnedBySession($formOwner['pid'], $sessionPid)) {
        AccessDeniedHelper::deny(
            sprintf('Form %d/%s not accessible by session pid %d', $formId, $formDir, $sessionPid),
            'security-access',
            Response::HTTP_NOT_FOUND,
        );
    }
    $sensitivityEncounterId = $formOwner['encounter'];
}

if ($sensitivityEncounterId > 0) {
    $sensitivity = (new EncounterService())->getSensitivity($sessionPid, $sensitivityEncounterId);
    if ($sensitivity !== null && $sensitivity !== '' && !AclMain::aclCheckCore('sensitivities', $sensitivity)) {
        AccessDeniedHelper::denyWithTemplate('Not authorized to view encounter form.', 'Not authorized');
    }
}

$pid = $sessionPid;
$encounter = $sensitivityEncounterId;

$formLocator = new FormLocator();
$file = $formLocator->findFile($_GET['formname'], $pageName, 'load_form.php');
require_once($file);

$id = $clean_id;
if (OEGlobalsBag::getInstance()->getBoolean('text_templates_enabled')) { ?>
    <script src="<?php echo OEGlobalsBag::getInstance()->getWebRoot() ?>/library/js/CustomTemplateLoader.js"></script>
<?php } ?>
