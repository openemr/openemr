<?php

/**
 * General Dermatology save handler.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

global $userauthorized, $pid, $encounter, $descriptions, $srcdir;

require_once 'vendor/autoload.php';
require_once '../../globals.php';
require_once "$srcdir/api.inc.php";
require_once "$srcdir/forms.inc.php";
require_once 'resources/dx-desc.php';

use GeneralDermatology\App\GeneralDermatologyClaimProcessor;
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

if (!CsrfUtils::verifyCsrfToken($_POST['csrf_token_form'] ?? null, $session)) {
    CsrfUtils::csrfNotVerified();
}

// Prefer session encounter/pid over stale globals when available.
$sessionPid = $context->pid();
$sessionEncounter = $context->encounter();
if ($sessionPid > 0) {
    $pid = $sessionPid;
}
if ($sessionEncounter !== 0 && $sessionEncounter !== '0') {
    $encounter = $sessionEncounter;
}

$claimData = new GeneralDermatologyClaimProcessor($context);

$billingData = [];
$billingData['treatment'] = $_POST['treatment'] ?? [];
$billingData['claim'] = $_POST['claim'] ?? [];
$claimData->setClaimData($billingData);

unset($_POST['mode'], $_POST['claim'], $_POST['billing_code']);

if ($encounter === '' || $encounter === null || $encounter === 0 || $encounter === '0') {
    $encounter = date('Ymd');
}

if (isset($_POST['treatment']) && is_array($_POST['treatment'])) {
    $document = TreatmentDocument::fromPostFindings($_POST['treatment']);
    $validationErrors = TreatmentDocument::validate($document);
    if ($validationErrors !== []) {
        // Soft-fail invalid empty cards already stripped; hard errors should not occur from UI posts.
        error_log('General Dermatology treatment validation: ' . implode('; ', $validationErrors));
    }
    $_POST['treatment'] = TreatmentDocument::encodeForStorage($document);
    // Keep claim processor diagnosis source as findings list.
    $billingData['treatment'] = TreatmentDocument::findingsForUi($document);
} elseif (isset($_POST['treatment']) && is_string($_POST['treatment'])) {
    $document = TreatmentDocument::fromStorage($_POST['treatment']);
    $_POST['treatment'] = TreatmentDocument::encodeForStorage($document);
    $billingData['treatment'] = TreatmentDocument::findingsForUi($document);
}
$claimData->setClaimData($billingData);


$mode = (string) ($_GET['mode'] ?? ($_POST['mode'] ?? ''));
$formId = (int) ($_GET['id'] ?? ($_POST['id'] ?? 0));
$authUser = $context->authUser();
$webroot = $context->webroot();

$redirectWithMessage = static function (string $status, string $message, string $url): void {
    $separator = str_contains($url, '?') ? '&' : '?';
    $target = $url . $separator . http_build_query([
        'tmpl_status' => $status,
        'tmpl_msg' => $message,
    ]);
    formHeader('Redirecting....');
    formJump($target);
    formFooter();
};

if ($mode === 'new' || $mode === 'update') {
    $_POST = TemplateFormService::sanitizePatientSavePayload($_POST);
}

if ($mode === 'saveTemplate' || $mode === 'updateTemplate') {
    $excludeId = $mode === 'updateTemplate' ? $formId : null;
    if ($mode === 'updateTemplate') {
        $existing = TemplateFormService::getTemplateById($formId);
        if ($existing === null) {
            $redirectWithMessage(
                'danger',
                'Template not found or is not a template row.',
                TemplateFormService::buildTemplateManagerUrl($webroot)
            );
        }
    }

    $validation = TemplateFormService::validateTemplateSave($_POST, $excludeId);
    if (!$validation['ok']) {
        $returnUrl = $mode === 'updateTemplate' && $formId > 0
            ? TemplateFormService::buildTemplateEditUrl($webroot, $formId)
            : TemplateFormService::buildTemplateEditUrl($webroot, 0);
        if (empty($_POST['template_mode']) && empty($_GET['template_mode'])) {
            $returnUrl = $webroot . '/interface/patient_file/encounter/load_form.php?formname=general_dermatology';
            if ($formId > 0 && $mode === 'updateTemplate') {
                $returnUrl .= '&id=' . rawurlencode((string) $formId);
            }
        }
        $redirectWithMessage('danger', implode(' ', $validation['errors']), $returnUrl);
    }

    $_POST = TemplateFormService::prepareTemplateWritePayload($_POST, $validation['name'], $authUser);

    if ($mode === 'saveTemplate') {
        $newid = formSubmit('general_dermatology', $_POST, 0, $userauthorized);
        TemplateFormService::markTemplateMetadata((int) $newid, $authUser);
        $msg = "Template '{$validation['name']}' saved and available in Load Existing Template.";
        if (!empty($validation['warnings'])) {
            $msg .= ' ' . implode(' ', $validation['warnings']);
        }
        $redirectWithMessage('success', $msg, TemplateFormService::buildTemplateManagerUrl($webroot));
    }

    formUpdate('general_dermatology', $_POST, $formId, $userauthorized);
    TemplateFormService::markTemplateMetadata($formId, $authUser);
    $msg = "Template '{$validation['name']}' updated.";
    if (!empty($validation['warnings'])) {
        $msg .= ' ' . implode(' ', $validation['warnings']);
    }
    $redirectWithMessage('success', $msg, TemplateFormService::buildTemplateManagerUrl($webroot));
} elseif ($mode === 'new') {
    $newid = formSubmit('general_dermatology', $_POST, 0, $userauthorized);
    addForm($session->get('encounter'), 'General Dermatology', $newid, 'general_dermatology', $pid, $userauthorized);
} elseif ($mode === 'update') {
    $encounterFormId = 0;
    if ($formId <= 0) {
        $existing = QueryUtils::querySingleRow(
            'SELECT form_id FROM forms WHERE encounter = ? AND formdir = ? AND deleted = 0',
            [$session->get('encounter'), 'general_dermatology']
        ) ?: [];
        $encounterFormId = (int) ($existing['form_id'] ?? 0);
    }

    $formId = TemplateFormService::resolveUpdateFormId($formId, $encounterFormId);

    if (TemplateFormService::shouldCreateInsteadOfUpdate($formId)) {
        $newid = formSubmit('general_dermatology', $_POST, 0, $userauthorized);
        addForm($session->get('encounter'), 'General Dermatology', $newid, 'general_dermatology', $pid, $userauthorized);
    } else {
        try {
            $access->requirePatientNote($formId, (int) $pid);
        } catch (GeneralDermatologyException $e) {
            http_response_code(403);
            echo text($e->getMessage());
            exit;
        }
        formUpdate('general_dermatology', $_POST, $formId, $userauthorized);
    }
}

try {
    $billed = $claimData->hasBeenBilled();
    $claims = $billingData['claim'] ?? [];
    if (!$billed && is_array($claims) && $claims !== []) {
        $claimData->processClaim();
    }
} catch (GeneralDermatologyException $e) {
    error_log('General Dermatology Billing Error: ' . $e->getMessage());
} catch (Throwable $e) {
    error_log('General Dermatology Unexpected Billing Error: ' . $e->getMessage());
}

formHeader('Redirecting....');
formJump();
formFooter();
