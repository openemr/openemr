<?php

/**
 * Database-backed tests for CarePlanController.
 *
 * Only the permission-denied paths live here. denyAccess() renders its message through
 * xlt(), which resolves translations via sqlStatementNoLog() and so cannot run in the
 * isolated suite. Everything that does not touch translation is covered by
 * tests/Tests/Isolated/Controllers/Interface/Forms/CarePlan/.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (C) 2025 Open Plan IT Ltd. <support@openplanit.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Controllers\Interface\Forms\CarePlan;

use OpenEMR\Controllers\Interface\Forms\CarePlan\CarePlanController;
use OpenEMR\Services\Forms\CarePlanFormService;
use OpenEMR\Services\FormService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Twig\Environment;

class CarePlanControllerTest extends TestCase
{
    private CarePlanFormService&MockObject $carePlanFormService;

    private FormService&MockObject $formService;

    private Environment&MockObject $twig;

    private Session $session;

    private LoggerInterface&MockObject $logger;

    protected function setUp(): void
    {
        $this->carePlanFormService = $this->createMock(CarePlanFormService::class);
        $this->formService = $this->createMock(FormService::class);
        $this->twig = $this->createMock(Environment::class);
        $this->session = new Session(new MockArraySessionStorage());
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->session->set('authUser', 'testuser');
    }

    private function controller(): CarePlanController
    {
        return new CarePlanController(
            $this->carePlanFormService,
            $this->formService,
            $this->twig,
            $this->session,
            $this->logger,
            '/openemr',
            'http://localhost',
        );
    }

    #[Test]
    public function testNewActionDeniesAccessWithoutFormPermission(): void
    {
        $this->formService->method('hasFormPermission')->willReturn(false);
        $this->twig->expects($this->never())->method('render');
        $this->logger->expects($this->once())->method('warning');

        $response = $this->controller()->newAction(new Request());

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    #[Test]
    public function testSaveActionDeniesAccessBeforeTouchingTheDatabase(): void
    {
        // The permission gate runs before the CSRF check, so this path is reachable
        // without a POST body -- and nothing may be written when it trips.
        $this->formService->method('hasFormPermission')->willReturn(false);
        $this->carePlanFormService->expects($this->never())->method('registerForm');
        $this->carePlanFormService->expects($this->never())->method('insertCarePlanRow');
        $this->carePlanFormService->expects($this->never())->method('deleteCarePlanRows');

        $response = $this->controller()->saveAction(new Request(), 1);

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    #[Test]
    public function testReportActionDeniesAccessWithoutFormPermission(): void
    {
        $this->formService->method('hasFormPermission')->willReturn(false);
        $this->carePlanFormService->expects($this->never())->method('getCarePlanRows');
        $this->twig->expects($this->never())->method('render');

        $response = $this->controller()->reportAction(42, 7, 3, 1);

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}
