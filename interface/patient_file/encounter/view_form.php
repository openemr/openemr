<?php

/**
 * view_form.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2024 Sophisticated Acquisitions <sophisticated.acquisitions@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\ModulesApplication;
use OpenEMR\Events\Encounter\LoadEncounterFormFilterEvent;

$clean_id = sanitizeNumber($_GET["id"]);

$pageName = 'view.php';
$isLBF = false;
/**
 * @global $incdir
 */
if (substr($_GET["formname"], 0, 3) === 'LBF') {
    $dir = "$incdir/forms/LBF/";
    $isLBF = true;
    // Use the List Based Forms engine for all LBFxxxxx forms.
} else {
    if ((!empty($_GET['pid'])) && ($_GET['pid'] > 0)) {
        $pid = $_GET['pid'];
        $encounter = $_GET['encounter'];
    }

    // ensure the path variable has no illegal characters
    check_file_dir_name($_GET["formname"]);

    // ensure authorized to see the form
    if (!AclMain::aclCheckForm($_GET["formname"])) {
        $formLabel = xl_form_title(getRegistryEntryByDirectory($_GET["formname"], 'name')['name'] ?? '');
        $formLabel = (!empty($formLabel)) ? $formLabel : $_GET["formname"];
        echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => $formLabel]);
        exit;
    }
    $dir = "$incdir/forms/" . $_GET["formname"] . "/";
}

$encounterLoadFormEvent = new LoadEncounterFormFilterEvent($_GET["formname"], $dir, $pageName);
$encounterLoadFormEvent->setPid($pid ?? null);
$encounterLoadFormEvent->setEncounter($encounter ?? null);
$encounterLoadFormEvent->setIsLayoutBasedForm($isLBF);
$event = $GLOBALS['kernel']->getContainer()->get('event_dispatcher')->dispatch($encounterLoadFormEvent, LoadEncounterFormFilterEvent::EVENT_NAME);
$formDirToInclude = $dir . $pageName;
if (
    $event instanceof LoadEncounterFormFilterEvent
    && $event->getFormIncludePath() !== null
    && $event->getFormIncludePath() != $formDirToInclude
) {
    // currently we do not allow rerouting from one form to another, the event ONLY allows inclusion within the modules
    if (ModulesApplication::isSafeModuleFileForInclude($event->getFormIncludePath())) {
        $formDirToInclude = $event->getFormIncludePath();
    } else {
        (new SystemLogger())->errorLogCaller(
            "Module attempted to load a file outside of its directory",
            ['file' => $event->getFormIncludePath(), 'formdir' => $event->getFormName()]
        );
    }
}
include_once($formDirToInclude);

$id = $clean_id;
if (!empty($GLOBALS['text_templates_enabled'])) { ?>
    <script src="<?php echo $GLOBALS['web_root'] ?>/library/js/CustomTemplateLoader.js"></script>
<?php } ?>
