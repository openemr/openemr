<?php

/**
 * Patient Statements: list what patients owe and print, download, email or
 * send their statements to the portal.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once("../globals.php");

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Billing\Invoice\InvoiceCategory;
use OpenEMR\Billing\Invoice\InvoiceList;
use OpenEMR\Billing\Invoice\InvoiceListRow;
use OpenEMR\Billing\Invoice\InvoiceSearchCriteria;
use OpenEMR\Billing\InvoiceServiceDates;
use OpenEMR\Billing\Statement\PatientNetDue;
use OpenEMR\Billing\Statement\StatementInvoices;
use OpenEMR\Billing\Statement\StatementListRows;
use OpenEMR\Billing\Statement\StatementPageServices;
use OpenEMR\Billing\Statement\StatementRunOnce;
use OpenEMR\Billing\Statement\StatementRunRequest;
use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Common\Utils\FormatMoney;
use OpenEMR\Core\OEGlobalsBag;

$globals = OEGlobalsBag::getInstance();
$srcDir = $globals->getSrcDir();
require_once($srcDir . '/appointments.inc.php');
// The site's statement layout; it also sets $STMT_TEMP_FILE and $STMT_TEMP_FILE_PDF.
require_once($globals->getString('OE_SITE_DIR') . '/statement.inc.php');
require_once($srcDir . '/../controllers/C_Document.class.php');
require_once($srcDir . '/documents.php');
require_once($srcDir . '/options.inc.php');

if (!AclMain::aclCheckCore('acct', 'eob', '', 'write')) {
    AccessDeniedHelper::denyWithTemplate('ACL check failed for acct/eob: Patient Statements', xl('Patient Statements'));
}

if ($globals->getBoolean('portal_onsite_two_enable')) {
    require_once("../../portal/lib/portal_mail.inc.php");
}

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$post = CurrentRequest::get()->request;
$today = date('Y-m-d');
$services = StatementPageServices::forSite(
    $globals,
    $session,
    ServiceContainer::getLogger(),
    $STMT_TEMP_FILE ?? null,
    $STMT_TEMP_FILE_PDF ?? null,
    $today
);

// Due Pt unless the biller asked for All (to resend or reprint any invoice).
$category = InvoiceCategory::fromForm($post->getString('form_category')) === InvoiceCategory::All
    ? InvoiceCategory::All
    : InvoiceCategory::DuePt;
$lastStatementBeforeInput = $post->getString('form_last_stmt_before');
$criteria = new InvoiceSearchCriteria(
    $category,
    trim($post->getString('form_name')),
    trim($post->getString('form_pid')),
    trim($post->getString('form_encounter')),
    new InvoiceServiceDates(fixDate($post->getString('form_date'), ''), fixDate($post->getString('form_to_date'), '')),
    fixDate($lastStatementBeforeInput, '')
);
$showNothingDue = $post->getString('form_show_nothing_due') !== '';

$message = '';
$run = StatementRunRequest::fromPost($post);
$searched = $post->getString('form_search') !== '';
if ($run !== null || $searched) {
    CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);
}
if ($run !== null) {
    if ($run->checkedInvoices === []) {
        $message = xl('No invoices were checked.');
    } elseif (!$services->runOnce->claim($post->getString(StatementRunOnce::FORM_FIELD) ?: StatementRunOnce::newId())) {
        // A refresh or Back resubmitted a run that was already done.
        $message = xl('These statements were already sent. Search again to send more.');
    } else {
        // A download hands the file to the browser and ends the request here.
        $message = $services->runHandler->handle(
            StatementInvoices::forRun($run->checkedInvoices),
            $run->delivery,
            $run->withoutUpdate,
            false,
            $category === InvoiceCategory::DuePt,
            $today
        );
    }
    // List again after a run, as the biller left it.
    $searched = true;
}

$rows = [];
$hiddenPatients = 0;
$anyPortal = false;
if ($searched) {
    $condition = $criteria->condition();
    if ($condition === null) {
        $message .= ($message === '' ? '' : ' ') . xl('At least one search parameter is required if you select All.');
    } else {
        [, $invoices] = InvoiceList::find($condition[0], $condition[1], $category);
        $list = $category === InvoiceCategory::DuePt
            ? StatementListRows::net($invoices, $showNothingDue, PatientNetDue::nothingDue(...))
            : StatementListRows::asListed($invoices);
        $hiddenPatients = $list->hiddenPatients;
        // A patient's portal and email flags are looked up once, not for each of their invoices.
        $portalByPid = [];
        $emailableByPid = [];
        $rows = array_map(static function (InvoiceListRow $invoice) use ($services, &$anyPortal, &$portalByPid, &$emailableByPid): array {
            $portal = $portalByPid[$invoice->pid] ??= $services->portal->listsAsPortalUser($invoice->pid);
            $anyPortal = $anyPortal || $portal;
            return [
                'id' => $invoice->id,
                'pid' => $invoice->pid,
                'encounter' => $invoice->encounter,
                'patient' => $invoice->lname . ', ' . $invoice->fname,
                'serviceDate' => $invoice->serviceDate(),
                'lastStatementDate' => $invoice->lastStatementDate,
                'charges' => FormatMoney::getBucks($invoice->charges),
                'chargesSort' => $invoice->charges ?? '',
                'adjustments' => FormatMoney::getBucks($invoice->adjustments),
                'adjustmentsSort' => $invoice->adjustments ?? '',
                'paid' => FormatMoney::getBucks($invoice->paid()),
                'paidSort' => (string) $invoice->paid(),
                'balance' => FormatMoney::getBucks($invoice->balance),
                'balanceSort' => $invoice->balance,
                'dunCount' => $invoice->dunCount,
                'checked' => $invoice->preChecked,
                'inCollections' => $invoice->inCollections,
                'netDue' => $invoice->netDue === null ? null : FormatMoney::getBucks($invoice->netDue),
                'portal' => $portal,
                'emailable' => $emailableByPid[$invoice->pid] ??= $services->email->listsAsEmailable($invoice->pid),
            ];
        }, $list->rows);
    }
}

echo ServiceContainer::getTwig()->render('billing/patient_statements.html.twig', [
    'title' => xl('Patient Statements'),
    'oemrUiSettings' => [
        'heading_title' => xl('Patient Statements'),
        'include_patient_name' => false,
        'expandable' => false,
        'action' => '',
        'action_title' => '',
        'action_href' => '',
        'show_help_icon' => false,
        'help_file_name' => '',
    ],
    'csrfToken' => CsrfUtils::collectCsrfToken(session: $session),
    'runIdField' => StatementRunOnce::FORM_FIELD,
    'runId' => StatementRunOnce::newId(),
    'filters' => [
        'category' => $category->value,
        'name' => $post->getString('form_name'),
        'pid' => $post->getString('form_pid'),
        'encounter' => $post->getString('form_encounter'),
        'serviceFrom' => $post->getString('form_date'),
        'serviceTo' => $post->getString('form_to_date'),
        'lastStatementBefore' => $lastStatementBeforeInput,
        'showNothingDue' => $showNothingDue,
    ],
    'categories' => [
        ['value' => InvoiceCategory::DuePt->value, 'label' => InvoiceCategory::DuePt->label()],
        ['value' => InvoiceCategory::All->value, 'label' => InvoiceCategory::All->label()],
    ],
    'searched' => $searched,
    'rows' => $rows,
    'hiddenPatients' => $hiddenPatients,
    'anyPortal' => $anyPortal,
    'textStatements' => $globals->getString('statement_appearance') !== '1',
    'message' => $message,
    'assetVersion' => $globals->getString('v_js_includes'),
]);
