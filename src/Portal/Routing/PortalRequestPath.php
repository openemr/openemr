<?php

/**
 * The route path of a portal/patient request: the part after portal/patient/, with no
 * leading or trailing slash, for example `api/onsitedocument/57`.
 *
 * With the .htaccess or nginx rewrite, HttpRestRequest has already moved the rewritten path into
 * PATH_INFO. Without it (the front controller), the path comes from the request URI, less the
 * portal/patient root.
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
            if ($rootPath !== '' && str_starts_with($path, $rootPath)) {
                $path = substr($path, strlen($rootPath));
            }
        }

        return trim($path, '/');
    }
}
