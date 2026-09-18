<?php

/**
 * Process-wide holder for the request being served.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Http;

/**
 * Holds the one request object for the current PHP process.
 *
 * OpenEMR's legacy entry points have no constructor to inject a request into,
 * so before this existed each script called
 * {@see HttpRestRequest::createFromGlobals()} for itself. That is worse than
 * merely wasteful: `createFromGlobals()` rewrites `$_GET['_REWRITE_COMMAND']`
 * into `$_SERVER['PATH_INFO']`/`REQUEST_URI`/`QUERY_STRING`, so the second
 * caller in a process is not building from the same input as the first.
 *
 * Entry points that own a request — `apis/dispatch.php`, `oauth2/authorize.php`
 * — call {@see self::set()} with the instance the rest of the stack will
 * mutate (api type, access token scopes, session). Everything downstream,
 * including `interface/globals.php`, calls {@see self::get()} and receives that
 * same instance. For a plain web request nobody has called `set()`, so `get()`
 * builds the request on first use.
 *
 * This is a transitional seam for procedural code, not a target architecture.
 * Classes that can accept a request through their constructor should do that
 * instead — see the patient summary cards in {@see \OpenEMR\Patient\Cards} for
 * the shape to copy.
 */
final class CurrentRequest
{
    private static ?HttpRestRequest $request = null;

    /**
     * The request being served, built from the superglobals on first use.
     */
    public static function get(): HttpRestRequest
    {
        return self::$request ??= HttpRestRequest::createFromGlobals();
    }

    /**
     * Adopt a request built by an entry point.
     *
     * Call this before anything downstream can reach {@see self::get()},
     * otherwise the lazy path wins and the two instances diverge.
     */
    public static function set(HttpRestRequest $request): void
    {
        self::$request = $request;
    }

    /**
     * Whether a request has been established, without building one.
     *
     * Lets callers distinguish "not yet set" from "set", which `get()` cannot
     * express because it always returns a request.
     */
    public static function has(): bool
    {
        return self::$request instanceof HttpRestRequest;
    }

    /**
     * Drop the held request so the next {@see self::get()} rebuilds it.
     *
     * Tests that mutate `$_GET`/`$_POST` between cases need this; nothing in
     * application code should.
     */
    public static function reset(): void
    {
        self::$request = null;
    }
}
