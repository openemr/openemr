<?php

/**
 * Verify the FHIR resource write switch is enforced before controller dispatch.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Http;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Http\HttpRestRouteHandler;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Core\OEHttpKernel;
use OpenEMR\Services\Globals\GlobalConnectorsEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FhirWriteConfigIsolatedTest extends TestCase
{
    /**
     * @return array<string, array{string, string, ?string, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function routeProvider(): array
    {
        return [
            'missing setting blocks create' => ['POST', '/fhir/Patient', null, true],
            'disabled blocks create' => ['POST', '/fhir/Patient', '0', true],
            'disabled blocks update' => ['PUT', '/fhir/Patient/123', '0', true],
            'disabled blocks patch' => ['PATCH', '/fhir/Patient/123', '0', true],
            'disabled blocks delete' => ['DELETE', '/fhir/Patient/123', '0', true],
            'disabled blocks custom operation' => ['POST', '/fhir/Patient/$custom-write', '0', true],
            'enabled allows create' => ['POST', '/fhir/Patient', '1', false],
            'enabled allows update' => ['PUT', '/fhir/Patient/123', '1', false],
            'enabled allows patch' => ['PATCH', '/fhir/Patient/123', '1', false],
            'enabled allows delete' => ['DELETE', '/fhir/Patient/123', '1', false],
            'disabled allows read' => ['GET', '/fhir/Patient/123', '0', false],
            'disabled allows search' => ['GET', '/fhir/Patient', '0', false],
            'disabled allows POST search' => ['POST', '/fhir/Patient/_search', '0', false],
            'disabled allows metadata' => ['GET', '/fhir/metadata', '0', false],
            'disabled allows docref' => ['POST', '/fhir/DocumentReference/$docref', '0', false],
            'disabled allows bulk export' => ['GET', '/fhir/Patient/$export', '0', false],
            'disabled allows export cancellation' => ['DELETE', '/fhir/$bulkdata-status', '0', false],
            'disabled allows standard API write' => ['POST', '/api/patient', '0', false],
            'disabled allows portal API write' => ['POST', '/portal/patient', '0', false],
        ];
    }

    #[DataProvider('routeProvider')]
    public function testWriteConfiguration(string $method, string $path, ?string $setting, bool $blocked): void
    {
        $globals = $setting === null ? [] : [GlobalConnectorsEnum::REST_FHIR_WRITE->value => $setting];
        $kernel = $this->createMock(OEHttpKernel::class);
        $kernel->method('getGlobalsBag')->willReturn(new OEGlobalsBag($globals));
        $kernel->method('getSystemLogger')->willReturn(new NullLogger());
        $kernel->expects($blocked ? $this->never() : $this->once())
            ->method('getEventDispatcher')->willReturn(new EventDispatcher());

        $request = HttpRestRequest::create('/default' . $path, $method);
        $request->setRequestSite('default');
        $request->setRequestUserRole('users');
        // Local-session requests can skip the authorization listener; the switch must still apply.
        $request->attributes->set('skipAuthorization', true);
        $handler = new HttpRestRouteHandler($kernel);
        $routes = [$method . ' ' . $path => static fn(): string => 'controller called'];

        if ($blocked) {
            try {
                $handler->dispatch($routes, $request);
                $this->fail('Disabled FHIR writes must not reach controller dispatch.');
            } catch (HttpException $exception) {
                $this->assertSame(403, $exception->getStatusCode());
                $this->assertSame('FHIR resource writes are disabled by configuration.', $exception->getMessage());
                $this->assertFalse($request->attributes->has('_controller'));
            }
        } else {
            $this->assertNull($handler->dispatch($routes, $request));
            $controller = $request->attributes->get('_controller');
            $this->assertIsCallable($controller);
            $this->assertSame('controller called', $controller());
        }
    }
}
