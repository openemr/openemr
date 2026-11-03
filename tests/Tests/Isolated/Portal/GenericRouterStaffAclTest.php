<?php

/**
 * Verify staff-only routing without reopening patient-scoped portal routes.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Portal;

use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../portal/patient/fwk/libs/verysimple/Phreeze/IRouter.php';
require_once __DIR__ . '/../../../../portal/patient/fwk/libs/verysimple/Phreeze/GenericRouter.php';

final class GenericRouterStaffAclTest extends TestCase
{
    /** @var array<string, array{exists: bool, value: mixed}> */
    private array $savedGlobals = [];

    protected function setUp(): void
    {
        foreach (['bootstrap_pid', 'bootstrap_uri_id', 'bootstrap_register'] as $key) {
            $this->savedGlobals[$key] = [
                'exists' => array_key_exists($key, $GLOBALS),
                'value' => $GLOBALS[$key] ?? null,
            ];
            unset($GLOBALS[$key]);
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->savedGlobals as $key => $saved) {
            if ($saved['exists']) {
                $GLOBALS[$key] = $saved['value'];
            } else {
                unset($GLOBALS[$key]);
            }
        }
    }

    public function testCoreStaffCanReachLiteralStaffRoute(): void
    {
        $router = $this->createRouter([
            'GET:staff' => ['route' => 'Staff.Index', 'p_acl' => 'p_staff', 'p_reg' => false],
        ]);

        $this->assertSame(['Staff', 'Index'], $router->GetRoute('GET:staff'));
    }

    public function testPortalPatientCannotReachLiteralStaffRoute(): void
    {
        OEGlobalsBag::getInstance()->set('bootstrap_pid', 7);
        $router = $this->createRouter([
            'GET:staff' => ['route' => 'Staff.Index', 'p_acl' => 'p_staff', 'p_reg' => false],
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unauthorized');
        $router->GetRoute('GET:staff');
    }

    public function testCoreStaffCanReachWildcardStaffRoute(): void
    {
        $router = $this->createRouter([
            'GET:staff/(:num)' => [
                'route' => 'Staff.Read',
                'params' => ['id' => 1],
                'p_acl' => 'p_staff',
                'p_reg' => false,
            ],
        ]);

        $this->assertSame(['Staff', 'Read'], $router->GetRoute('GET:staff/9'));
    }

    public function testPortalPatientCannotReachWildcardStaffRoute(): void
    {
        OEGlobalsBag::getInstance()->set('bootstrap_pid', 7);
        $router = $this->createRouter([
            'GET:staff/(:num)' => [
                'route' => 'Staff.Read',
                'params' => ['id' => 1],
                'p_acl' => 'p_staff',
                'p_reg' => false,
            ],
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unauthorized');
        $router->GetRoute('GET:staff/9');
    }

    public function testCoreFallbackStillRejectsPatientLimitedRoute(): void
    {
        $router = $this->createRouter([
            'GET:patient/(:num)' => [
                'route' => 'Patient.Read',
                'params' => ['id' => 1],
                'p_acl' => 'p_limited',
                'p_reg' => false,
            ],
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unauthorized');
        $router->GetRoute('GET:patient/9');
    }

    #[RunInSeparateProcess]
    public function testActivityReviewRoutesAreStaffOnly(): void
    {
        class_alias(PortalRouteConfigStub::class, 'GlobalConfig');
        \GlobalConfig::$APP_ROOT = dirname(__DIR__, 4) . '/portal/patient';
        require __DIR__ . '/../../../../portal/patient/_app_config.php';

        foreach ([
            'GET:onsiteactivityviews',
            'GET:api/onsiteactivityviews',
            'GET:api/onsiteactivityview/(:any)',
        ] as $route) {
            $this->assertSame('p_staff', \GlobalConfig::$ROUTE_MAP[$route]['p_acl']);
        }
    }

    /**
     * @param array<string, array<string, mixed>> $routes
     */
    private function createRouter(array $routes): \GenericRouter
    {
        return new \GenericRouter('/', 'Default.Home', $routes);
    }
}

final class PortalRouteConfigStub
{
    public static string $APP_ROOT = '';
    public static string $TEMPLATE_ENGINE = '';
    public static string $TEMPLATE_PATH = '';
    /** @var array<string, array<string, mixed>> */
    public static array $ROUTE_MAP = [];
}
