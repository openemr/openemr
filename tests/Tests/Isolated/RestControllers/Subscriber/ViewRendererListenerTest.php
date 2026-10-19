<?php

/**
 * ViewRendererListenerTest
 *
 * Covers the status-preservation contract added to ViewRendererListener
 * — RestControllerHelper::handleProcessingResult() sets the HTTP status
 * via http_response_code() but the JsonResponse / Response constructors
 * default to 200 and silently drop it unless the listener threads it
 * through. 12+ `handleProcessingResult(…, 4xx)` call sites across the
 * REST controllers depend on this.
 *
 * The JSON path is also exercised end-to-end by DrugApiTest /
 * ImmunizationApiTest. These isolated tests also cover the text branch,
 * which no api-tier test reaches today.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\RestControllers\Subscriber;

use OpenEMR\RestControllers\Subscriber\ViewRendererListener;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ViewRendererListenerTest extends TestCase
{
    protected function tearDown(): void
    {
        // http_response_code() lives on the SAPI; reset to the default so
        // the global doesn't bleed into sibling tests.
        http_response_code(200);
    }

    #[Test]
    public function testJsonBranchPreservesNon200StatusFromHttpResponseCode(): void
    {
        // Simulate the common case: a REST controller calls
        // RestControllerHelper::handleProcessingResult($pr, 404) which
        // sets http_response_code(404) before returning its array. The
        // listener wraps the array into a JsonResponse; without the
        // status-preservation fix the response is 200 regardless.
        http_response_code(404);

        $event = $this->buildViewEvent(new Request(), ['validationErrors' => [], 'internalErrors' => [], 'data' => []]);
        (new ViewRendererListener())->onViewEvent($event);

        $response = $event->getResponse();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(404, $response->getStatusCode());
    }

    /**
     * Run this test in a separate process so http_response_code() returns the
     * CLI-default (`false`) rather than inheriting state from a sibling test.
     * The surrounding tearDown() resets the global to 200 after each test, so
     * without the subprocess the listener would see 200 regardless of whether
     * the is_int guard exists — the fallback branch would never actually
     * fire. The subprocess hands the listener an uninitialised SAPI state
     * which is the real-world shape the guard protects against.
     */
    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testJsonBranchDefaultsTo200WhenNoCodeSet(): void
    {
        // No prior http_response_code() call → listener falls back to 200.
        // Guards against a regression that would propagate an unrelated
        // sentinel (e.g. `true`, which is_int-false) into the response.
        $event = $this->buildViewEvent(new Request(), ['data' => []]);
        (new ViewRendererListener())->onViewEvent($event);

        $response = $event->getResponse();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function testTextBranchPreservesNon200StatusFromHttpResponseCode(): void
    {
        // The listener's text branch is selected when
        // getContentTypeFormat() returns 'text'. Nothing in the current
        // codebase registers that mapping (Symfony's defaults use 'txt'
        // for text/plain), so the branch is not reached by api-tier
        // tests. Mock the Request to force the branch so a future
        // caller that DOES wire up a 'text' content-type still gets
        // the status-preservation contract this listener owes.
        http_response_code(404);

        $request = $this->createMock(Request::class);
        $request->method('getContentTypeFormat')->willReturn('text');
        $event = $this->buildViewEvent($request, 'plain-text payload');
        (new ViewRendererListener())->onViewEvent($event);

        $response = $event->getResponse();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertNotInstanceOf(JsonResponse::class, $response);
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('plain-text payload', $response->getContent());
        $this->assertSame('text/plain; charset=UTF-8', $response->headers->get('Content-Type'));
    }

    private function buildViewEvent(Request $request, mixed $controllerResult): ViewEvent
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        return new ViewEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $controllerResult);
    }
}
