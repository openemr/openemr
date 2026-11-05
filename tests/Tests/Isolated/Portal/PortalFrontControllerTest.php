<?php

/**
 * The portal/patient front controller: who the caller is, the CSRF check on writes, and how a
 * failure is answered (JSON for api/, the error page otherwise, never the failure itself).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Portal;

use OpenEMR\Controllers\Portal\Patient\PortalFrontController;
use OpenEMR\Controllers\Portal\Patient\PortalPageRenderer;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Portal\Routing\PortalPrincipal;
use OpenEMR\Portal\Routing\PortalRouteDeniedException;
use OpenEMR\Portal\Routing\PrincipalKind;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

final class PortalFrontControllerTest extends TestCase
{
    private const ROOT = '/openemr/portal/patient/';

    private OEGlobalsBag $globals;
    /** @var array<string, mixed> */
    private array $saved = [];
    private RecordingLogger $logger;
    /** @var list<array{string, string, PortalPrincipal}> */
    private array $dispatched = [];

    /**
     * Saves the globals the front controller reads and starts each test with none of them set.
     */
    protected function setUp(): void
    {
        $this->globals = OEGlobalsBag::getInstance();
        foreach (['bootstrap_pid', 'bootstrap_register', 'disable_translation'] as $key) {
            $this->saved[$key] = $this->globals->get($key);
        }
        $this->globals->set('disable_translation', true);
        // The singleton reads $GLOBALS, which remove() leaves alone: clear by setting null.
        $this->globals->set('bootstrap_pid', null);
        $this->globals->set('bootstrap_register', null);
        $this->logger = new RecordingLogger();
    }

    /**
     * Puts the saved globals back.
     */
    protected function tearDown(): void
    {
        foreach ($this->saved as $key => $value) {
            $this->globals->set($key, $value);
        }
    }

    /**
     * A front controller whose dispatch records each request, or throws the given failure.
     */
    private function controller(?\Throwable $failure = null, ?PortalPageRenderer $pages = null): PortalFrontController
    {
        $dispatch = function (Request $request, string $path, PortalPrincipal $caller) use ($failure): Response {
            if ($failure !== null) {
                throw $failure;
            }
            $this->dispatched[] = [$request->getRealMethod(), $path, $caller];

            return new Response('dispatched ' . $path);
        };

        return new PortalFrontController($pages ?? $this->pages(), $this->logger, $dispatch);
    }

    /**
     * A page renderer that names the page and its message, or fails to render.
     */
    private function pages(bool $fail = false): PortalPageRenderer
    {
        return new class ($fail) implements PortalPageRenderer {
            public function __construct(private readonly bool $fail)
            {
            }

            /**
             * The page name and message, or a failure when asked to fail.
             */
            public function render(string $page, array $vars = []): string
            {
                if ($this->fail) {
                    throw new \RuntimeException('template broke');
                }
                $message = $vars['message'] ?? '';

                return $page . ':' . (is_string($message) ? $message : '');
            }
        };
    }

    /**
     * @param array<string, string> $query
     * @param array<string, string> $body
     */
    private function request(string $method, string $path, array $query = [], array $body = []): Request
    {
        return Request::create(self::ROOT . $path, $method, $method === 'GET' ? $query : $body, server: ['PATH_INFO' => '/' . $path]);
    }

    /**
     * @param array<string, mixed> $values
     */
    private function session(array $values = []): Session
    {
        $session = new Session(new MockArraySessionStorage());
        foreach ($values as $key => $value) {
            $session->set($key, $value);
        }

        return $session;
    }

    /**
     * The response body, which must be a string: a false body would make the "does not
     * contain" assertions pass whatever was sent.
     */
    private function content(Response $response): string
    {
        $content = $response->getContent();
        $this->assertIsString($content);

        return $content;
    }

    public function testAStaffReadIsDispatchedWithItsPath(): void
    {
        $response = $this->controller()->handle($this->request('GET', 'api/onsitedocuments'), $this->session(), $this->globals, self::ROOT);

        $this->assertSame('dispatched api/onsitedocuments', $this->content($response));
        $this->assertCount(1, $this->dispatched);
        $this->assertSame(PrincipalKind::Staff, $this->dispatched[0][2]->kind);
        $this->assertFalse($this->globals->getBoolean('bootstrap_register'));
    }

    public function testARegisteringVisitorIsTheRegisteringCaller(): void
    {
        $this->controller()->handle($this->request('GET', 'patientdata'), $this->session(['register' => true]), $this->globals, self::ROOT);

        $this->assertTrue($this->globals->getBoolean('bootstrap_register'));
        $this->assertSame(PrincipalKind::Registering, $this->dispatched[0][2]->kind);
    }

    public function testAPortalPatientIsBoundToTheSessionPid(): void
    {
        $session = $this->session(['pid' => 42, 'patient_portal_onsite_two' => 1]);

        $this->controller()->handle($this->request('GET', 'patientdata', ['pid' => '42']), $session, $this->globals, self::ROOT);

        $this->assertSame(42, $this->globals->get('bootstrap_pid'));
        $caller = $this->dispatched[0][2];
        $this->assertSame(PrincipalKind::PortalPatient, $caller->kind);
        $this->assertSame(42, $caller->patientPid());
    }

    public function testTheCallerComesFromTheBootstrapValues(): void
    {
        $this->assertSame(PrincipalKind::Staff, PortalFrontController::caller($this->globals)->kind);

        $this->globals->set('bootstrap_register', true);
        $this->assertSame(PrincipalKind::Registering, PortalFrontController::caller($this->globals)->kind);

        $this->globals->set('bootstrap_pid', '7');
        $this->assertSame(7, PortalFrontController::caller($this->globals)->patientPid(), 'a patient pid wins over registration');

        $this->globals->set('bootstrap_pid', '0');
        $this->assertSame(PrincipalKind::Registering, PortalFrontController::caller($this->globals)->kind, 'no pid is not a patient');
    }

    public function testAWriteWithoutTheCsrfTokenIsAJsonErrorForTheApi(): void
    {
        $response = $this->controller()->handle($this->request('POST', 'api/onsitedocument'), $this->session(), $this->globals, self::ROOT);

        $this->assertSame([], $this->dispatched, 'nothing is dispatched');
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode(), 'refused, not an authentication failure');
        $this->assertStringContainsString('application/json', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('An unexpected error occurred', $this->content($response));
        $this->assertStringNotContainsString('CSRF', $this->content($response), 'the failure is not shown');
        $this->assertSame('Portal request failed', $this->logger->records[0][1]);
    }

    public function testTheCsrfCheckUsesTheRealMethodNotAnOverride(): void
    {
        // A POST that claims to be a GET still needs the token.
        $request = $this->request('POST', 'api/onsitedocument');
        $request->headers->set('X-HTTP-Method-Override', 'GET');
        $this->assertSame('GET', $request->getMethod(), 'Symfony applies the override');

        $response = $this->controller()->handle($request, $this->session(), $this->globals, self::ROOT);

        $this->assertSame([], $this->dispatched);
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function testAnEngineErrorIsNotSwallowed(): void
    {
        $this->expectException(\TypeError::class);

        $this->controller(new \TypeError('bug'))->handle($this->request('GET', 'patientdata'), $this->session(), $this->globals, self::ROOT);
    }

    public function testAnApiFailureIsAServerError(): void
    {
        $response = $this->controller(new \RuntimeException('SELECT secret'))->handle($this->request('GET', 'api/onsitedocuments'), $this->session(), $this->globals, self::ROOT);

        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertStringNotContainsString('secret', $this->content($response));
    }

    public function testAnApiRouteTheCallerMayNotUseIsRefused(): void
    {
        $response = $this->controller(new PortalRouteDeniedException())->handle($this->request('GET', 'api/onsiteactivityviews'), $this->session(), $this->globals, self::ROOT);

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function testAPageFailureShowsTheErrorPage(): void
    {
        $response = $this->controller(new \RuntimeException('SELECT secret'))->handle($this->request('GET', 'patientdata'), $this->session(), $this->globals, self::ROOT);

        $this->assertSame('DefaultErrorFatal:An unexpected error occurred', $this->content($response));
        $this->assertStringNotContainsString('secret', $this->content($response));
    }

    public function testAFailingErrorPageFallsBackToPlainHtml(): void
    {
        $controller = $this->controller(new \RuntimeException('boom'), $this->pages(fail: true));

        $response = $controller->handle($this->request('GET', 'patientdata'), $this->session(), $this->globals, self::ROOT);

        $this->assertStringContainsString('<h1>Fatal Error:</h1>', $this->content($response));
        $this->assertStringContainsString('An unexpected error occurred', $this->content($response));
        $this->assertSame(['Portal request failed', 'Portal error template failed'], array_column($this->logger->records, 1));
    }
}
