<?php

/**
 * General Dermatology SOAP Template Manager.
 */

require_once 'vendor/autoload.php';
require_once '../../globals.php';

use GeneralDermatology\Bootstrap;
use GeneralDermatology\Services\TemplateFormService;
use OpenEMR\Common\Acl\AclMain;
use GeneralDermatology\Support\FormContext;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\OEGlobalsBag;

$context = FormContext::fromRuntime();
$session = $context->session();

if (!AclMain::aclCheckForm('general_dermatology')) {
    echo (new TwigContainer(null, OEGlobalsBag::getInstance()->getKernel()))->getTwig()->render(
        'core/unauthorized.html.twig',
        ['pageTitle' => xl('Dermatology Templates')]
    );
    exit;
}

$webroot = $context->webroot();
$authUser = $context->authUser();
$flash = ['type' => '', 'messages' => []];
$preview = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CsrfUtils::verifyCsrfToken($_POST['csrf_token_form'] ?? null, $session)) {
        CsrfUtils::csrfNotVerified();
    }

    $action = (string) ($_POST['action'] ?? '');
    $templateId = (int) ($_POST['template_id'] ?? 0);
    $result = ['ok' => false, 'errors' => ['Unknown action.']];

    switch ($action) {
        case 'rename':
            $result = TemplateFormService::renameTemplate(
                $templateId,
                (string) ($_POST['new_name'] ?? '')
            );
            if ($result['ok']) {
                $flash = [
                    'type' => 'success',
                    'messages' => ['Template renamed to \'' . $result['name'] . '\'.'],
                ];
            }
            break;

        case 'duplicate':
            $result = TemplateFormService::duplicateTemplate(
                $templateId,
                (string) ($_POST['new_name'] ?? ''),
                $authUser
            );
            if ($result['ok']) {
                $flash = [
                    'type' => 'success',
                    'messages' => ['Template duplicated as \'' . ($result['name'] ?? '') . '\'.'],
                ];
            }
            break;

        case 'activate':
            $result = TemplateFormService::setTemplateStatus(
                $templateId,
                TemplateFormService::STATUS_ACTIVE
            );
            if ($result['ok']) {
                $flash = [
                    'type' => 'success',
                    'messages' => ['Template activated and available in Load Existing Template.'],
                ];
            }
            break;

        case 'deactivate':
            $result = TemplateFormService::setTemplateStatus(
                $templateId,
                TemplateFormService::STATUS_INACTIVE
            );
            if ($result['ok']) {
                $flash = [
                    'type' => 'success',
                    'messages' => ['Template deactivated. It will no longer appear in Load Existing Template.'],
                ];
            }
            break;

        case 'delete':
            $result = TemplateFormService::deleteTemplate($templateId);
            if ($result['ok']) {
                $flash = [
                    'type' => 'success',
                    'messages' => ['Template deleted.'],
                ];
            }
            break;

        case 'preview':
            $row = TemplateFormService::getTemplateById($templateId);
            if ($row === null) {
                $result = ['ok' => false, 'errors' => ['Template not found.']];
            } else {
                $result = ['ok' => true, 'errors' => []];
                $preview = [
                    'id' => $templateId,
                    'name' => (string) ($row['general_dermatology_form_template_name'] ?? ''),
                    'summary' => TemplateFormService::buildPreviewSummary($row),
                ];
            }
            break;

        default:
            $result = ['ok' => false, 'errors' => ['Unknown action.']];
            break;
    }

    if (!$result['ok']) {
        $flash = [
            'type' => 'danger',
            'messages' => $result['errors'] ?? ['Action failed.'],
        ];
    }
}

$search = trim((string) ($_GET['q'] ?? $_POST['q'] ?? ''));
$status = (string) ($_GET['status'] ?? $_POST['status'] ?? 'all');
$sort = (string) ($_GET['sort'] ?? 'name');
$dir = (string) ($_GET['dir'] ?? 'asc');
$page = (int) ($_GET['page'] ?? 1);

$list = TemplateFormService::listTemplates($search, $status, $sort, $dir, $page, 25);

$twig = new Bootstrap();
echo $twig->twigEnv()->render('templates/index.twig', [
    'csrf' => CsrfUtils::collectCsrfToken(session: $session),
    'webroot' => $webroot,
    'search' => $search,
    'status' => $status,
    'sort' => $sort,
    'dir' => $dir,
    'list' => $list,
    'flash' => $flash,
    'preview' => $preview,
    'createUrl' => TemplateFormService::buildTemplateEditUrl($webroot, 0),
    'managerUrl' => TemplateFormService::buildTemplateManagerUrl($webroot),
]);
