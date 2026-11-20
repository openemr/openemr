<?php

/**
 * Isolated tests for the Email Queue detail JSON controller.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025-2026 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Controllers\Interface\Reports;

use OpenEMR\Controllers\Interface\Reports\EmailQueueDetailController;
use OpenEMR\Reports\Email\EmailQueueService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

#[Group('isolated')]
final class EmailQueueDetailControllerTest extends TestCase
{
    private EmailQueueService&MockObject $service;

    private LoggerInterface&MockObject $logger;

    private Session $session;

    protected function setUp(): void
    {
        $this->service = $this->createMock(EmailQueueService::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->session = new Session(new MockArraySessionStorage());
        $this->session->set('authUserID', 7);
    }

    private function controller(?callable $authorizationGuard = null): EmailQueueDetailController
    {
        return new EmailQueueDetailController(
            $this->service,
            $this->session,
            $this->logger,
            $authorizationGuard ?? static function (): void {
            },
        );
    }

    #[Test]
    public function fetchEmailReturnsJsonPayload(): void
    {
        $email = [
            'id' => 12,
            'sender' => 'a@example.com',
            'recipient' => 'b@example.com',
            'subject' => 'Hello',
            'datetime_queued' => '2026-01-01 00:00:00',
            'sent' => 1,
            'datetime_sent' => '2026-01-01 00:01:00',
            'error' => 0,
            'error_message' => null,
            'datetime_error' => null,
            'template_name' => 'welcome',
        ];
        $this->service->expects(self::once())->method('getEmailById')->with(12)->willReturn($email);

        $response = $this->controller()->fetchEmail(12);
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame(
            ['success' => true, 'email' => $email],
            json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR)
        );
    }

    #[Test]
    public function fetchEmailRejectsInvalidId(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Email ID is required');
        $this->controller()->fetchEmail(0);
    }

    #[Test]
    public function fetchEmailRejectsMissingRow(): void
    {
        $this->service->method('getEmailById')->willReturn(null);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Email not found');
        $this->controller()->fetchEmail(99);
    }

    #[Test]
    public function dispatchActionReturnsSuccess(): void
    {
        $this->service->method('getEmailById')->with(5)->willReturn(['id' => 5]);
        $this->logger->expects(self::never())->method('error');

        $response = $this->controller()->dispatchAction(Request::create('/detail', 'GET', ['id' => '5']));
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame(
            ['success' => true, 'email' => ['id' => 5]],
            json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR)
        );
    }

    #[Test]
    public function dispatchActionReturnsGenericErrorAndLogs(): void
    {
        $this->logger->expects(self::once())->method('error')->with(
            'Email queue detail fetch failed',
            self::callback(static fn(array $context): bool => isset($context['exception']) && $context['exception'] instanceof RuntimeException)
        );

        $response = $this->controller(static function (): void {
            throw new RuntimeException('User not authenticated');
        })->dispatchAction(Request::create('/detail', 'GET', ['id' => '1']));

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertSame(
            ['success' => false, 'error' => 'Unable to load email details.'],
            json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR)
        );
    }

    #[Test]
    public function assertAuthorizedRequiresSessionUser(): void
    {
        $this->session->remove('authUserID');
        $controller = new EmailQueueDetailController($this->service, $this->session, $this->logger);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('User not authenticated');
        $controller->assertAuthorized();
    }
}
