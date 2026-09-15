<?php

/**
 * FHIR Questionnaire Service CRUD Tests
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
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRQuestionnaire;
use OpenEMR\Services\FHIR\FhirQuestionnaireService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class FhirQuestionnaireServiceCrudTest extends TestCase
{
    private const TITLE_PREFIX = 'test-fixture Questionnaire';

    private FhirQuestionnaireService $service;
    private SessionInterface $session;
    /** @var array<string, mixed> */
    private array $fixture;
    private string $title;

    protected function setUp(): void
    {
        $this->session = SessionWrapperFactory::getInstance()->getActiveSession();
        $this->session->set('authUserID', QueryUtils::fetchSingleValue('SELECT id FROM users ORDER BY id LIMIT 1', 'id'));

        $raw = file_get_contents(__DIR__ . '/../../Fixtures/FHIR/questionnaire.json');
        $this->assertIsString($raw);
        $fixtureData = json_decode($raw, true);
        $this->assertIsArray($fixtureData);
        $fixture = $fixtureData[0];
        $this->assertIsArray($fixture);

        // the repository keys questionnaires by title, so each run gets its own
        $this->title = self::TITLE_PREFIX . ' ' . bin2hex(random_bytes(4));
        $fixture['title'] = $this->title;
        unset($fixture['resourceType']);

        $stringKeyed = [];
        foreach ($fixture as $key => $value) {
            $this->assertIsString($key);
            $stringKeyed[$key] = $value;
        }
        $this->fixture = $stringKeyed;

        $this->service = new FhirQuestionnaireService();
    }

    protected function tearDown(): void
    {
        $this->session->clear();
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM uuid_registry WHERE uuid IN (SELECT uuid FROM questionnaire_repository WHERE name LIKE ?)",
            [self::TITLE_PREFIX . '%']
        );
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM questionnaire_repository WHERE name LIKE ?",
            [self::TITLE_PREFIX . '%']
        );
    }

    #[Test]
    public function testInsert(): void
    {
        $result = $this->service->insert(new FHIRQuestionnaire($this->fixture));
        $this->assertTrue($result->isValid(), 'Insert should succeed: ' . json_encode($result->getValidationMessages()));

        $created = $result->getFirstDataResult();
        $this->assertIsArray($created);
        $this->assertArrayHasKey('uuid', $created);
        $this->assertIsString($created['uuid']);
    }

    /**
     * saveQuestionnaireResource() resolves an existing repository row by title, so a create
     * carrying a title already in the repository would silently overwrite that questionnaire.
     */
    #[Test]
    public function testInsertRejectsDuplicateTitle(): void
    {
        $this->assertTrue($this->service->insert(new FHIRQuestionnaire($this->fixture))->isValid());

        $second = $this->service->insert(new FHIRQuestionnaire($this->fixture));
        $this->assertFalse($second->isValid());
        $messages = $second->getValidationMessages();
        $this->assertIsArray($messages);
        $this->assertArrayHasKey('title', $messages);
        $this->assertSame([], $second->getData());
    }

    #[Test]
    public function testInsertRequiresStatus(): void
    {
        $fixture = $this->fixture;
        unset($fixture['status']);

        $this->expectException(\InvalidArgumentException::class);
        $this->service->insert(new FHIRQuestionnaire($fixture));
    }

    #[Test]
    public function testInsertRequiresTitle(): void
    {
        $fixture = $this->fixture;
        unset($fixture['title'], $fixture['name']);

        $this->expectException(\InvalidArgumentException::class);
        $this->service->insert(new FHIRQuestionnaire($fixture));
    }

    /**
     * FhirServiceBase::update() re-shapes the stored row through parseOpenEMRRecord(); an
     * aggregator that leaves that on the empty trait answers a successful PUT with a null body.
     */
    #[Test]
    public function testUpdateReturnsTheStoredResource(): void
    {
        $created = $this->service->insert(new FHIRQuestionnaire($this->fixture))->getFirstDataResult();
        $this->assertIsArray($created);
        $uuid = $created['uuid'];
        $this->assertIsString($uuid);

        $updated = $this->fixture;
        $updated['status'] = 'retired';
        $result = $this->service->update($uuid, new FHIRQuestionnaire($updated));
        $this->assertTrue($result->isValid(), 'Update should succeed: ' . json_encode($result->getValidationMessages()));

        $resource = $result->getFirstDataResult();
        $this->assertInstanceOf(FHIRQuestionnaire::class, $resource);
        $this->assertSame($uuid, $resource->getId()->getValue());

        // the update is in place: the repository still holds exactly one row for this title
        $rows = QueryUtils::fetchTableColumn(
            'SELECT id FROM questionnaire_repository WHERE name = ?',
            'id',
            [$this->title]
        );
        $this->assertCount(1, $rows);
    }

    #[Test]
    public function testUpdateWithMalformedUuid(): void
    {
        $result = $this->service->update('not-a-uuid', new FHIRQuestionnaire($this->fixture));
        $this->assertFalse($result->isValid());
        $this->assertSame([], $result->getData());
    }

    #[Test]
    public function testUpdateOfUnknownResourceReportsNotFound(): void
    {
        $result = $this->service->update(
            '00000000-0000-4000-8000-000000000001',
            new FHIRQuestionnaire($this->fixture)
        );
        $this->assertTrue($result->isValid());
        // an empty, error free result is what the REST layer turns into a 404
        $this->assertSame([], $result->getData());
    }
}
