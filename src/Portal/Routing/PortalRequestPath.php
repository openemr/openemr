<?php

/**
 * The route path of a portal/patient request: the part after portal/patient/, with no
 * leading or trailing slash, for example `api/onsitedocument/57`.
 *
 * The server rewrite passes the path in `_REWRITE_COMMAND`, which HttpRestRequest has already
 * moved into PATH_INFO. The Apache rule (portal/patient/.htaccess) passes the path after
 * portal/patient/; the nginx rule passes the whole path, root included. Without a rewrite (the
 * front controller), the path comes from the request URI. Either way the root is taken off, as
 * the Phreeze router did.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Portal\Routing;

use Symfony\Component\HttpFoundation\Request;

final class PortalRequestPath
{
    /**
     * @param string $rootPath the portal/patient root path, such as `/portal/patient/`
     */
    public static function of(Request $request, string $rootPath): string
    {
        $path = $request->server->getString('PATH_INFO');
        if ($path === '') {
            $uriPath = parse_url($request->server->getString('REQUEST_URI'), PHP_URL_PATH);
            $path = is_string($uriPath) ? $uriPath : '';
        }

        $root = '/' . trim($rootPath, '/');
        $path = '/' . ltrim($path, '/');
        if ($root !== '/' && ($path === $root || str_starts_with($path, $root . '/'))) {
            $path = substr($path, strlen($root));
        }

        return trim($path, '/');
    }
}
