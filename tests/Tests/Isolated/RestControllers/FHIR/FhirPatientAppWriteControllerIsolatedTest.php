<?php

/**
 * Isolated tests for a patient app's FHIR writes through the REST layer: held on create, changed
 * or withdrawn on update, and read back only by the app that wrote them.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\RestControllers\FHIR;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\RestControllers\FHIR\FhirPatientAppWriteController;
use OpenEMR\Services\PatientReview\FhirWrite\FhirWriteReviewHandler;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWritableType;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteClientType;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteHold;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteMode;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWritePolicy;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteReadBack;
use OpenEMR\Services\PatientReview\ReviewStatus;
use OpenEMR\Services\PatientReview\ReviewType;
use OpenEMR\Tests\Isolated\Services\PatientReview\FhirWrite\PatientWriteQueueFixture;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class FhirPatientAppWriteControllerIsolatedTest extends TestCase
{
    use PatientWriteQueueFixture;

    private const FHIR_URL = 'https://ehr.example.org/apis/default/fhir';
    private const PUBLIC_CLIENT = 'client-public';
    private const PORTAL_USERNAME = 'ann.seven';

    protected function setUp(): void
    {
        $this->setUpQueue();
    }

    private function controller(PatientWriteMode $mode = PatientWriteMode::ConfidentialAndPublic): FhirPatientAppWriteController
    {
        return new FhirPatientAppWriteController(
            PatientWritableType::QuestionnaireResponse,
            new PatientWriteHold($this->queue, new PatientWritePolicy($mode, [PatientWritableType::QuestionnaireResponse]), new FhirWriteReviewHandler([])),
            new PatientWriteReadBack($this->queue),
            static fn (string $clientId): ?PatientWriteClientType => match ($clientId) {
                self::CLIENT => PatientWriteClientType::Confidential,
                self::PUBLIC_CLIENT => PatientWriteClientType::Public,
                default => null,
            },
            self::FHIR_URL,
            static fn (int $pid): ?string => $pid === self::PID ? self::PORTAL_USERNAME : null,
        );
    }

    /**
     * A request as the bearer-token check leaves it for a patient's SMART app.
     */
    private function patientRequest(string $clientId = self::CLIENT): HttpRestRequest
    {
        $request = new HttpRestRequest();
        $request->setPatientRequest(true);
        $request->setPatientUuidString(self::PATIENT);
        $request->setClientId($clientId);
        $request->setRequestUser(self::PATIENT, ['pid' => (string) self::PID, 'fullname' => 'Ann Seven']);
        return $request;
    }

    /**
     * @return array<string, mixed>
     */
    private static function decode(Response $response): array
    {
        $decoded = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertIsArray($decoded);
        /** @var array<string, mixed> $decoded */
        return $decoded;
    }

    private function createdId(): string
    {
        $response = $this->controller()->create($this->patientRequest(), $this->resourceOf(PatientWritableType::QuestionnaireResponse));
        $id = self::decode($response)['id'] ?? null;
        self::assertIsString($id);
        return $id;
    }

    public function testACreateIsHeldAndAnsweredWithLocation(): void
    {
        $response = $this->controller()->create($this->patientRequest(), $this->resourceOf(PatientWritableType::QuestionnaireResponse));

        $this->assertSame(201, $response->getStatusCode());
        $body = self::decode($response);
        $this->assertIsString($body['id']);
        $this->assertSame(self::FHIR_URL . '/QuestionnaireResponse/' . $body['id'], $response->headers->get('Location'));
        $this->assertSame(['reference' => 'Patient/' . self::PATIENT], $body['subject']);
        $this->assertStringContainsString(PatientWriteReadBack::HELD_TAG_CODE, (string) $response->getContent());
        $this->assertSame(ReviewStatus::Pending, $this->queue->findByUuid($body['id'])?->status);
        $this->assertSame(self::PORTAL_USERNAME, array_slice($this->trail->entries, -1)[0]['actor'], 'audited by portal login, not display name');
    }

    public function testAPatientWithoutAPortalLoginIsAuditedByUuid(): void
    {
        $controller = new FhirPatientAppWriteController(
            PatientWritableType::QuestionnaireResponse,
            new PatientWriteHold($this->queue, new PatientWritePolicy(PatientWriteMode::ConfidentialAndPublic, [PatientWritableType::QuestionnaireResponse]), new FhirWriteReviewHandler([])),
            new PatientWriteReadBack($this->queue),
            static fn (string $clientId): PatientWriteClientType => PatientWriteClientType::Confidential,
            self::FHIR_URL,
            static fn (int $pid): ?string => null,
        );

        $this->assertSame(201, $controller->create($this->patientRequest(), $this->resourceOf(PatientWritableType::QuestionnaireResponse))->getStatusCode());
        $this->assertSame(self::PATIENT, array_slice($this->trail->entries, -1)[0]['actor']);
    }

    public function testAPublicAppIsRefusedWhenTheModeAllowsOnlyConfidentialApps(): void
    {
        $response = $this->controller(PatientWriteMode::Confidential)
            ->create($this->patientRequest(self::PUBLIC_CLIENT), $this->resourceOf(PatientWritableType::QuestionnaireResponse));

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame('OperationOutcome', self::decode($response)['resourceType']);
        $this->assertSame(0, $this->store->countOpenForClient(self::PID, ReviewType::FhirWrite, self::PUBLIC_CLIENT));
    }

    public function testAnUnregisteredClientIsRefused(): void
    {
        $response = $this->controller()->create($this->patientRequest('unknown-client'), $this->resourceOf(PatientWritableType::QuestionnaireResponse));

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame(0, $this->store->countOpenForClient(self::PID, ReviewType::FhirWrite, 'unknown-client'));
    }

    public function testOnlyTheWritingAppReadsItBack(): void
    {
        $id = $this->createdId();

        $read = $this->controller()->read($this->patientRequest(), $id);
        $this->assertNotNull($read);
        $this->assertSame(200, $read->getStatusCode());
        $this->assertSame($id, self::decode($read)['id']);
        $this->assertNull($this->controller()->read($this->patientRequest(self::PUBLIC_CLIENT), $id), 'another app reads the chart');
    }

    public function testAnUpdateReplacesTheHeldWriteAndAWithdrawalIsThenGone(): void
    {
        $id = $this->createdId();
        $changed = ['id' => $id, 'status' => 'amended'] + $this->resourceOf(PatientWritableType::QuestionnaireResponse);

        $updated = $this->controller()->update($this->patientRequest(), $id, $changed);
        $this->assertSame(200, $updated->getStatusCode());
        $this->assertSame('amended', self::decode($updated)['status']);

        $withdrawn = $this->controller()->update($this->patientRequest(), $id, ['status' => 'entered-in-error'] + $changed);
        $this->assertSame(200, $withdrawn->getStatusCode());
        $this->assertSame(ReviewStatus::Cancelled, $this->queue->findByUuid($id)?->status);

        $read = $this->controller()->read($this->patientRequest(), $id);
        $this->assertNotNull($read);
        $this->assertSame(410, $read->getStatusCode());
    }

    public function testAnUpdateFromAnotherAppIsRefused(): void
    {
        $id = $this->createdId();

        $response = $this->controller()->update($this->patientRequest(self::PUBLIC_CLIENT), $id, ['id' => $id] + $this->resourceOf(PatientWritableType::QuestionnaireResponse));

        $this->assertSame(403, $response->getStatusCode());
    }

    public function testSearchFindsHeldWritesOnlyForParametersTheyCanMatch(): void
    {
        $first = $this->createdId();
        $second = $this->createdId();
        $controller = $this->controller();
        $request = $this->patientRequest();

        $this->assertCount(2, $controller->searchHeld($request, []));
        $this->assertCount(2, $controller->searchHeld($request, ['patient' => 'Patient/' . self::PATIENT]));
        $this->assertCount(2, $controller->searchHeld($request, ['subject' => strtoupper(self::PATIENT)]), 'a uuid in upper case');
        $this->assertCount(2, $controller->searchHeld($request, ['_count' => '1']), 'the bundle trims them to _count');
        $this->assertSame([$second], array_column($controller->searchHeld($request, ['_id' => $second]), 'id'));
        $this->assertSame([], $controller->searchHeld($request, ['_id' => $first, 'authored' => 'ge2026-01-01']), 'unmatched parameter');
        $this->assertSame([], $controller->searchHeld($request, ['patient' => 'another-patient']));
        $this->assertSame([], $controller->searchHeld($this->patientRequest(self::PUBLIC_CLIENT), []));
    }

    public function testANonPatientRequestNeverSeesHeldWrites(): void
    {
        $id = $this->createdId();
        $staff = new HttpRestRequest();
        $staff->setClientId(self::CLIENT);

        $this->assertNull($this->controller()->read($staff, $id));
        $this->assertSame([], $this->controller()->searchHeld($staff, []));
    }
}
