<?php

/**
 * FormAdminController.php  - Controller for the forms administration page
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2024 Sophisticated Acquisitions <sophisticated.acquisitions@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 * @license   There are segments of code in this file that have been generated via Claude.ai and used forms_admin.php as its original source are licensed as Public Domain.  They have been marked with a header and footer.
 */

namespace OpenEMR\Controllers\Forms;

// AI GENERATED HEADER START
use OpenEMR\Common\Acl\AclExtended;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Twig\AdminTwigExtension;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Events\Forms\ModuleFormsFilterEvent;
use OpenEMR\OeUI\OemrUI;
use OpenEMR\Services\FormRegistryService;
use Twig\TwigFunction;

use function sqlQuery;
use function xl;

class FormAdminController
{
    private FormRegistryService $formService;
    private TwigContainer $twig;

    public function __construct()
    {
        $this->formService = new FormRegistryService();
        $this->twig = new TwigContainer(null, $GLOBALS['kernel']);
    }

    public function checkAccess(): bool
    {
        if (!AclMain::aclCheckCore('admin', 'forms')) {
            echo $this->twig->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Forms Administration")]);
            return false;
        }
        return true;
    }

    public function handleAction(): ?string
    {
        // AI GENERATED HEADER END
        if (!$this->hasAction()) {
            return null;
        }
        if ($this->isPost()) {
            return $this->handlePost();
        }

        if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
            CsrfUtils::csrfNotVerified();
        }

        try {
            switch ($_GET['method']) {
                case 'enable':
                    $this->formService->updateRegistered($_GET['id'], "state=1");
                    break;
                case 'disable':
                    $this->formService->updateRegistered($_GET['id'], "state=0");
                    break;
                case 'install_db':
                    if ($this->formService->installSQL($_GET['id'])) {
                        $this->formService->updateRegistered($_GET['id'], "sql_run=1");
                    } else {
                        return xl('ERROR: could not open table.sql, broken form?');
                    }
                    break;
                case 'register':
                    $id = $this->formService->registerForm($_GET['name']);
                    if (!$id) {
                        return xl('error while registering form!');
                    }
                    break;
            }
        } catch (SqlQueryException $exception) {
            (new SystemLogger())->errorLogCaller($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
            return xl('error while updating form!');
        }
        return null;
    }

    public function hasAction(): bool
    {
        return $this->isPost() || !empty($_GET['method']);
    }

    private function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    public function handlePost(): ?string
    {
        if (!$this->hasAction()) {
            return null;
        }

        if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
            CsrfUtils::csrfNotVerified();
        }
        try {
            foreach ($_POST as $key => $val) {
                if (preg_match('/nickname_(\d+)/', $key, $matches)) {
                    QueryUtils::sqlStatementThrowException("update registry set nickname = ? where id = ?", array($val, $matches[1]));
                } elseif (preg_match('/category_(\d+)/', $key, $matches)) {
                    QueryUtils::sqlStatementThrowException("update registry set category = ? where id = ?", array($val, $matches[1]));
                } elseif (preg_match('/priority_(\d+)/', $key, $matches)) {
                    QueryUtils::sqlStatementThrowException("update registry set priority = ? where id = ?", array($val, $matches[1]));
                } elseif (preg_match('/aco_spec_(\d+)/', $key, $matches)) {
                    QueryUtils::sqlStatementThrowException("update registry set aco_spec = ? where id = ?", array($val, $matches[1]));
                }
            }
            return null;
        } catch (SqlQueryException $exception) {
            (new SystemLogger())->errorLogCaller($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
            return xl('error while updating form!');
        }
    }
    // AI GENERATION START

    public function getAllForms(): array
    {
        $moduleFormsEvent = new ModuleFormsFilterEvent();
        $moduleFormsEvent = $GLOBALS['kernel']->getContainer()->get('event_dispatcher')
            ->dispatch($moduleFormsEvent, ModuleFormsFilterEvent::EVENT_NAME);

        $forms = $this->formService->getAllForms();

        if ($moduleFormsEvent->hasModuleForms()) {
            $forms = array_merge($forms, $moduleFormsEvent->getModuleForms());
        }

        foreach ($forms as &$form) {
            // AI GENERATED HEADER END
            if (!isset($form['mod_id'])) {
                $form['patient_portal_compliant'] = file_exists($GLOBALS['srcdir'] . "/../interface/forms/" . $form['directory'] . "/patient_portal.php");
            }
            // AI GENERATED HEADER START
        }
        usort($forms, fn($a, $b) => strcasecmp($a['name'], $b['name']));

        return $forms;
    }

    public function render($pageName, ?string $error = null): void
    {
        $forms = $this->getAllForms();
        $registeredForms = array_filter($forms, fn($form) => $form['status'] === 'registered');
        $unregisteredForms = array_filter($forms, fn($form) => $form['status'] === 'unregistered');
        $twig = $this->twig->getTwig();
        // AI GENERATED HEADER END
        $twig->addExtension(new AdminTwigExtension());

        $title = xl("Forms Administration");
        $oemrUiSettings = OemrUI::getDefaultSettings($title);
        $oemrUiSettings['expandable'] = true;
        $oemrUiSettings['expandable_files'] = [$pageName];
        // AI GENERATED HEADER END
        echo $twig->render('interface/forms_admin/admin.html.twig', [
            'registeredForms' => $registeredForms,
            'unregisteredForms' => $unregisteredForms,
            'csrfToken' => CsrfUtils::collectCsrfToken(),
            'error' => $error,
            'title' => $title,
            // AI GENERATED HEADER END
            'acoOptionsArray' => AclExtended::genAcoArray(),
            'oemrUiSettings' => $oemrUiSettings,
        ]);
    }
}
