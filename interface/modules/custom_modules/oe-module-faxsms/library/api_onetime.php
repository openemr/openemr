<?php

/**
 * Portal OneTime for API
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c)2023-2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General public License 3
 */

require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\Messaging\SendNotificationEvent;
use OpenEMR\Services\PatientPortalService;

$session = SessionWrapperFactory::getInstance()->getActiveSession();
if (!CsrfUtils::verifyCsrfToken($_REQUEST["csrf_token_form"] ?? '', $session, 'contact-form')) {
    CsrfUtils::csrfNotVerified();
}

if (isset($_REQUEST['sendOneTime'])) {
    try {
        doOnetimeDocumentRequest();
    } catch (\Throwable $e) {
        OneTimeRequestGuard::fail($e);
    }
}

if (isset($_REQUEST['sendInvoiceOneTime'])) {
    try {
        doOnetimeInvoiceRequest();
    } catch (\Throwable $e) {
        OneTimeRequestGuard::fail($e);
    }
}

/**
 * @throws Exception
 */
function doOnetimeInvoiceRequest(): void
{
    $service = new PatientPortalService();

    // A4: bind the effective pid (portal user -> own session pid; staff -> ACL + request pid).
    $reqPid = $_REQUEST['pid'] ?? null;
    $ot_pid = OneTimeRequestGuard::resolvePid($service, (is_int($reqPid) || is_string($reqPid)) ? $reqPid : null);
    $patient = $service->getPatientDetails($ot_pid);
    if (!is_array($patient)) {
        throw new \Exception(xlt("Error! Patient not found."));
    }

    $message = "Dear " . $patient['fname'] . ' ' . $patient['lname'] . ",\n";
    $message .= xlt("Please review your current invoice by clinking the link to automatically redirect to your billing account portal. Use this PIN to complete authorization");
    $session = SessionWrapperFactory::getInstance()->getActiveSession();
    $data = [
        'pid' => $ot_pid,
        'expiry_interval' => "P14D",
        'text_message' => $message,
        'html_message' => "",
        'redirect_url' => OEGlobalsBag::getInstance()->getWebRoot() . "/portal/home.php?site=" . urlencode((string) $session->get('site_id')) . "&landOn=MakePayment",
        'phone' => $patient['phone'] ?? '',
        'email' => $patient['email'] ?? '',
        'actions' => [
            'enforce_onetime_use' => true,
            'enforce_auth_pin' => true,
            'extend_portal_visit' => false,
        ]
    ];

    OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher()
        ->dispatch(new SendNotificationEvent($data['pid'], $data, 'email'), SendNotificationEvent::SEND_NOTIFICATION_SERVICE_UNIVERSAL_ONETIME);
}

/**
 * @throws Exception
 */
function doOnetimeDocumentRequest(): void
{
    $service = new PatientPortalService();
    $details = json_decode((string) $service->getRequest('details'), true);
    if (!is_array($details)) {
        $details = [];
    }
    $content = $service->getRequest('comments');

    // A4: bind the effective pid. For a portal user the session pid wins and
    // any details.pid / form_pid in the request is ignored.
    $detailPid = $details['pid'] ?? $service->getRequest('form_pid');
    $ot_pid = OneTimeRequestGuard::resolvePid($service, (is_int($detailPid) || is_string($detailPid)) ? $detailPid : null);
    $patient = $service->getPatientDetails($ot_pid);
    if (!is_array($patient)) {
        throw new \Exception(xlt("Error! Patient not found."));
    }

    $data = [
        'pid' => $ot_pid,
        'onetime_period' => $details['onetime_period'] ?? 'PT60M',
        'notification_template_name' => $details['notification_template_name'] ?? '',
        'document_id' => $details['id'] ?? 0,
        'audit_id' => $details['audit_id'] ?? 0,
        'document_name' => $details['template_name'] ?? '',
        'notification_method' => $service->getRequest('notification_method', 'both'),
        'phone' => $patient['phone'] ?? '',
        'email' => $patient['email'] ?? '',
        'onetime' => $details['onetime'] ?? 0
    ];

    $rtn = $service->dispatchPortalOneTimeDocumentRequest($ot_pid, $data, $content);
    echo js_escape($rtn);
}

/**
 * One-time request guards, kept in a class so the helpers are not defined in
 * the global function namespace (openemr.noGlobalNsFunctions). Inlined here
 * rather than autoloaded from src/ because this is a direct entry point.
 */
final class OneTimeRequestGuard
{
    /**
     * Resolve the patient id this request is permitted to act on.
     *
     * A4 (portal IDOR): a portal patient may only operate on their OWN record.
     * For a portal user the request-supplied pid is discarded and the
     * authenticated portal pid (from the portal session) is used; back-office
     * staff are ACL-gated and may target the request-supplied pid.
     *
     * @param int|string|null $requestedPid pid as supplied by the request (staff path only)
     */
    public static function resolvePid(PatientPortalService $service, int|string|null $requestedPid): int
    {
        if ($service::isPortalUser()) {
            $session = SessionWrapperFactory::getInstance()->getActiveSession();
            $sessionPid = $session->get('pid');
            $portalPid = is_numeric($sessionPid) ? (int) $sessionPid : 0;
            if ($portalPid <= 0) {
                throw new \Exception(xlt("Error! No authenticated portal patient."));
            }
            return $portalPid;
        }

        if (!$service::verifyAcl()) {
            throw new \Exception(xlt("Error! Not authorised. You must be an authorised portal user or admin."));
        }

        $pid = (int) ($requestedPid ?? 0);
        if ($pid <= 0) {
            throw new \Exception(xlt("Error! Missing patient id."));
        }
        return $pid;
    }

    /**
     * A6/A7: never echo raw exception text to the client. Log the real cause
     * server-side and return a generic, non-revealing message.
     */
    public static function fail(\Throwable $e): never
    {
        \OpenEMR\BC\ServiceContainer::getLogger()->error('api_onetime.php request failed', ['exception' => $e]);
        http_response_code(400);
        die(text(xlt('Error: the request could not be completed.')));
    }
}



