<?php

/**
 * Standardize the deny-and-exit sequence for legacy scripts
 *
 * Legacy scripts (interface/, library/, portal/) are standalone entry points
 * with no framework exception handler. When access is denied they must
 * manually log, audit, set the HTTP response code, and exit. This class
 * provides a single method that handles all four steps.
 *
 * Modern controllers should continue throwing AccessDeniedException instead.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Acl;

use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Session\SessionWrapperFactory;
use Symfony\Component\HttpFoundation\Response;

class AccessDeniedHelper
{
    /**
     * Log an access denial and terminate the request.
     *
     * @param string                     $comment     Audit log comment describing what was attempted
     * @param string                     $auditEvent  Event name for EventAuditLogger
     * @param Response::HTTP_UNAUTHORIZED|Response::HTTP_FORBIDDEN|Response::HTTP_NOT_FOUND $httpStatus
     * @param AccessDeniedResponseFormat $format      Response body format
     * @param ?(callable(): void)         $beforeExit  Optional callback to run before exiting
     *                                                (e.g. render a template, clean up session)
     */
    public static function deny(
        string $comment,
        string $auditEvent = 'security-access-denied',
        int $httpStatus = Response::HTTP_FORBIDDEN,
        AccessDeniedResponseFormat $format = AccessDeniedResponseFormat::Text,
        ?callable $beforeExit = null,
    ): never {
        $session = SessionWrapperFactory::getInstance()->getWrapper();
        $user = $session->get('authUser', 'unknown');
        $group = $session->get('authProvider', '');

        (new SystemLogger())->warning("Access denied: $comment", [
            'user' => $user,
        ]);

        EventAuditLogger::getInstance()->newEvent(
            $auditEvent,
            $user,
            $group,
            0,
            $comment,
        );

        http_response_code($httpStatus);

        match ($format) {
            AccessDeniedResponseFormat::Json => (static function (): void {
                header('Content-Type: application/json');
                echo json_encode(['error' => xl('Access denied')]);
            })(),
            AccessDeniedResponseFormat::Text => (static function (): void {
                echo xlt('Access denied');
            })(),
        };

        if ($beforeExit !== null) {
            $beforeExit();
        }

        exit;
    }
}
