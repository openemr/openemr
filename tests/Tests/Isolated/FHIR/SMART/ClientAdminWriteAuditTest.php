<?php

/**
 * Verify administrator audit records for clients requesting FHIR writes.
 *
 * @package OpenEMR
 * @link https://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\FHIR\SMART;

use DateTimeImmutable;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\Audit\Event;
use OpenEMR\Common\Logging\Audit\SinkInterface;
use OpenEMR\Common\Logging\AuditConfig;
use OpenEMR\Common\Logging\BreakglassCheckerInterface;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\FHIR\SMART\ActionUrlBuilder;
use OpenEMR\FHIR\SMART\ClientAdminController;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

require_once dirname(__DIR__, 5) . '/library/translation.inc.php';

final class ClientAdminWriteAuditTest extends TestCase
{
    /**
     * @return array<string, array{list<string>, bool, bool, bool}>
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function actionsProvider(): array
    {
        return [
            'enable FHIR write client' => [['api:fhir', 'user/Patient.write'], false, true, true],
            'disable FHIR write client' => [['api:fhir', 'user/Patient.write'], true, false, true],
            'write client without API marker' => [['system/Observation.u'], false, true, true],
            'standard API write client' => [['api:oemr', 'user/patient.write'], false, true, false],
            'FHIR read client' => [['api:fhir', 'user/Patient.read'], false, true, false],
        ];
    }

    /** @param list<string> $scopes */
    #[DataProvider('actionsProvider')]
    public function testSuccessfulActionRecordsAdministratorAndTime(array $scopes, bool $previous, bool $enabled, bool $expectAudit): void
    {
        $client = $this->createClient($scopes, $previous);
        $repository = $this->createMock(ClientRepository::class);
        $repository->expects($this->once())->method('saveIsEnabled')->with($client, $enabled)->willReturn(true);
        $sink = $this->createMock(SinkInterface::class);
        if ($expectAudit) {
            $sink->expects($this->once())->method('record')->with(self::callback(
                static function (Event $event) use ($previous, $enabled, $client): bool {
                    self::assertSame('oauth2', $event->event);
                    self::assertSame('admin-test', $event->user);
                    self::assertSame('Default', $event->group);
                    self::assertSame('2026-10-10 20:00:00', $event->current_datetime);
                    self::assertSame(1, $event->success);
                    self::assertSame([
                        'action' => $enabled ? 'enable_fhir_write_client' : 'disable_fhir_write_client',
                        'client_id' => 'test-write-client',
                        'client_name' => 'Test client',
                        'admin_user_id' => 42,
                        'previously_enabled' => $previous,
                        'enabled' => $enabled,
                        'fhir_write_scopes' => $client->getFhirWriteScopes(),
                    ], json_decode(base64_decode($event->comments), true, 512, JSON_THROW_ON_ERROR));
                    return true;
                }
            ));
        } else {
            $sink->expects($this->never())->method('record');
        }
        $controller = $this->createController($repository, $sink);
        $response = new ReflectionMethod(ClientAdminController::class, 'handleEnabledAction');
        $result = $response->invoke($controller, $client, $enabled, 'Saved client');
        self::assertInstanceOf(Response::class, $result);
        self::assertSame(Response::HTTP_TEMPORARY_REDIRECT, $result->getStatusCode());
        self::assertSame($enabled, $client->isEnabled());
        self::assertSame($scopes, $client->getScopes());
    }

    public function testFailedSaveDoesNotRecordSuccessfulApproval(): void
    {
        $client = $this->createClient(['user/Patient.write'], false);
        $repository = $this->createMock(ClientRepository::class);
        $repository->method('saveIsEnabled')->willThrowException(new RuntimeException('Save failed'));
        $sink = $this->createMock(SinkInterface::class);
        $sink->expects($this->never())->method('record');
        $controller = $this->createController($repository, $sink);
        $bag = OEGlobalsBag::getInstance();
        $hadSetting = $bag->has('disable_translation');
        $previous = $bag->getBoolean('disable_translation');
        $bag->set('disable_translation', true);
        try {
            $method = new ReflectionMethod(ClientAdminController::class, 'handleEnabledAction');
            $result = $method->invoke($controller, $client, true, 'Saved client');
            self::assertInstanceOf(Response::class, $result);
            self::assertStringContainsString(urlencode('Client failed to save'), $result->headers->get('Location') ?? '');
        } finally {
            if ($hadSetting) {
                $bag->set('disable_translation', $previous);
            } else {
                $bag->remove('disable_translation');
            }
        }
    }

    /** @param list<string> $scopes */
    private function createClient(array $scopes, bool $enabled): ClientEntity
    {
        $client = new ClientEntity();
        $client->setIdentifier('test-write-client');
        $client->setName('Test client');
        $client->setScopes($scopes);
        $client->setIsEnabled($enabled);
        return $client;
    }

    private function createController(ClientRepository $repository, SinkInterface $sink): ClientAdminController
    {
        $session = new Session(new MockArraySessionStorage());
        $session->set('authUser', 'admin-test');
        $session->set('authProvider', 'Default');
        $session->set('authUserID', 42);
        CsrfUtils::setupCsrfKey($session);
        $clock = $this->createMock(ClockInterface::class);
        $clock->method('now')->willReturn(new DateTimeImmutable('2026-10-10 20:00:00'));
        $auditLogger = new EventAuditLogger(
            $sink,
            $session,
            new AuditConfig(true, false, false, false, []),
            $this->createMock(BreakglassCheckerInterface::class),
            $clock
        );
        // Isolate enable/disable from the constructor's template/kernel setup.
        $controller = (new ReflectionClass(ClientAdminController::class))->newInstanceWithoutConstructor();
        foreach (['session' => $session, 'clientRepo' => $repository, 'actionURL' => 'admin-client.php', 'actionUrlBuilder' => new ActionUrlBuilder($session, 'admin-client.php')] as $property => $value) {
            (new ReflectionProperty(ClientAdminController::class, $property))->setValue($controller, $value);
        }
        $controller->setEventAuditLogger($auditLogger);
        return $controller;
    }
}
