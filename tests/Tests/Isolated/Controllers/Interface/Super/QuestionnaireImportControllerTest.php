<?php

/**
 * What the import screen sends to the importer, and what it tells the user.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Controllers\Interface\Super;

use OpenEMR\Controllers\Interface\Super\QuestionnaireImportController;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\FHIR\Questionnaire\LhcFormToFhirConverter;
use OpenEMR\Services\Questionnaire\Import\LoincFormClient;
use OpenEMR\Services\Questionnaire\Import\LoincFormUnavailable;
use OpenEMR\Services\Questionnaire\Import\QuestionnaireImportRefusal;
use OpenEMR\Services\Questionnaire\Import\QuestionnaireImportRefused;
use OpenEMR\Services\Questionnaire\Import\QuestionnaireImportRequest;
use OpenEMR\Services\Questionnaire\Import\QuestionnaireImportResult;
use OpenEMR\Services\Questionnaire\Import\QuestionnaireImportReview;
use OpenEMR\Services\Questionnaire\Import\QuestionnaireImportService;
use OpenEMR\Services\Questionnaire\QuestionnaireSurface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

#[Group('isolated')]
class QuestionnaireImportControllerTest extends TestCase
{
    private FakeQuestionnaireImporter $importer;
    private FakeLoincClient $loinc;

    /** @var array{present: bool, value: bool} */
    private array $translationSetting;

    protected function setUp(): void
    {
        $globals = OEGlobalsBag::getInstance();
        $this->translationSetting = [
            'present' => $globals->has('disable_translation'),
            'value' => $globals->getBoolean('disable_translation'),
        ];
        $globals->set('disable_translation', true);
        $this->importer = new FakeQuestionnaireImporter();
        $this->loinc = new FakeLoincClient();
    }

    /**
     * Put disable_translation back the way setUp() found it.
     */
    protected function tearDown(): void
    {
        $globals = OEGlobalsBag::getInstance();
        if ($this->translationSetting['present']) {
            $globals->set('disable_translation', $this->translationSetting['value']);
        } else {
            $globals->remove('disable_translation');
        }
    }

    public function testImportPassesTheFieldsThrough(): void
    {
        $response = $this->controller()->handlePost(self::post([
            'action' => 'import',
            'questionnaire' => '{}',
            'name' => 'PHQ',
            'category' => 'survey',
            'tags' => ' one, two ,one,',
            'replace_existing' => '1',
            'surfaces' => 'dashboard,smart,portal,nonsense',
            'create_template' => '1',
            'template_category' => 'questionnaire',
        ]), true);

        $this->assertSame(['id' => 7, 'replaced' => false], self::decode($response));
        $request = $this->importer->received;
        $this->assertNotNull($request);
        $this->assertSame('PHQ', $request->name);
        $this->assertSame('survey', $request->category);
        $this->assertSame(['one', 'two'], $request->tags);
        $this->assertTrue($request->replaceExisting);
        $this->assertSame([QuestionnaireSurface::Dashboard, QuestionnaireSurface::Smart], $request->surfaces);
        $this->assertSame('questionnaire', $request->portalTemplateCategory);
    }

    public function testAUserWhoCannotAssignLeavesTheDefaultAvailability(): void
    {
        $this->controller()->handlePost(self::post(['action' => 'import', 'surfaces' => 'smart']), false);

        $this->assertNull($this->importer->received?->surfaces);
    }

    public function testNoPortalTemplateWhenThePortalIsOff(): void
    {
        $this->controller(portalEnabled: false)->handlePost(self::post([
            'action' => 'import',
            'create_template' => '1',
            'template_category' => '',
        ]), true);

        $this->assertNull($this->importer->received?->portalTemplateCategory);
    }

    public function testAnUntickedTemplateIsNotCreated(): void
    {
        $this->controller()->handlePost(self::post(['action' => 'import', 'template_category' => '']), true);

        $this->assertNull($this->importer->received?->portalTemplateCategory);
    }

    public function testAnEmptyNameInReviewUsesTheResourcesOwn(): void
    {
        $response = $this->controller()->handlePost(self::post(['action' => 'review', 'questionnaire' => '{}']), true);

        $this->assertNull($this->importer->reviewedName);
        $this->assertSame('Reviewed', self::decode($response)['name']);
    }

    public function testAFileTheFhirModelRejectsIsInvalidJsonNotALoincFailure(): void
    {
        $this->importer->rejectByModel = true;

        $response = $this->controller()->handlePost(self::post(['action' => 'import']), true);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame(['error' => 'This is not valid FHIR Questionnaire JSON.'], self::decode($response));
    }

    public function testARefusalIsReportedInWordsNotTheExceptionMessage(): void
    {
        $this->importer->refuseWith = QuestionnaireImportRefusal::MissingCategory;

        $response = $this->controller()->handlePost(self::post(['action' => 'import']), true);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame(['error' => 'Choose a category.'], self::decode($response));
    }

    public function testLoincSearchReturnsTheResults(): void
    {
        $response = $this->controller()->handlePost(self::post(['action' => 'loinc_search', 'terms' => 'phq']), true);

        $this->assertSame('phq', $this->loinc->searched);
        $this->assertSame(['results' => [['code' => '44249-1', 'name' => 'PHQ-9']]], self::decode($response));
    }

    public function testLoincFormComesBackAsFhirWithItsConversionNotes(): void
    {
        $response = $this->controller()->handlePost(self::post(['action' => 'loinc_fetch', 'loinc_number' => '44249-1']), true);

        $payload = self::decode($response);
        $this->assertIsString($payload['json']);
        $questionnaire = json_decode($payload['json'], true);
        $this->assertIsArray($questionnaire);
        $this->assertSame('Questionnaire', $questionnaire['resourceType']);
        $this->assertSame(['Odd: Unknown answer type, shown as text'], $payload['notes']);
    }

    public function testNlmBeingDownIsReportedInWords(): void
    {
        $this->loinc->failure = new LoincFormUnavailable('internal detail');

        $response = $this->controller()->handlePost(self::post(['action' => 'loinc_search', 'terms' => 'phq']), true);

        $this->assertSame(502, $response->getStatusCode());
        $this->assertStringNotContainsString('internal detail', (string)$response->getContent());
    }

    public function testAnUnknownActionIsRejected(): void
    {
        $this->assertSame(400, $this->controller()->handlePost(self::post(['action' => 'bogus']), true)->getStatusCode());
    }

    public function testOnlyAdministratorsAreOfferedSurfaces(): void
    {
        $this->assertSame([], $this->controller()->importView(self::emptyQuery(), false)['surfaces']);
        $surfaces = $this->controller()->importView(self::emptyQuery(), true)['surfaces'];
        $this->assertIsArray($surfaces);
        $this->assertCount(3, $surfaces);
    }

    public function testPreviewShowsAnActiveQuestionnaire(): void
    {
        $this->importer->preview = ['name' => 'PHQ', 'json' => '{"a":1}'];

        $view = $this->controller()->previewView(5);

        $this->assertSame('PHQ', $view['name']);
        $this->assertSame('{"a":1}', $view['questionnaireJson']);
    }

    public function testPreviewOfAnUnavailableQuestionnaireIsEmpty(): void
    {
        $view = $this->controller()->previewView(5);

        $this->assertSame('', $view['questionnaireJson']);
        $this->assertSame('', $view['name']);
    }

    private function controller(bool $portalEnabled = true): QuestionnaireImportController
    {
        $session = new Session(new MockArraySessionStorage());
        $session->set('csrf_private_key', 'controller-test-key');

        return new QuestionnaireImportController(
            $this->importer,
            $this->loinc,
            new LhcFormToFhirConverter(),
            new Environment(new ArrayLoader()),
            $session,
            new NullLogger(),
            ['stylesheet' => 's.css', 'script' => 'r.js', 'version' => '1', 'strings' => '{}'],
            $portalEnabled,
            true,
        );
    }

    /**
     * @param array<string, string> $fields
     * @return InputBag<bool|float|int|string>
     */
    private static function post(array $fields): InputBag
    {
        return (new Request(request: $fields))->request;
    }

    /**
     * @return InputBag<string>
     */
    private static function emptyQuery(): InputBag
    {
        return (new Request())->query;
    }

    /**
     * @return array<mixed>
     */
    private static function decode(JsonResponse $response): array
    {
        $decoded = json_decode((string)$response->getContent(), true);
        self::assertIsArray($decoded);

        return $decoded;
    }
}

final class FakeLoincClient extends LoincFormClient
{
    public string $searched = '';
    public ?LoincFormUnavailable $failure = null;

    public function __construct()
    {
    }

    /**
     * One canned result, or the failure a test set.
     *
     * @return list<array{code: string, name: string}>
     */
    public function search(string $terms): array
    {
        if ($this->failure !== null) {
            throw $this->failure;
        }
        $this->searched = $terms;

        return [['code' => '44249-1', 'name' => 'PHQ-9']];
    }

    public function fetch(string $loincNumber): array
    {
        return [
            'name' => 'Form',
            'code' => $loincNumber,
            'items' => [['questionCode' => '1-1', 'question' => 'Odd', 'dataType' => 'WHAT']],
        ];
    }
}

final class FakeQuestionnaireImporter extends QuestionnaireImportService
{
    public ?QuestionnaireImportRequest $received = null;
    public ?string $reviewedName = 'not called';
    public ?QuestionnaireImportRefusal $refuseWith = null;
    public bool $rejectByModel = false;

    /** @var ?array{name: string, json: string} */
    public ?array $preview = null;

    public function __construct()
    {
    }

    public function review(string $json, ?string $name = null): QuestionnaireImportReview
    {
        $this->reviewedName = $name;

        return new QuestionnaireImportReview([], 'Reviewed', [], null, null);
    }

    /**
     * Record the request, or refuse it with the refusal a test set.
     */
    public function import(QuestionnaireImportRequest $request): QuestionnaireImportResult
    {
        if ($this->refuseWith !== null) {
            throw new QuestionnaireImportRefused($this->refuseWith);
        }
        if ($this->rejectByModel) {
            throw new \InvalidArgumentException('Invalid value for FHIRQuestionnaire field');
        }
        $this->received = $request;

        return new QuestionnaireImportResult(7, false);
    }

    public function findForPreview(int $id): ?array
    {
        return $this->preview;
    }

    public function categories(): array
    {
        return ['survey' => 'Survey'];
    }

    public function templateCategories(): array
    {
        return ['' => 'General'];
    }
}
