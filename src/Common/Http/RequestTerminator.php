<?php

/**
 * Ends the current request with a real HTTP response instead of die()/exit()
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Http;

use Closure;
use Symfony\Component\HttpFoundation\Response;

/**
 * Legacy scripts end error paths with die("message"), which reports HTTP 200
 * and process exit code 0 — invisible to browsers, proxies, monitoring, and
 * anything else that reads status codes. This helper sends a real Response
 * first, then hands control to a terminator that never returns.
 *
 * The terminator receives the HTTP status code of the sent response and
 * decides how to end the request. The default exits the process: nonzero for
 * client and server error statuses (4xx/5xx), zero otherwise. Tests inject a
 * throwing closure so request-ending code paths remain executable under
 * PHPUnit instead of killing the test runner.
 *
 * This class exists to retrofit legacy procedural scripts. Do not reach for
 * it in new code — new endpoints should build and return a Response through
 * a controller and let the front controller send it.
 */
final readonly class RequestTerminator
{
    /** @var Closure(int): never */
    private Closure $terminator;

    /**
     * @param (Closure(int): never)|null $terminator receives the HTTP status
     *        code of the sent response and must end the request
     */
    public function __construct(?Closure $terminator = null)
    {
        $this->terminator = $terminator ?? static function (int $statusCode): never {
            // @codeCoverageIgnoreStart -- exiting would kill the test runner
            exit(self::defaultExitCode($statusCode));
            // @codeCoverageIgnoreEnd
        };
    }

    /**
     * The process exit code the default terminator uses for a status code:
     * 1 for client and server errors (4xx/5xx), 0 otherwise.
     */
    public static function defaultExitCode(int $statusCode): int
    {
        return $statusCode >= Response::HTTP_BAD_REQUEST ? 1 : 0;
    }

    /**
     * Send the response, then terminate the request.
     */
    public function respond(Response $response): never
    {
        $response->send();
        ($this->terminator)($response->getStatusCode());
    }

    /**
     * Convenience wrapper for the common case: an error body with status.
     */
    public function error(int $statusCode, string $message): never
    {
        $this->respond(new Response($message, $statusCode));
    }
}
