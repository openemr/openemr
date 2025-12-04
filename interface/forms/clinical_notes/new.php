<?php

/**
 * Clinical Notes form new.php Borrowed from Care Plan
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jacob T Paul <jacob@zhservices.com>
 * @author    Vinish K <vinish@zhservices.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2015 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/api.inc.php");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/patient.inc.php");
require_once("$srcdir/options.inc.php");
require_once($GLOBALS['srcdir'] . '/csv_like_join.php');

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Events\Core\TemplatePageEvent;
use OpenEMR\Services\ClinicalNotesService;
use OpenEMR\Services\ListService;
use OpenEMR\Services\PatientService;

$returnurl = 'encounter_top.php';
$formid = (int)($_GET['id'] ?? 0);

$clinicalNotesService = new ClinicalNotesService();
$alertMessage = '';
if (empty($formid)) {
    $sql = "SELECT form_id, encounter FROM `forms` WHERE formdir = 'clinical_notes' AND pid = ? AND encounter = ? AND deleted = 0 LIMIT 1";
    $formid = sqlQuery($sql, [$_SESSION["pid"], $_SESSION["encounter"]])['form_id'] ?? 0;
    if (!empty($formid)) {
        $alertMessage = xl("Already a Clinical Notes form for this encounter. Using existing Clinical Notes form.");
    }
}

$clinical_notes_type = $clinicalNotesService->getClinicalNoteTypes();
$clinical_notes_category = $clinicalNotesService->getClinicalNoteCategories();
$getDefaultValue = function ($items) {
    $selectedItem = array_filter($items, fn($val) => $val['selected']);
    if (empty($selectedItem)) {
        return ''; // default to an empty value if there is no default option
    } else {
        return array_pop($selectedItem)['value'] ?? '';
    }
};
$defaultType = $getDefaultValue($clinical_notes_type);
$defaultCategory = $getDefaultValue($clinical_notes_category);
if ($formid) {
    $records = $clinicalNotesService->getClinicalNotesForPatientForm($formid, $_SESSION['pid'], $_SESSION['encounter']) ?? [];
    $check_res = [];
    foreach ($records as $record) {
        // we are only going to include active clinical notes, but we leave them as historical records in the system
        // FHIR and other resources still refer to them, they will just be marked as inactive...
        if ($record['activity'] == ClinicalNotesService::ACTIVITY_ACTIVE) {
            $record['uuid'] = UuidRegistry::uuidToString($record['uuid']);
            $record['full_name'] = sqlQuery("SELECT CONCAT(fname, ' ', lname) AS full_name FROM users WHERE username = ?", [$record['user']]) ['full_name'] ?? '';
            $check_res[] = $record;
        }
        // if we don't have a type_title or type_category, we are going to set them to the default values as we don't have a matching list option type / category
        if (empty($record['type_title'])) {
            $record['clinical_notes_type'] = $defaultType;
        }
        if (empty($record['type_category'])) {
            $record['clinical_notes_category'] = $defaultCategory;
        }
    }
} else {
    $check_res = [
        [
            'id' => 0
            ,'code' => ''
            ,'codetext' => ''
            ,'clinical_notes_type' => $defaultType
            ,'clinical_notes_category' => $defaultCategory
            ,'description' => ''
            ,'date' => oeFormatShortDate(date('Y-m-d'))
        ]
    ];
}

$patientService = new PatientService();
$patient = $patientService->findByPid($_SESSION['pid']);
$listService = new ListService();
$resultCategories = $listService->getOptionsByListName('Observation_Types');
$twig = new TwigContainer(dirname(__DIR__), $GLOBALS['kernel']);
$t = $twig->getTwig();
$viewArgs = [
    'clinical_notes_type' => $clinical_notes_type
    ,'patientUuid' => UuidRegistry::uuidToString($patient['uuid'])
    ,'clinical_notes_category' => $clinical_notes_category
    ,'oemrUiSettings' =>  [
        'heading_title' => xl('Clinical Notes Form'),
        'include_patient_name' => false,
        'expandable' => true,
        'expandable_files' => [],//all file names need suffix _xpd
        'action' => "",//conceal, reveal, search, reset, link or back
        'action_title' => "",
        'action_href' => "",//only for actions - reset, link and back
        'show_help_icon' => false, // there could be a help icon at some point here but we don't use it for now
        'help_file_name' => ''
    ]
    ,'check_res' => $check_res
    ,'alertMessage' => $alertMessage
    ,'rootdir' => $GLOBALS['rootdir']
    ,'formid' => $formid
    ,'defaultType' => $defaultType
    ,'defaultCategory' => $defaultCategory
    ,'csrfToken' => CsrfUtils::collectCsrfToken('api')
    ,'resultCategories' => $resultCategories ?? []
];
$templatePageEvent = new TemplatePageEvent(
    'clinical_notes/new.php',
    [],
    'clinical_notes/templates/new.html.twig',
    $viewArgs
);
$event = $GLOBALS['kernel']->getEventDispatcher()->dispatch($templatePageEvent, TemplatePageEvent::RENDER_EVENT);
if (!$event instanceof TemplatePageEvent) {
    throw new \RuntimeException('Invalid event returned from template page event');
}

// Render template
echo $t->render($event->getTwigTemplate(), $event->getTwigVariables());
