<?php

/**
 * Isolated duplicate OAuth consent submission test.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\RestControllers\Authorization;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\RestControllers\AuthorizationController;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AuthorizationControllerDuplicateConsentTest extends TestCase
{
    public function testMissingAuthorizationSessionReturnsNoContent(): void
    {
        $controllerReflection = new \ReflectionClass(AuthorizationController::class);
        $controller = $controllerReflection->newInstanceWithoutConstructor();

        $session = $this->createMock(SessionInterface::class);
        $session->method('get')
            ->with('authRequestSerial', '')
            ->willReturn('');
        $controllerReflection->getProperty('session')->setValue($controller, $session);
        $controllerReflection->getProperty('authRequestSerial')->setValue($controller, '');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('warning')
            ->with('authorizeUser() ignored a consent submission without an authorization request');
        $controllerReflection->getProperty('logger')->setValue($controller, $logger);

        $response = $controller->authorizeUser(HttpRestRequest::create('/oauth2/default/auth/code'));

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }
}
