<?php

/**
 * Isolated tests for the assessment workspace's controller: which questionnaire and response
 * it opens, and the requests it refuses in the frame instead.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Controllers\Interface\PatientFile\Assessment;

use OpenEMR\Controllers\Interface\PatientFile\Assessment\AssessmentUnavailable;
use OpenEMR\Controllers\Interface\PatientFile\Assessment\AssessmentWorkspaceController;
use OpenEMR\Services\NativeQuestionnaireResponseProcessor;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

#[Group('isolated')]
class AssessmentWorkspaceControllerTest extends TestCase
{
    use TranslationOff;

    protected function setUp(): void
    {
        $this->turnTranslationOff();
    }

    protected function tearDown(): void
    {
        $this->restoreTranslation();
    }

    /**
     * The workspace for patient 7, rendering $template in place of the real page.
     */
    private static function controller(string $template = ''): AssessmentWorkspaceController
    {
        return new AssessmentWorkspaceController(
            new Environment(new ArrayLoader([AssessmentWorkspaceController::TEMPLATE => $template])),
            FakeAssessmentStore::sample(),
            new NativeQuestionnaireResponseProcessor(),
            7,
            'token',
            '/save.php',
            ['stylesheet' => '/q.css', 'script' => '/q.js', 'version' => '8.5.0', 'strings' => '{}'],
        );
    }

    #[Test]
    public function aNewResponseOpensTheActiveRepositoryQuestionnaire(): void
    {
        $view = self::controller()->buildView(1, '');

        $this->assertSame('PHQ-9', $view['title']);
        $this->assertSame(1, $view['questionnaireId']);
        $this->assertSame('null', $view['responseJson']);
        $this->assertSame('', $view['error']);
        $this->assertSame('/save.php', $view['saveUrl']);
    }

    #[Test]
    public function aSavedResponseOpensWithTheQuestionnaireItWasAnsweredAgainst(): void
    {
        $view = self::controller()->buildView(null, 'qr-11');

        $this->assertSame(1, $view['questionnaireId']);
        $this->assertSame('qr-11', $view['responseId']);
        $this->assertSame('{"resourceType":"QuestionnaireResponse","status":"in-progress"}', $view['responseJson']);
    }

    #[Test]
    public function scriptJsonCannotCloseTheScriptElement(): void
    {
        $store = new FakeAssessmentStore([
            ['id' => 5, 'active' => 1, 'questionnaire' => '{"resourceType":"Questionnaire","title":"</script><b>"}'],
        ]);
        $controller = new AssessmentWorkspaceController(
            new Environment(new ArrayLoader()),
            $store,
            new NativeQuestionnaireResponseProcessor(),
            7,
            'token',
            '/save.php',
            ['stylesheet' => '', 'script' => '', 'version' => '', 'strings' => ''],
        );

        $json = $controller->buildView(5, '')['questionnaireJson'];

        $this->assertIsString($json);
        $this->assertStringNotContainsString('</script>', $json);
        $this->assertStringContainsString(trim(json_encode('</script>', JSON_HEX_TAG | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), '"'), $json);
    }

    /**
     * @return array<string, array{?int, string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function refusalProvider(): array
    {
        return [
            'no id' => [null, '', 'A Questionnaire repository id is required.'],
            'unknown questionnaire' => [404, '', 'The selected Questionnaire is not active or was not found.'],
            'inactive questionnaire' => [4, '', 'The selected Questionnaire is not active or was not found.'],
            'unknown response' => [null, 'qr-missing', 'Questionnaire response was not found.'],
            "another patient's response" => [null, 'qr-other', 'Questionnaire response does not belong to the selected patient.'],
            'encounter response' => [null, 'qr-encounter', 'Encounter questionnaires cannot be edited from the patient assessment workspace.'],
            'unlinked response' => [null, 'qr-unlinked', 'Questionnaire repository link is missing from the response.'],
            'no questionnaire anywhere' => [3, '', 'The repository record does not contain a FHIR Questionnaire.'],
        ];
    }

    #[Test]
    #[DataProvider('refusalProvider')]
    public function refusals(?int $questionnaireId, string $responseId, string $message): void
    {
        $this->expectException(AssessmentUnavailable::class);
        $this->expectExceptionMessage($message);

        self::controller()->buildView($questionnaireId, $responseId);
    }

    #[Test]
    public function aResponseSavedThroughTheApiFallsBackToTheRepositoryQuestionnaire(): void
    {
        $view = self::controller()->buildView(null, 'qr-api');

        $this->assertSame('PHQ-9', $view['title'], 'an empty stored questionnaire uses the repository one');
        $this->assertSame('null', $view['responseJson']);
    }

    #[Test]
    public function aRefusalIsShownInTheFrameNotThrown(): void
    {
        $response = self::controller('{{ error }}|{{ title }}')->dispatchAction(Request::create('/?response_id=qr-other'));

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Questionnaire response does not belong to the selected patient.|Questionnaire Assessment', $response->getContent());
    }

    #[Test]
    public function aMalformedIdIsTreatedAsMissing(): void
    {
        $response = self::controller('{{ error }}')->dispatchAction(Request::create('/?questionnaire_id=1abc'));

        $this->assertSame('A Questionnaire repository id is required.', $response->getContent());
    }

    #[Test]
    public function dispatchOpensTheQuestionnaire(): void
    {
        $response = self::controller('{{ title }}')->dispatchAction(Request::create('/?questionnaire_id=1'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('PHQ-9', $response->getContent());
    }
}
