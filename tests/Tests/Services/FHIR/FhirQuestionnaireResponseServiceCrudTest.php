<?php

/**
 * FHIR QuestionnaireResponse Service CRUD Tests
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\FHIR;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRQuestionnaireResponse;
use OpenEMR\Services\FHIR\FhirQuestionnaireResponseService;
use OpenEMR\Services\QuestionnaireService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class FhirQuestionnaireResponseServiceCrudTest extends TestCase
{
    private const QUESTIONNAIRE_TITLE_PREFIX = 'test-fixture QR Questionnaire';

    private FhirQuestionnaireResponseService $service;
    private FixtureManager $fixtureManager;
    private SessionInterface $session;
    /** @var array<string, mixed> */
    private array $fixture;
    private string $patientUuid;
    private string $questionnaireUuid;

    protected function setUp(): void
    {
        $this->session = SessionWrapperFactory::getInstance()->getActiveSession();
        $this->session->set('authUserID', QueryUtils::fetchSingleValue('SELECT id FROM users ORDER BY id LIMIT 1', 'id'));

        $this->fixtureManager = new FixtureManager();
        $this->fixtureManager->installPatientFixtures();
        $patients = $this->fixtureManager->getPatientFixtures();
        $patientFixture = $patients[0];
        $this->assertIsArray($patientFixture);
        $patientRecord = QueryUtils::querySingleRow(
            'SELECT uuid FROM patient_data WHERE pubpid = ?',
            [$patientFixture['pubpid']]
        );
        $this->assertIsArray($patientRecord);
        $this->patientUuid = UuidRegistry::uuidToString($patientRecord['uuid']);

        $this->questionnaireUuid = $this->installQuestionnaire();

        $raw = file_get_contents(__DIR__ . '/../../Fixtures/FHIR/questionnaire-response.json');
        $this->assertIsString($raw);
        $fixtureData = json_decode($raw, true);
        $this->assertIsArray($fixtureData);
        $fixture = $fixtureData[0];
        $this->assertIsArray($fixture);
        $fixture['questionnaire'] = 'Questionnaire/' . $this->questionnaireUuid;
        $fixture['subject'] = ['reference' => 'Patient/' . $this->patientUuid];
        unset($fixture['resourceType']);

        $stringKeyed = [];
        foreach ($fixture as $key => $value) {
            $this->assertIsString($key);
            $stringKeyed[$key] = $value;
        }
        $this->fixture = $stringKeyed;

        $this->service = new FhirQuestionnaireResponseService();
    }

    protected function tearDown(): void
    {
        $this->session->clear();
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM uuid_registry WHERE uuid IN (SELECT uuid FROM questionnaire_response WHERE questionnaire_name LIKE ?)",
            [self::QUESTIONNAIRE_TITLE_PREFIX . '%']
        );
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM questionnaire_response WHERE questionnaire_name LIKE ?",
            [self::QUESTIONNAIRE_TITLE_PREFIX . '%']
        );
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM uuid_registry WHERE uuid IN (SELECT uuid FROM questionnaire_repository WHERE name LIKE ?)",
            [self::QUESTIONNAIRE_TITLE_PREFIX . '%']
        );
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM questionnaire_repository WHERE name LIKE ?",
            [self::QUESTIONNAIRE_TITLE_PREFIX . '%']
        );
        $this->fixtureManager->removePatientFixtures();
    }

    #[Test]
    public function testInsert(): void
    {
        $result = $this->service->insert(new FHIRQuestionnaireResponse($this->fixture));
        $this->assertTrue($result->isValid(), 'Insert should succeed: ' . json_encode($result->getValidationMessages()));

        $created = $result->getFirstDataResult();
        $this->assertIsArray($created);
        $this->assertArrayHasKey('uuid', $created);
        $this->assertIsString($created['uuid']);
    }

    /**
     * https://build.fhir.org/http.html#create -- a client supplied id is ignored on create,
     * so a POST never turns into an update of some other response.
     */
    #[Test]
    public function testInsertIgnoresClientSuppliedId(): void
    {
        $first = $this->service->insert(new FHIRQuestionnaireResponse($this->fixture))->getFirstDataResult();
        $this->assertIsArray($first);
        $firstId = $first['uuid'];
        $this->assertIsString($firstId);

        $second = $this->fixture;
        $second['id'] = $firstId;
        $result = $this->service->insert(new FHIRQuestionnaireResponse($second));
        $this->assertTrue($result->isValid(), 'Insert should succeed: ' . json_encode($result->getValidationMessages()));
        $created = $result->getFirstDataResult();
        $this->assertIsArray($created);
        $this->assertNotSame($firstId, $created['uuid'], 'A create must not overwrite the referenced response');
    }

    #[Test]
    public function testInsertRequiresLocalQuestionnaire(): void
    {
        $fixture = $this->fixture;
        $fixture['questionnaire'] = 'https://example.com/fhir/Questionnaire/' . $this->questionnaireUuid;

        $this->expectException(\InvalidArgumentException::class);
        $this->service->insert(new FHIRQuestionnaireResponse($fixture));
    }

    #[Test]
    public function testInsertRequiresSubject(): void
    {
        $fixture = $this->fixture;
        unset($fixture['subject']);

        $this->expectException(\InvalidArgumentException::class);
        $this->service->insert(new FHIRQuestionnaireResponse($fixture));
    }

    /**
     * FhirServiceBase::update() re-shapes the stored row through parseOpenEMRRecord(); an
     * aggregator that leaves that on the empty trait answers a successful PUT with a null body.
     */
    #[Test]
    public function testUpdateReturnsTheStoredResource(): void
    {
        $created = $this->service->insert(new FHIRQuestionnaireResponse($this->fixture))->getFirstDataResult();
        $this->assertIsArray($created);
        $uuid = $created['uuid'];
        $this->assertIsString($uuid);

        $updated = $this->fixture;
        $updated['id'] = $uuid;
        $updated['status'] = 'amended';
        $result = $this->service->update($uuid, new FHIRQuestionnaireResponse($updated));
        $this->assertTrue($result->isValid(), 'Update should succeed: ' . json_encode($result->getValidationMessages()));

        $resource = $result->getFirstDataResult();
        $this->assertInstanceOf(FHIRQuestionnaireResponse::class, $resource);
        $this->assertSame($uuid, $resource->getId()->getValue());
        $this->assertSame('amended', $resource->getStatus()->getValue());
    }

    /**
     * `patient_id` is not in the update statement, so a changed subject cannot be honoured.
     * It must not be written against the stored patient either.
     */
    #[Test]
    public function testUpdateRejectsRebindingTheSubject(): void
    {
        $created = $this->service->insert(new FHIRQuestionnaireResponse($this->fixture))->getFirstDataResult();
        $this->assertIsArray($created);
        $uuid = $created['uuid'];
        $this->assertIsString($uuid);

        $otherPatient = QueryUtils::querySingleRow(
            'SELECT uuid FROM patient_data WHERE uuid <> ? ORDER BY pid LIMIT 1',
            [UuidRegistry::uuidToBytes($this->patientUuid)]
        );
        if (!is_array($otherPatient)) {
            $this->markTestSkipped('needs a second patient record');
        }

        $updated = $this->fixture;
        $updated['id'] = $uuid;
        $updated['subject'] = ['reference' => 'Patient/' . UuidRegistry::uuidToString($otherPatient['uuid'])];
        $result = $this->service->update($uuid, new FHIRQuestionnaireResponse($updated));

        $this->assertTrue($result->isValid());
        // an empty, error free result is what the REST layer turns into a 404
        $this->assertSame([], $result->getData());
    }

    /**
     * `questionnaire_id` is not in the update statement either, so a changed questionnaire is
     * reported rather than silently ignored while the answers are re-filed under the old one.
     */
    #[Test]
    public function testUpdateRejectsRebindingTheQuestionnaire(): void
    {
        $created = $this->service->insert(new FHIRQuestionnaireResponse($this->fixture))->getFirstDataResult();
        $this->assertIsArray($created);
        $uuid = $created['uuid'];
        $this->assertIsString($uuid);

        $otherQuestionnaire = $this->installQuestionnaire();

        $updated = $this->fixture;
        $updated['id'] = $uuid;
        $updated['questionnaire'] = 'Questionnaire/' . $otherQuestionnaire;
        $result = $this->service->update($uuid, new FHIRQuestionnaireResponse($updated));

        $this->assertFalse($result->isValid());
        $messages = $result->getValidationMessages();
        $this->assertIsArray($messages);
        $this->assertArrayHasKey('questionnaire', $messages);
        $this->assertSame([], $result->getData());
    }

    #[Test]
    public function testUpdateWithMalformedUuid(): void
    {
        $result = $this->service->update('not-a-uuid', new FHIRQuestionnaireResponse($this->fixture));
        $this->assertFalse($result->isValid());
        $this->assertSame([], $result->getData());
    }

    #[Test]
    public function testUpdateOfUnknownResourceReportsNotFound(): void
    {
        $result = $this->service->update(
            '00000000-0000-4000-8000-000000000001',
            new FHIRQuestionnaireResponse($this->fixture)
        );
        $this->assertTrue($result->isValid());
        $this->assertSame([], $result->getData());
    }

    /**
     * Installs a questionnaire in the repository and returns its uuid.
     */
    private function installQuestionnaire(): string
    {
        $raw = file_get_contents(__DIR__ . '/../../Fixtures/FHIR/questionnaire.json');
        $this->assertIsString($raw);
        $questionnaireData = json_decode($raw, true);
        $this->assertIsArray($questionnaireData);
        $questionnaire = $questionnaireData[0];
        $this->assertIsArray($questionnaire);
        $questionnaire['title'] = self::QUESTIONNAIRE_TITLE_PREFIX . ' ' . bin2hex(random_bytes(4));
        unset($questionnaire['id'], $questionnaire['url']);

        $rowId = (new QuestionnaireService())->saveQuestionnaireResource($questionnaire);
        $this->assertNotEmpty($rowId);
        $binUuid = QueryUtils::fetchSingleValue('SELECT uuid FROM questionnaire_repository WHERE id = ?', 'uuid', [$rowId]);

        return UuidRegistry::uuidToString($binUuid);
    }
}
