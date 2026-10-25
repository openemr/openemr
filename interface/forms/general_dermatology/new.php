<?php

/**
 * General Dermatology form entry (new/edit/template).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once 'vendor/autoload.php';
require_once '../../globals.php';

use GeneralDermatology\App\DisplayController;
use GeneralDermatology\App\FavoriteDiagnosisCodes;
use GeneralDermatology\App\LnPalpatedsList;
use GeneralDermatology\App\ProcedurePlans;
use GeneralDermatology\Catalog\LocationCatalog;
use GeneralDermatology\Catalog\ProcedureCatalog;
use GeneralDermatology\App\SeverityList;
use GeneralDermatology\App\SitesList;
use GeneralDermatology\App\SxsList;
use GeneralDermatology\Bootstrap;
use GeneralDermatology\Domain\TreatmentDocument;
use GeneralDermatology\Exception\GeneralDermatologyException;
use GeneralDermatology\Services\TemplateFormService;
use GeneralDermatology\Support\FormContext;
use GeneralDermatology\Support\NoteAccessService;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;

$context = FormContext::fromRuntime();
$session = $context->session();
$access = NoteAccessService::fromContext($context);

$encounter = $context->encounter();
$formid = (int) ($_GET['id'] ?? 0);
// Apply a shared SOAP template into the current encounter (not a patient form id).
$applyTemplateId = (int) ($_GET['template_id'] ?? $_GET['templateId'] ?? 0);
$templateMode = !empty($_GET['template_mode']) || !empty($_GET['templateMode']);
$existingFormId = [];
$loadedTemplateId = 0;
$templateFlash = [
    'type' => (string) ($_GET['tmpl_status'] ?? ''),
    'message' => (string) ($_GET['tmpl_msg'] ?? ''),
];

if (!$templateMode && !empty($encounter)) {
    $existingFormId = QueryUtils::querySingleRow(
        'SELECT form_id FROM forms WHERE encounter = ? AND formdir = ? AND deleted = 0',
        [$encounter, 'general_dermatology']
    ) ?: [];
}

$encounterFormId = !empty($existingFormId['form_id']) ? (int) $existingFormId['form_id'] : 0;
$general_dermatology_form_data = [];
$editingTemplateId = 0;

try {
    if ($templateMode && $formid > 0) {
        $loadedRow = $access->requireTemplate($formid);
        $general_dermatology_form_data = TemplateFormService::prepareTemplateEditFormData($loadedRow, $formid);
        $editingTemplateId = $formid;
        $loadedTemplateId = $formid;
    } elseif (!$templateMode && $applyTemplateId > 0) {
        // Apply template content into this encounter without treating template id as a forms.form_id.
        $loadedRow = $access->requireTemplate($applyTemplateId);
        $prepared = TemplateFormService::prepareLoadedFormData($loadedRow, $applyTemplateId, $encounterFormId);
        $general_dermatology_form_data = $prepared['formData'];
        $loadedTemplateId = $prepared['loadedTemplateId'];
        $encounterFormId = $prepared['encounterFormId'];
        $formid = $encounterFormId;
    } elseif ($formid > 0) {
        $loadedRow = $access->findById($formid) ?? [];
        if ($loadedRow === []) {
            $formid = 0;
        } elseif (TemplateFormService::isTemplateRow($loadedRow)) {
            // Legacy URLs that still pass template rows as id=.
            $prepared = TemplateFormService::prepareLoadedFormData($loadedRow, $formid, $encounterFormId);
            $general_dermatology_form_data = $prepared['formData'];
            $loadedTemplateId = $prepared['loadedTemplateId'];
            $encounterFormId = $prepared['encounterFormId'];
            $formid = $encounterFormId;
        } else {
            // Patient note: enforce ownership.
            $loadedRow = $access->requirePatientNote($formid);
            $prepared = TemplateFormService::prepareLoadedFormData($loadedRow, $formid, $encounterFormId);
            $general_dermatology_form_data = $prepared['formData'];
            $loadedTemplateId = $prepared['loadedTemplateId'];
            $encounterFormId = $prepared['encounterFormId'];
        }
    }
} catch (GeneralDermatologyException $e) {
    http_response_code(403);
    echo text($e->getMessage());
    exit;
}

$twig = new Bootstrap();
$code = new FavoriteDiagnosisCodes();
$severity = new SeverityList();
$lnpalpateds = new LnPalpatedsList();
$sites = new SitesList();
$sxs = new SxsList();
$procedurePlans = new ProcedurePlans();
$treatmentDocument = TreatmentDocument::fromStorage(
    !empty($general_dermatology_form_data) ? ($general_dermatology_form_data['treatment'] ?? null) : null
);
$treatments = TreatmentDocument::findingsForUi($treatmentDocument);
$elementCount = count($treatments);
$mapBootstrap = LocationCatalog::bootstrapForMapUi();
$mapSavedFindings = [];
foreach ($treatments as $findingRow) {
    if (!is_array($findingRow)) {
        continue;
    }
    $mapSavedFindings[] = [
        'finding_location' => (string) ($findingRow['finding_location'] ?? ''),
        'location' => is_array($findingRow['location'] ?? null) ? $findingRow['location'] : null,
    ];
}

$webroot = $context->webroot();
$templateFormsData = TemplateFormService::fetchActiveTemplatesForDropdown();
$templateManagerUrl = TemplateFormService::buildTemplateManagerUrl($webroot);

if ($templateMode) {
    $mode = $editingTemplateId > 0 ? 'updateTemplate' : 'saveTemplate';
    $saveFormId = $editingTemplateId;
    $formSaveAction = TemplateFormService::buildTemplateSaveAction($editingTemplateId > 0, $editingTemplateId);
} else {
    $saveTarget = TemplateFormService::resolvePatientSaveTarget($encounterFormId);
    $mode = $saveTarget['mode'];
    $saveFormId = $saveTarget['saveFormId'];
    $formSaveAction = TemplateFormService::buildPatientSaveAction($mode, $saveFormId);
}

$templateLoadUrlBase = TemplateFormService::buildTemplateLoadUrl($webroot, '__TEMPLATE_ID__');

echo $twig->twigEnv()->render(
    'dermatology_main_template.twig',
    [
        'csrf' => CsrfUtils::collectCsrfToken(session: $session),
        'days' => DisplayController::getDays(),
        'weeks' => DisplayController::getWeeks(),
        'months' => DisplayController::getMonths(),
        'years' => DisplayController::getYears(),
        'mode' => $mode,
        'saveFormId' => $saveFormId,
        'formSaveAction' => $formSaveAction,
        'loadedTemplateId' => $loadedTemplateId,
        'templateMode' => $templateMode,
        'editingTemplateId' => $editingTemplateId,
        'templateManagerUrl' => $templateManagerUrl,
        'templateFlash' => $templateFlash,
        'webroot' => $webroot,
        'templateLoadUrlBase' => $templateLoadUrlBase,
        'codes' => $code->codeArray(),
        'diagnosisAndDescription' => FavoriteDiagnosisCodes::diagnosisAndDescription(),
        'severities' => $severity->severityArray(),
        'lnpalpateds' => $lnpalpateds->lnPalpateds(),
        'sites' => $sites->sites(),
        'sxslist' => $sxs->getSxs(),
        'plans' => ProcedureCatalog::plans(),
        'displayValues' => ProcedureCatalog::displayValues(),
        'general_dermatology' => $general_dermatology_form_data,
        'templateFormsData' => $templateFormsData,
        'treatments' => $treatments,
        'reviewNotes' => $severity->endNotesArray(),
        'elementCount' => $elementCount,
        'encounter' => $encounter,
        'mapBootstrap' => $mapBootstrap,
        'mapSavedFindings' => $mapSavedFindings,
    ]
);
