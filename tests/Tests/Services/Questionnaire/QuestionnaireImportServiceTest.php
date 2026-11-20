<?php

/**
 * Rules every staff questionnaire import has to follow, against the real repository.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Questionnaire;

use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\DocumentTemplates\DocumentTemplateService;
use OpenEMR\Services\Questionnaire\Import\QuestionnaireImportRefusal;
use OpenEMR\Services\Questionnaire\Import\QuestionnaireImportRefused;
use OpenEMR\Services\Questionnaire\Import\QuestionnaireImportRequest;
use OpenEMR\Services\Questionnaire\Import\QuestionnaireImportService;
use OpenEMR\Services\Questionnaire\QuestionnaireAvailabilityLookups;
use OpenEMR\Services\Questionnaire\QuestionnaireAvailabilityService;
use OpenEMR\Services\Questionnaire\QuestionnaireSurface;
use OpenEMR\Services\QuestionnaireService;
use PHPUnit\Framework\TestCase;

class QuestionnaireImportServiceTest extends TestCase
{
    private const NAME = 'Import Rules Test Questionnaire';
    private const OTHER_NAME = 'Import Rules Renamed Questionnaire';

    private QuestionnaireImportService $importer;
    private QuestionnaireService $questionnaires;
    private string $category;

    protected function setUp(): void
    {
        $this->removeRows();
        $this->questionnaires = new QuestionnaireService();
        $lookups = new QuestionnaireAvailabilityLookups(new ClientRepository());
        $this->importer = new QuestionnaireImportService(
            $this->questionnaires,
            new QuestionnaireAvailabilityService(),
            $lookups,
            new DocumentTemplateService(),
        );
        $categories = array_keys($lookups->categoryTitles());
        $this->assertNotEmpty($categories, 'Observation_Types must be seeded');
        $this->category = $categories[0];
    }

    protected function tearDown(): void
    {
        $this->removeRows();
    }

    public function testImportStoresTheCategoryAndUsesTheFhirTitle(): void
    {
        $result = $this->importer->import(new QuestionnaireImportRequest(self::json(), '', $this->category));

        $row = $this->questionnaires->fetchQuestionnaireById($result->id);
        $this->assertFalse($result->replaced);
        $this->assertSame(self::NAME, $row['name']);
        $this->assertSame($this->category, $row['category']);
    }

    public function testImportWithoutACategoryIsRefused(): void
    {
        $this->assertRefused(
            QuestionnaireImportRefusal::MissingCategory,
            new QuestionnaireImportRequest(self::json(), self::NAME, '')
        );
    }

    public function testImportWithACategoryOutsideTheListIsRefused(): void
    {
        $this->assertRefused(
            QuestionnaireImportRefusal::UnknownCategory,
            new QuestionnaireImportRequest(self::json(), self::NAME, 'no-such-category')
        );
    }

    public function testAResourceThatIsNotAQuestionnaireIsRefused(): void
    {
        $this->assertRefused(
            QuestionnaireImportRefusal::NotQuestionnaire,
            new QuestionnaireImportRequest('{"resourceType":"Patient"}', self::NAME, $this->category)
        );
    }

    public function testBrokenJsonIsRefused(): void
    {
        $this->assertRefused(
            QuestionnaireImportRefusal::InvalidJson,
            new QuestionnaireImportRequest('{not json', self::NAME, $this->category)
        );
    }

    public function testASameNamedImportIsRefusedUntilConfirmed(): void
    {
        $first = $this->importer->import(new QuestionnaireImportRequest(self::json(), '', $this->category));

        $this->assertRefused(
            QuestionnaireImportRefusal::ExistingNotConfirmed,
            new QuestionnaireImportRequest(self::json(), '', $this->category)
        );

        $second = $this->importer->import(
            new QuestionnaireImportRequest(self::json(), '', $this->category, replaceExisting: true)
        );
        $this->assertTrue($second->replaced);
        $this->assertSame($first->id, $second->id);
        $this->assertEquals(2, $this->questionnaires->fetchQuestionnaireById($first->id)['version']);
    }

    public function testReviewReportsTheRowAnImportWouldReplaceAndWritesNothing(): void
    {
        $this->assertNull($this->importer->review(self::json())->existing);
        $this->assertSame([], $this->questionnaires->getQuestionnaireIdAndVersion(self::NAME));

        $first = $this->importer->import(new QuestionnaireImportRequest(self::json(), '', $this->category));
        $review = $this->importer->review(self::json());

        $this->assertSame($first->id, $review->existing['id'] ?? null);
        $this->assertSame($this->category, $review->suggestedCategory);
        $this->assertNull($this->importer->review(self::json(), self::OTHER_NAME)->existing);
    }

    public function testAnEditedNameReplacesTheFhirTitle(): void
    {
        $result = $this->importer->import(new QuestionnaireImportRequest(self::json(), self::OTHER_NAME, $this->category));

        $this->assertSame(self::OTHER_NAME, $this->questionnaires->fetchQuestionnaireById($result->id)['name']);
    }

    public function testAMetaTagNamingACategoryIsSuggested(): void
    {
        $json = self::json(['meta' => ['tag' => [['code' => $this->category]]]]);

        $this->assertSame($this->category, $this->importer->review($json)->suggestedCategory);
    }

    public function testTagsAreStoredAndASourceCodingIsKept(): void
    {
        $source = ['system' => 'http://example.org/tags', 'code' => 'kept', 'display' => 'Kept'];
        $json = self::json(['meta' => ['tag' => [$source, ['code' => 'dropped']]]]);

        $result = $this->importer->import(new QuestionnaireImportRequest($json, '', $this->category, ['Kept', 'New Tag']));

        $json = $this->questionnaires->fetchQuestionnaireById($result->id)['questionnaire'];
        $this->assertIsString($json);
        $stored = json_decode($json, true);
        $this->assertIsArray($stored);
        $meta = $stored['meta'] ?? null;
        $this->assertIsArray($meta);
        $tags = $meta['tag'] ?? null;
        $this->assertIsArray($tags);
        $kept = [];
        foreach ($tags as $tag) {
            $this->assertIsArray($tag);
            $kept[] = array_intersect_key($tag, $source);
        }
        $this->assertSame([$source, ['code' => 'new-tag', 'display' => 'New Tag']], $kept);
    }

    public function testWithNoSurfaceChoiceTheDashboardDefaultStands(): void
    {
        $result = $this->importer->import(new QuestionnaireImportRequest(self::json(), '', $this->category));

        $this->assertSame([QuestionnaireSurface::Dashboard->value => 1], $this->assignments($result->id));
    }

    public function testAnUntickedDashboardIsWithdrawnAndTickedSurfacesAreOffered(): void
    {
        $result = $this->importer->import(new QuestionnaireImportRequest(
            self::json(),
            '',
            $this->category,
            surfaces: [QuestionnaireSurface::Smart],
        ));

        $this->assertSame(
            [QuestionnaireSurface::Dashboard->value => 0, QuestionnaireSurface::Smart->value => 1],
            $this->assignments($result->id)
        );
    }

    public function testOfferingOnEncountersRegistersTheForm(): void
    {
        $result = $this->importer->import(new QuestionnaireImportRequest(
            self::json(),
            '',
            $this->category,
            surfaces: [QuestionnaireSurface::Encounter],
        ));

        $registered = QueryUtils::fetchSingleValue(
            'SELECT `form_foreign_id` FROM `registry` WHERE `directory` = ? AND `name` = ?',
            'form_foreign_id',
            [QuestionnaireAvailabilityService::ENCOUNTER_FORM_DIRECTORY, self::NAME]
        );
        $this->assertEquals($result->id, $registered);
    }

    public function testReplacingLeavesExistingAvailabilityAlone(): void
    {
        $first = $this->importer->import(new QuestionnaireImportRequest(
            self::json(),
            '',
            $this->category,
            surfaces: [],
        ));

        $this->importer->import(new QuestionnaireImportRequest(
            self::json(),
            '',
            $this->category,
            replaceExisting: true,
            surfaces: [QuestionnaireSurface::Dashboard, QuestionnaireSurface::Smart],
        ));

        $this->assertSame([QuestionnaireSurface::Dashboard->value => 0], $this->assignments($first->id));
    }

    public function testAPortalTemplateIsCreatedWhenAsked(): void
    {
        $result = $this->importer->import(new QuestionnaireImportRequest(
            self::json(),
            '',
            $this->category,
            portalTemplateCategory: '',
        ));

        $content = QueryUtils::fetchSingleValue(
            'SELECT `template_content` FROM `document_templates` WHERE `template_name` = ? AND `pid` = -1',
            'template_content',
            [self::NAME]
        );
        $this->assertSame('{ParseAsHTML}{Questionnaire:' . $result->id . '}' . "\n", $content);
    }

    public function testPreviewOnlyFindsActiveQuestionnaires(): void
    {
        $result = $this->importer->import(new QuestionnaireImportRequest(self::json(), '', $this->category));
        $this->assertSame(self::NAME, $this->importer->findForPreview($result->id)['name'] ?? null);

        QueryUtils::sqlStatementThrowException('UPDATE `questionnaire_repository` SET `active` = 0 WHERE `id` = ?', [$result->id]);

        $this->assertNull($this->importer->findForPreview($result->id));
    }

    public function testAnUnknownPortalTemplateCategoryIsRefused(): void
    {
        $this->assertRefused(
            QuestionnaireImportRefusal::UnknownTemplateCategory,
            new QuestionnaireImportRequest(self::json(), '', $this->category, portalTemplateCategory: 'no-such-category')
        );
    }

    /**
     * Assert that importing the request is refused for the expected reason.
     */
    private function assertRefused(QuestionnaireImportRefusal $expected, QuestionnaireImportRequest $request): void
    {
        try {
            $this->importer->import($request);
        } catch (QuestionnaireImportRefused $e) {
            $this->assertSame($expected, $e->refusal);

            return;
        }
        $this->fail('Expected the import to be refused: ' . $expected->name);
    }

    /**
     * @return array<string, int> surface => active
     */
    private function assignments(int $id): array
    {
        $rows = QueryUtils::fetchRecordsNoLog(
            'SELECT `surface`, `active` FROM `content_assignments`
              WHERE `resource_type` = ? AND `resource_id` = ? ORDER BY `surface`',
            [QuestionnaireAvailabilityService::RESOURCE_TYPE, $id]
        );
        $found = [];
        foreach ($rows as $row) {
            $surface = $row['surface'] ?? null;
            $this->assertIsString($surface);
            $found[$surface] = $row['active'] == 1 ? 1 : 0;
        }

        return $found;
    }

    /**
     * @param array<string, mixed> $extra
     */
    private static function json(array $extra = []): string
    {
        return (string)json_encode(array_merge([
            'resourceType' => 'Questionnaire',
            'status' => 'active',
            'title' => self::NAME,
            'item' => [['linkId' => 'q1', 'text' => 'Question', 'type' => 'string']],
        ], $extra));
    }

    private function removeRows(): void
    {
        foreach ([self::NAME, self::OTHER_NAME] as $name) {
            QueryUtils::sqlStatementThrowException(
                'DELETE ca FROM `content_assignments` ca
                   JOIN `questionnaire_repository` qr ON qr.`id` = ca.`resource_id`
                  WHERE qr.`name` = ?',
                [$name]
            );
            QueryUtils::sqlStatementThrowException('DELETE FROM `questionnaire_repository` WHERE `name` = ?', [$name]);
            QueryUtils::sqlStatementThrowException('DELETE FROM `registry` WHERE `name` = ? AND `form_foreign_id` > 0', [$name]);
            QueryUtils::sqlStatementThrowException('DELETE FROM `document_templates` WHERE `template_name` = ? AND `pid` = -1', [$name]);
        }
    }
}
