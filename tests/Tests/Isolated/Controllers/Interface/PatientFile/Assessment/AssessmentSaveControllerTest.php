<?php

/**
 * Isolated tests for the assessment workspace's save: what it stores, with whose identity,
 * and the requests it refuses.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Controllers\Interface\PatientFile\Assessment;

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Controllers\Interface\PatientFile\Assessment\AssessmentSaveController;
use OpenEMR\Services\NativeQuestionnaireResponseProcessor;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

#[Group('isolated')]
class AssessmentSaveControllerTest extends TestCase
{
    use TranslationOff;

    private const RESPONSE = '{"resourceType":"QuestionnaireResponse","status":"completed","subject":{"reference":"Patient/someone-else"},"item":[]}';

    private Session $session;

    private FakeAssessmentStore $store;

    protected function setUp(): void
    {
        $this->turnTranslationOff();
        $this->session = new Session(new MockArraySessionStorage());
        $this->session->set('csrf_private_key', 'save-test-key');
        $this->store = FakeAssessmentStore::sample();
    }

    protected function tearDown(): void
    {
        $this->restoreTranslation();
    }

    /**
     * The save for patient 7 with patients/med, against the sample store unless one is given.
     */
    private function controller(int $pid = 7, bool $authorized = true, ?FakeAssessmentStore $store = null): AssessmentSaveController
    {
        return new AssessmentSaveController(
            $store ?? $this->store,
            new NativeQuestionnaireResponseProcessor(),
            $this->session,
            new NullLogger(),
            $pid,
            $authorized,
        );
    }

    /**
     * A POST from the workspace with a valid token unless $token is given.
     *
     * @param array<string, string> $fields
     */
    private function post(array $fields, ?string $token = null): Request
    {
        return Request::create('/native_save.php', 'POST', [
            'csrf_token_form' => $token ?? CsrfUtils::collectCsrfToken($this->session),
            'questionnaire_id' => '1',
            'response_id' => '',
            'questionnaire_response' => self::RESPONSE,
            ...$fields,
        ]);
    }

    /**
     * @return array<mixed>
     */
    private static function body(string|false $content): array
    {
        self::assertIsString($content);
        $body = json_decode($content, true);
        self::assertIsArray($body);
        return $body;
    }

    #[Test]
    public function aNewResponseIsStampedWithTheSessionPatientAndSaved(): void
    {
        $response = $this->controller()->dispatchAction($this->post([]));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['success' => true, 'id' => 99, 'response_id' => 'qr-new', 'new' => true], self::body($response->getContent()));
        $this->assertCount(1, $this->store->saved);
        $saved = $this->store->saved[0];
        $this->assertSame(7, $saved['pid']);
        $this->assertNull($saved['responseId']);
        $this->assertSame(FakeAssessmentStore::PHQ9, $saved['questionnaire'], 'the repository questionnaire, not one from the browser');
        $stored = json_decode($saved['response'], true);
        $this->assertIsArray($stored);
        $this->assertSame(['reference' => 'Patient/96506861-511f-4f6d-bc97-b65a78cf1995'], $stored['subject']);
    }

    #[Test]
    public function anUpdateKeepsTheQuestionnaireTheResponseWasSavedWith(): void
    {
        $response = $this->controller()->dispatchAction($this->post(['response_id' => 'qr-11']));

        $this->assertSame(['success' => true, 'id' => 99, 'response_id' => 'qr-11', 'new' => false], self::body($response->getContent()));
        $this->assertSame('qr-11', $this->store->saved[0]['responseId']);
    }

    /**
     * @return array<string, array{array<string, string>, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function refusalProvider(): array
    {
        return [
            'no questionnaire id' => [['questionnaire_id' => ''], 'A Questionnaire repository id is required.'],
            'inactive questionnaire' => [['questionnaire_id' => '4'], 'The selected Questionnaire is not active or was not found.'],
            'not a response' => [['questionnaire_response' => '{"resourceType":"Patient"}'], 'FHIR QuestionnaireResponse data is invalid.'],
            'unknown response' => [['response_id' => 'qr-missing'], 'The QuestionnaireResponse to update was not found.'],
            "another patient's response" => [['response_id' => 'qr-other'], 'The QuestionnaireResponse does not belong to the selected patient.'],
            'encounter response' => [['response_id' => 'qr-encounter'], 'Encounter questionnaires cannot be updated from the patient assessment workspace.'],
            'linked to another questionnaire' => [['response_id' => 'qr-11', 'questionnaire_id' => '2'], 'QuestionnaireResponse repository linkage is invalid.'],
        ];
    }

    /**
     * @param array<string, string> $fields
     */
    #[Test]
    #[DataProvider('refusalProvider')]
    public function refusals(array $fields, string $message): void
    {
        $response = $this->controller()->dispatchAction($this->post($fields));

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame(['success' => false, 'message' => $message], self::body($response->getContent()));
        $this->assertSame([], $this->store->saved);
    }

    #[Test]
    public function aBadTokenIsRefusedBeforeAnythingElse(): void
    {
        $response = $this->controller(pid: 0)->dispatchAction($this->post([], 'forged'));

        $this->assertSame(['success' => false, 'message' => 'Unable to save QuestionnaireResponse.'], self::body($response->getContent()));
    }

    /**
     * Each of the two missing permissions is refused the same way.
     */
    #[Test]
    public function withoutAPatientOrPatientsMedNothingIsSaved(): void
    {
        foreach ([$this->controller(pid: 0), $this->controller(authorized: false)] as $controller) {
            $response = $controller->dispatchAction($this->post([]));
            $this->assertSame(['success' => false, 'message' => 'Not authorized to save patient assessments.'], self::body($response->getContent()));
        }
        $this->assertSame([], $this->store->saved);
    }

    #[Test]
    public function aPatientWithoutAUuidIsRefused(): void
    {
        $store = new FakeAssessmentStore([['id' => 1, 'active' => 1, 'questionnaire' => FakeAssessmentStore::PHQ9]], patientUuid: '');
        $response = $this->controller(store: $store)->dispatchAction($this->post([]));

        $this->assertSame(['success' => false, 'message' => 'Unable to resolve the patient identity for this QuestionnaireResponse.'], self::body($response->getContent()));
    }
}
