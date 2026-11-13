<?php

/**
 * The portal/patient route path is the same whichever way the request arrives: through the
 * Apache rewrite (the path after portal/patient/), the nginx rewrite (the whole path), or the
 * front controller (the request URI).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Portal;

use OpenEMR\Portal\Routing\PortalRequestPath;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class PortalRequestPathTest extends TestCase
{
    /**
     * @param array<string, string> $server
     */
    #[DataProvider('requestProvider')]
    public function testTheRoutePathIsWhatFollowsTheRoot(array $server, string $rootPath, string $expected): void
    {
        $request = new Request(server: $server);

        $this->assertSame($expected, PortalRequestPath::of($request, $rootPath));
    }

    /**
     * @return array<string, array{array<string, string>, string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function requestProvider(): array
    {
        return [
            'apache rewrite' => [['PATH_INFO' => '/api/onsitedocument/57'], '/portal/patient/', 'api/onsitedocument/57'],
            'apache rewrite, page' => [['PATH_INFO' => '/provider'], '/openemr/portal/patient/', 'provider'],
            'nginx rewrite' => [['PATH_INFO' => '/portal/patient/provider'], '/portal/patient/', 'provider'],
            'nginx rewrite under a web root' => [['PATH_INFO' => '/openemr/portal/patient/api/patient/3'], '/openemr/portal/patient/', 'api/patient/3'],
            'nginx rewrite, the root' => [['PATH_INFO' => '/portal/patient'], '/portal/patient/', ''],
            'nginx rewrite, the root with a slash' => [['PATH_INFO' => '/portal/patient/'], '/portal/patient/', ''],
            'trailing slash' => [['PATH_INFO' => '/portal/patient/onsitedocuments/'], '/portal/patient/', 'onsitedocuments'],
            'front controller' => [['REQUEST_URI' => '/openemr/portal/patient/onsitedocuments?pid=3'], '/openemr/portal/patient/', 'onsitedocuments'],
            'front controller, the root' => [['REQUEST_URI' => '/portal/patient/'], '/portal/patient/', ''],
            'a path that only starts like the root' => [['PATH_INFO' => '/portal/patients/provider'], '/portal/patient/', 'portal/patients/provider'],
            'no path at all' => [[], '/portal/patient/', ''],
        ];
    }
}
