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
 * The default terminator exits the process: nonzero when the response status
 * is an error (>= 400), zero otherwise. Tests inject a throwing closure so
 * request-ending code paths remain executable under PHPUnit instead of
 * killing the test runner.
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
     * @param (Closure(int): never)|null $terminator receives the intended
     *        process exit code: 1 when the response status is >= 400, else 0
     */
    public function __construct(?Closure $terminator = null)
    {
        $this->terminator = $terminator ?? static function (int $exitCode): never {
            // @codeCoverageIgnoreStart -- exiting would kill the test runner
            exit($exitCode);
            // @codeCoverageIgnoreEnd
        };
    }

    /**
     * Send the response, then terminate the request.
     */
    public function respond(Response $response): never
    {
        $response->send();
        ($this->terminator)((int) ($response->getStatusCode() >= 400));
    }

    /**
     * Convenience wrapper for the common case: an error body with status.
     */
    public function error(int $statusCode, string $message): never
    {
        $this->respond(new Response($message, $statusCode));
    }
}
