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
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\OEGlobalsBag;
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
            AccessDeniedResponseFormat::None => null,
        };

        if ($beforeExit !== null) {
            $beforeExit();
        }

        exit;
    }

    /**
     * Deny access and render the unauthorized template.
     *
     * Convenience method for the common pattern of rendering the unauthorized
     * template when an ACL check fails. Handles logging, auditing, and exits.
     *
     * @param string $comment   Audit log comment describing what was attempted
     * @param string $pageTitle Title to display on the unauthorized page
     * @param string $auditEvent Event name for EventAuditLogger
     */
    public static function denyWithTemplate(
        string $comment,
        string $pageTitle,
        string $auditEvent = 'security-access-denied',
    ): never {
        self::deny(
            $comment,
            $auditEvent,
            Response::HTTP_FORBIDDEN,
            AccessDeniedResponseFormat::None,
            static function () use ($pageTitle): void {
                echo (new TwigContainer(null, OEGlobalsBag::getInstance()->getKernel()))
                    ->getTwig()
                    ->render('core/unauthorized.html.twig', ['pageTitle' => $pageTitle]);
            },
        );
    }

    /**
     * Create an access denied Response for controller patterns.
     *
     * Use this in controller catch blocks that need to return a Response object
     * instead of calling exit(). Handles logging and auditing, then returns
     * a Response with the rendered unauthorized template.
     *
     * @param string $comment    Audit log comment describing what was attempted
     * @param string $pageTitle  Title to display on the unauthorized page
     * @param string $auditEvent Event name for EventAuditLogger
     * @param int    $httpStatus HTTP status code (default 403 Forbidden)
     */
    public static function createDeniedResponse(
        string $comment,
        string $pageTitle,
        string $auditEvent = 'security-access-denied',
        int $httpStatus = Response::HTTP_FORBIDDEN,
    ): Response {
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

        $contents = (new TwigContainer(null, OEGlobalsBag::getInstance()->getKernel()))
            ->getTwig()
            ->render('core/unauthorized.html.twig', ['pageTitle' => $pageTitle]);

        return new Response($contents, $httpStatus);
    }
}
