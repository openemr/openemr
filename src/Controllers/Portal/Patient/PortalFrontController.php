<?php

/**
 * The portal/patient front controller's work, after portal/patient/index.php has set up the
 * session: parse the path, check the CSRF token on writes, decide who is calling, dispatch, and
 * turn any failure into a JSON error (for an api/ path) or the error page, never showing the
 * failure itself. Only the failures the portal raises are answered this way (runtime, logic,
 * database and template exceptions); an engine error (\Error) reaches PHP's own error handling.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Portal\Patient;

use Closure;
use Doctrine\DBAL\Exception as DbalException;
use OpenEMR\Common\Csrf\CsrfInvalidException;
use OpenEMR\Common\Csrf\PortalApiCsrfGuard;
use OpenEMR\Common\Session\PortalSessionPidGuard;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Portal\Routing\PortalPrincipal;
use OpenEMR\Portal\Routing\PortalRequestPath;
use OpenEMR\Portal\Routing\PortalRouteDeniedException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig\Error\Error as TwigError;

final readonly class PortalFrontController
{
    /**
     * @param Closure(Request, string, PortalPrincipal): Response $dispatch routes the request
     *        (PortalPatientApp::handle)
     */
    public function __construct(
        private PortalPageRenderer $pages,
        private LoggerInterface $logger,
        private Closure $dispatch,
    ) {
    }

    /**
     * @param string $rootPath the URL path of portal/patient/, with trailing slash
     */
    public function handle(Request $request, SessionInterface $session, OEGlobalsBag $globals, string $rootPath): Response
    {
        // Set once the request is parsed; the error handling reads it to tell an API call from a page.
        $path = '';
        try {
            $path = PortalRequestPath::of($request, $rootPath);

            // Requests that can change data must carry the session's CSRF token.
            PortalApiCsrfGuard::assertValid(
                $request->getRealMethod(),
                $request->headers->get(PortalApiCsrfGuard::HEADER),
                $session,
            );

            $globals->set('bootstrap_register', PortalValues::isFilled($session->get('register')));
            if ($session->has('pid') && $session->has('patient_portal_onsite_two')) {
                // Bootstrap the session pid so downstream code can bind to it.
                $globals->set('bootstrap_pid', $session->get('pid'));
                // Any pid-family key in the request that disagrees with the session pid is denied.
                PortalSessionPidGuard::assertRequestKeysMatchSession($request->request->all(), $request->query->all());
            }

            return ($this->dispatch)($request, $path, self::caller($globals));
        } catch (\RuntimeException | \LogicException | DbalException | TwigError $e) {
            $this->logger->error('Portal request failed', ['exception' => $e]);

            return $this->failure($path, $e);
        }
    }

    /**
     * Who is calling, from the bootstrap values set for the session: a portal patient, a visitor
     * registering for the portal, or (otherwise) a staff user, whom _machine_config.php has
     * already checked for the patientportal/portal ACL.
     */
    public static function caller(OEGlobalsBag $globals): PortalPrincipal
    {
        $pid = filter_var($globals->get('bootstrap_pid'), FILTER_VALIDATE_INT);
        if (is_int($pid) && $pid > 0) {
            return PortalPrincipal::portalPatient($pid);
        }
        if ($globals->getBoolean('bootstrap_register')) {
            return PortalPrincipal::registering();
        }

        return PortalPrincipal::staff();
    }

    /**
     * The reply to a request that failed: JSON for the API (403 when refused, 500 otherwise), an error page for anything else. The failure is logged, never shown.
     */
    private function failure(string $path, \Throwable $failure): Response
    {
        $publicMessage = xl('An unexpected error occurred');
        if (str_starts_with($path, 'api/')) {
            // A missing token or a route the caller may not use is refused; anything else failed.
            $status = $failure instanceof CsrfInvalidException || $failure instanceof PortalRouteDeniedException
                ? Response::HTTP_FORBIDDEN
                : Response::HTTP_INTERNAL_SERVER_ERROR;

            return PortalJson::error($publicMessage, status: $status);
        }
        try {
            return (new PortalErrorPages($this->pages))->failed($publicMessage);
        } catch (\RuntimeException | \LogicException | TwigError $e) {
            $this->logger->error('Portal error template failed', ['exception' => $e]);

            return new Response(
                "<style>* { font-family: verdana, arial, helvetica, sans-serif; }</style>\n<h1>Fatal Error:</h1>\n<h3>" . text($publicMessage) . "</h3>\n",
            );
        }
    }
}
