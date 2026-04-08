<?php

/**
 * EncounterRestControllerTest - Isolated tests for EncounterRestController
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\RestControllers;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\RestControllers\EncounterRestController;
use OpenEMR\Services\EncounterService;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class EncounterRestControllerTest extends TestCase
{
    private EncounterService&MockObject $encounterService;
    private SessionInterface&MockObject $session;
    private EncounterRestController $controller;

    protected function setUp(): void
    {
        // Prevent code_types.inc.php from issuing DB queries when autoloading
        // BaseService (which EncounterService extends).
        if (!defined('OPENEMR_STATIC_ANALYSIS')) {
            define('OPENEMR_STATIC_ANALYSIS', true);
        }

        $this->encounterService = $this->createMock(EncounterService::class);
        $this->session = $this->createMock(SessionInterface::class);
        $this->controller = new EncounterRestController($this->session, $this->encounterService);
    }

    // ---------------------------------------------------------------
    // post()
    // ---------------------------------------------------------------

    public function testPostSuccess(): void
    {
        $result = new ProcessingResult();
        $result->addData(['encounter' => 1, 'uuid' => 'test-uuid']);

        $this->encounterService->expects($this->once())
            ->method('insertEncounter')
            ->willReturn($result);

        $request = $this->createMockRequest('testUser', 'testGroup');

        $response = $this->controller->post('patient-uuid', ['reason' => 'visit'], $request);

        $this->assertArrayHasKey('data', $response);
        $this->assertSame(['encounter' => 1, 'uuid' => 'test-uuid'], $response['data']);
        $this->assertEmpty($response['validationErrors']);
        $this->assertEmpty($response['internalErrors']);
    }

    public function testPostForwardsSessionData(): void
    {
        $result = new ProcessingResult();
        $result->addData(['encounter' => 1]);

        $this->encounterService->expects($this->once())
            ->method('insertEncounter')
            ->with(
                'patient-uuid',
                $this->callback(fn(array $data): bool => $data['user'] === 'admin' && $data['group'] === 'Default')
            )
            ->willReturn($result);

        $request = $this->createMockRequest('admin', 'Default');

        $this->controller->post('patient-uuid', ['reason' => 'checkup'], $request);
    }

    public function testPostValidationError(): void
    {
        $result = new ProcessingResult();
        $result->setValidationMessages(['pc_catid' => ['required']]);

        $this->encounterService->expects($this->once())
            ->method('insertEncounter')
            ->willReturn($result);

        $request = $this->createMockRequest('testUser', 'testGroup');

        $response = $this->controller->post('patient-uuid', [], $request);

        $this->assertNotEmpty($response['validationErrors']);
        $this->assertEmpty($response['data']);
    }

    public function testPostInternalError(): void
    {
        $result = new ProcessingResult();
        $result->addInternalError('database failure');

        $this->encounterService->expects($this->once())
            ->method('insertEncounter')
            ->willReturn($result);

        $request = $this->createMockRequest('testUser', 'testGroup');

        $response = $this->controller->post('patient-uuid', [], $request);

        $this->assertNotEmpty($response['internalErrors']);
        $this->assertEmpty($response['data']);
    }

    // ---------------------------------------------------------------
    // put()
    // ---------------------------------------------------------------

    public function testPutSuccess(): void
    {
        $result = new ProcessingResult();
        $result->addData(['id' => '1', 'reason' => 'updated']);

        $this->encounterService->expects($this->once())
            ->method('updateEncounter')
            ->with('patient-uuid', 'encounter-uuid', ['reason' => 'updated'])
            ->willReturn($result);

        $response = $this->controller->put('patient-uuid', 'encounter-uuid', ['reason' => 'updated']);

        $this->assertSame(['id' => '1', 'reason' => 'updated'], $response['data']);
        $this->assertEmpty($response['validationErrors']);
    }

    public function testPutValidationError(): void
    {
        $result = new ProcessingResult();
        $result->setValidationMessages(['class_code' => ['invalid']]);

        $this->encounterService->expects($this->once())
            ->method('updateEncounter')
            ->willReturn($result);

        $response = $this->controller->put('patient-uuid', 'encounter-uuid', []);

        $this->assertNotEmpty($response['validationErrors']);
        $this->assertEmpty($response['data']);
    }

    // ---------------------------------------------------------------
    // getOne()
    // ---------------------------------------------------------------

    public function testGetOneSuccess(): void
    {
        $result = new ProcessingResult();
        $result->addData(['id' => '1', 'uuid' => 'encounter-uuid']);

        $this->encounterService->expects($this->once())
            ->method('getEncounter')
            ->with('encounter-uuid', 'patient-uuid')
            ->willReturn($result);

        $response = $this->controller->getOne('patient-uuid', 'encounter-uuid');

        $this->assertSame(['id' => '1', 'uuid' => 'encounter-uuid'], $response['data']);
    }

    public function testGetOneNotFound(): void
    {
        $result = new ProcessingResult();

        $this->encounterService->expects($this->once())
            ->method('getEncounter')
            ->willReturn($result);

        $response = $this->controller->getOne('patient-uuid', 'missing-uuid');

        $this->assertEmpty($response['data']);
    }

    // ---------------------------------------------------------------
    // getAll()
    // ---------------------------------------------------------------

    public function testGetAllSuccess(): void
    {
        $result = new ProcessingResult();
        $result->addData(['id' => '1']);
        $result->addData(['id' => '2']);

        $this->encounterService->expects($this->once())
            ->method('search')
            ->with([], true, 'patient-uuid')
            ->willReturn($result);

        $response = $this->controller->getAll('patient-uuid');

        $this->assertIsArray($response['data']);
        $this->assertCount(2, $response['data']);
    }

    public function testGetAllEmpty(): void
    {
        $result = new ProcessingResult();

        $this->encounterService->expects($this->once())
            ->method('search')
            ->willReturn($result);

        $response = $this->controller->getAll('patient-uuid');

        $this->assertEmpty($response['data']);
    }

    // ---------------------------------------------------------------
    // postVital()
    // ---------------------------------------------------------------

    public function testPostVitalSuccess(): void
    {
        $validationResult = $this->createValidationSuccess();

        $this->encounterService->expects($this->once())
            ->method('validateVital')
            ->willReturn($validationResult);

        $this->encounterService->expects($this->once())
            ->method('insertVital')
            ->with('1', '2', ['bps' => '120'])
            ->willReturn([10, 20]);

        $response = $this->controller->postVital('1', '2', ['bps' => '120']);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(201, $response->getStatusCode());
    }

    public function testPostVitalValidationFailure(): void
    {
        $validationResult = $this->createValidationFailure(['bps' => 'required']);

        $this->encounterService->expects($this->once())
            ->method('validateVital')
            ->willReturn($validationResult);

        $this->encounterService->expects($this->never())
            ->method('insertVital');

        $response = $this->controller->postVital('1', '2', []);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('bps', $response);
    }

    // ---------------------------------------------------------------
    // putVital()
    // ---------------------------------------------------------------

    public function testPutVitalSuccess(): void
    {
        $validationResult = $this->createValidationSuccess();

        $this->encounterService->expects($this->once())
            ->method('validateVital')
            ->willReturn($validationResult);

        $this->encounterService->expects($this->once())
            ->method('updateVital')
            ->with('1', '2', '3', ['bps' => '130'])
            ->willReturn(true);

        $response = $this->controller->putVital('1', '2', '3', ['bps' => '130']);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testPutVitalValidationFailure(): void
    {
        $validationResult = $this->createValidationFailure(['bps' => 'required']);

        $this->encounterService->expects($this->once())
            ->method('validateVital')
            ->willReturn($validationResult);

        $this->encounterService->expects($this->never())
            ->method('updateVital');

        $response = $this->controller->putVital('1', '2', '3', []);

        $this->assertIsArray($response);
    }

    // ---------------------------------------------------------------
    // getVitals() / getVital()
    // ---------------------------------------------------------------

    public function testGetVitalsSuccess(): void
    {
        $this->encounterService->expects($this->once())
            ->method('getVitals')
            ->with('1', '2')
            ->willReturn([['bps' => '120']]);

        $response = $this->controller->getVitals('1', '2');

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testGetVitalSuccess(): void
    {
        $this->encounterService->expects($this->once())
            ->method('getVital')
            ->with('1', '2', '3')
            ->willReturn(['bps' => '120']);

        $response = $this->controller->getVital('1', '2', '3');

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testGetVitalNotFound(): void
    {
        $this->encounterService->expects($this->once())
            ->method('getVital')
            ->with('1', '2', '99')
            ->willReturn(null);

        $response = $this->controller->getVital('1', '2', '99');

        $this->assertSame(404, $response->getStatusCode());
    }

    // ---------------------------------------------------------------
    // postSoapNote()
    // ---------------------------------------------------------------

    public function testPostSoapNoteSuccess(): void
    {
        $validationResult = $this->createValidationSuccess();

        $this->encounterService->expects($this->once())
            ->method('validateSoapNote')
            ->willReturn($validationResult);

        $this->encounterService->expects($this->once())
            ->method('insertSoapNote')
            ->with('1', '2', ['subjective' => 'pain'])
            ->willReturn([10, 20]);

        $response = $this->controller->postSoapNote('1', '2', ['subjective' => 'pain']);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(201, $response->getStatusCode());
    }

    public function testPostSoapNoteValidationFailure(): void
    {
        $validationResult = $this->createValidationFailure(['subjective' => 'required']);

        $this->encounterService->expects($this->once())
            ->method('validateSoapNote')
            ->willReturn($validationResult);

        $this->encounterService->expects($this->never())
            ->method('insertSoapNote');

        $response = $this->controller->postSoapNote('1', '2', []);

        $this->assertIsArray($response);
    }

    // ---------------------------------------------------------------
    // putSoapNote()
    // ---------------------------------------------------------------

    public function testPutSoapNoteSuccess(): void
    {
        $validationResult = $this->createValidationSuccess();

        $this->encounterService->expects($this->once())
            ->method('validateSoapNote')
            ->willReturn($validationResult);

        $this->encounterService->expects($this->once())
            ->method('updateSoapNote')
            ->with('1', '2', '3', ['subjective' => 'updated'])
            ->willReturn(true);

        $response = $this->controller->putSoapNote('1', '2', '3', ['subjective' => 'updated']);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testPutSoapNoteValidationFailure(): void
    {
        $validationResult = $this->createValidationFailure(['plan' => 'required']);

        $this->encounterService->expects($this->once())
            ->method('validateSoapNote')
            ->willReturn($validationResult);

        $this->encounterService->expects($this->never())
            ->method('updateSoapNote');

        $response = $this->controller->putSoapNote('1', '2', '3', []);

        $this->assertIsArray($response);
    }

    // ---------------------------------------------------------------
    // getSoapNotes() / getSoapNote()
    // ---------------------------------------------------------------

    public function testGetSoapNotesSuccess(): void
    {
        $this->encounterService->expects($this->once())
            ->method('getSoapNotes')
            ->with('1', '2')
            ->willReturn([['subjective' => 'pain']]);

        $response = $this->controller->getSoapNotes('1', '2');

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testGetSoapNoteSuccess(): void
    {
        $this->encounterService->expects($this->once())
            ->method('getSoapNote')
            ->with('1', '2', '3')
            ->willReturn(['subjective' => 'pain']);

        $response = $this->controller->getSoapNote('1', '2', '3');

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testGetSoapNoteNotFound(): void
    {
        $this->encounterService->expects($this->once())
            ->method('getSoapNote')
            ->with('1', '2', '99')
            ->willReturn(null);

        $response = $this->controller->getSoapNote('1', '2', '99');

        $this->assertSame(404, $response->getStatusCode());
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function createMockRequest(string $user, string $group): HttpRestRequest
    {
        $requestSession = $this->createMock(SessionInterface::class);
        $requestSession->method('get')
            ->willReturnMap([
                ['authUser', null, $user],
                ['authProvider', null, $group],
            ]);

        $request = $this->createMock(HttpRestRequest::class);
        $request->method('getSession')->willReturn($requestSession);

        return $request;
    }

    /**
     * Create a validation result that validationHandler() treats as valid.
     *
     * validationHandler() uses property_exists() to check for an 'isValid' property,
     * then calls isValid() as a method.
     */
    private function createValidationSuccess(): object
    {
        return new class () {
            public bool $isValid = true;

            public function isValid(): bool
            {
                return true;
            }
        };
    }

    /**
     * Create a validation result that validationHandler() treats as invalid.
     *
     * validationHandler() checks property_exists() for 'getValidationMessages' as a
     * property (not method), so that check is false for real objects. It then falls
     * through to getMessages().
     *
     * @param array<string, string> $messages
     */
    private function createValidationFailure(array $messages): object
    {
        return new class ($messages) {
            public bool $isValid;

            /** @param array<string, string> $messages */
            public function __construct(private readonly array $messages)
            {
                $this->isValid = false;
            }

            public function isValid(): bool
            {
                return false;
            }

            /** @return array<string, string> */
            public function getMessages(): array
            {
                return $this->messages;
            }
        };
    }
}
