<?php

/**
 * E2e test: staff review a QuestionnaireResponse a patient's app wrote through FHIR.
 *
 * A patient app's write is held, not added to the chart. This holds one the way the FHIR
 * create does, reads it back as the app, then has staff decide it on Patient App
 * Submissions. Approving adds it to the chart, where it shows in the patient's Assessment
 * History; denying needs a reason and adds nothing. Each step must leave an audit entry
 * naming who acted: the patient by portal login for the write and the read, the staff user
 * for the decision, with the reason for a denial.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWritableType;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteClientType;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteContext;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteHold;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteMode;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWritePolicy;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteRead;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteReadBack;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteReadOutcome;
use OpenEMR\Services\PatientReview\ReviewActor;
use OpenEMR\Services\PatientReview\ReviewQueueFactory;
use OpenEMR\Services\QuestionnaireService;
use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTestData;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Panther\PantherTestCase;

class PatientAppSubmissionReviewTest extends PantherTestCase
{
    use BaseTrait;
    use LoginTrait;

    private const FIXTURE_MARKER = 'e2e-patient-app-write';
    private const PORTAL_USERNAME = 'e2e.app.writer';
    private const CLIENT_ID = 'e2e-patient-app-write-client';
    private const SUBMISSIONS_URL = '/interface/main/patient_app_submissions.php';
    private const ASSESSMENTS_URL = '/interface/patient_file/assessment/fhir_assessments.php';

    private int $pid = 0;
    private int $requestId = 0;
    private string $requestUuid = '';
    private string $questionnaireTitle = '';

    /**
     * Approving the held response files it in the chart, lists it in Assessment History, and
     * audits the write and read as the patient and the decision as the staff user.
     */
    #[Test]
    #[Depends('testLoginAuthorized')]
    public function testApprovingAHeldResponseAddsItToTheChartAndAuditsEachStep(): void
    {
        // Inside the try, so a setup that fails part way is still cleaned up.
        try {
            $this->insertFixtureData();
            $this->base();
            $this->login(LoginTestData::username, LoginTestData::password);
            // Built while the app shell is loaded: it carries the shell's CSRF token.
            $dashboard = $this->dashboardUrl($this->pid);
            $this->openSubmission();

            $header = $this->client->executeScript(
                'return document.querySelector("[data-target=\'#submission-body-" + arguments[0] + "\']").innerText;',
                [$this->requestId]
            );
            $this->assertIsString($header);
            $this->assertStringContainsString('Questionnaire response: ' . $this->questionnaireTitle, $header, 'The card must name the questionnaire answered');
            $this->assertStringContainsString('Writer', $header, 'The card must name the patient');

            $this->client->executeScript(
                'document.querySelector("#submission-body-" + arguments[0] + " button[value=approve]").click();',
                [$this->requestId]
            );
            $this->waitForNotice('The submission was approved and added to the chart.');

            $this->assertSame('completed', $this->requestStatus());
            $filed = QueryUtils::querySingleRow(
                'SELECT patient_id, questionnaire_name, status FROM questionnaire_response WHERE uuid = ?',
                [UuidRegistry::uuidToBytes($this->requestUuid)]
            );
            $this->assertIsArray($filed, 'The approved response must be in the chart under the id the app was given');
            $this->assertSame($this->pid, $this->toInt($filed['patient_id']));
            $this->assertSame($this->questionnaireTitle, $filed['questionnaire_name']);
            $this->assertSame('completed', $filed['status']);

            $this->assertSame(
                [
                    [self::PORTAL_USERNAME, 'submitted as pending', ''],
                    [self::PORTAL_USERNAME, 'read', ''],
                    // Approved, then completed once it is in the chart.
                    [LoginTestData::username, 'pending to approved', ''],
                    [LoginTestData::username, 'approved to completed', ''],
                ],
                $this->auditTrail(),
                'The write and the read must be audited as the patient, the approval as the staff user'
            );

            // The chart: the patient's Assessment History lists the response.
            $this->client->request('GET', $dashboard);
            $this->client->request('GET', self::ASSESSMENTS_URL);
            $history = '[aria-labelledby="assessment-history-tab"]';
            $this->client->waitFor($history, 30);
            $rows = $this->client->executeScript(
                'return Array.from(document.querySelectorAll(arguments[0] + " tbody tr"))'
                . '.map(function (row) { return row.innerText; });',
                [$history]
            );
            $this->assertIsArray($rows);
            $matching = array_values(array_filter(
                $rows,
                fn(mixed $row): bool => is_string($row) && str_contains($row, $this->questionnaireTitle)
            ));
            $this->assertCount(1, $matching, 'The approved response must show once in Assessment History');
            $this->assertStringContainsString('completed', $matching[0]);
        } finally {
            try {
                if (isset($this->client)) {
                    $this->client->quit();
                }
            } finally {
                $this->cleanUpFixtureData();
            }
        }
    }

    /**
     * A denial needs a reason in the browser and on the server; once given, nothing is filed,
     * the app reads the write as gone, and the audit entry carries the reason.
     */
    #[Test]
    #[Depends('testLoginAuthorized')]
    public function testDenyingAHeldResponseNeedsAReasonAndAddsNothing(): void
    {
        // Inside the try, so a setup that fails part way is still cleaned up.
        try {
            $this->insertFixtureData();
            $this->base();
            $this->login(LoginTestData::username, LoginTestData::password);
            $this->openSubmission();

            $note = '#note-' . $this->requestId;
            $blocked = $this->client->executeScript('return document.querySelector(arguments[0]).checkValidity();', [$note]);
            $this->assertFalse($blocked, 'The browser must ask for a reason before denying');

            // Past the browser's check, as a hand-made POST would: the server refuses it too.
            $this->client->executeScript(
                'HTMLFormElement.prototype.submit.call(document.querySelector(arguments[0]).form);',
                [$note]
            );
            $this->waitForAlert('.alert-danger', 'Give a reason for denying the submission.');
            $this->assertSame('pending', $this->requestStatus(), 'A denial with no reason must leave the write waiting');

            $this->client->waitFor($note, 30);
            $this->client->executeScript(
                'var note = document.querySelector(arguments[0]);'
                . ' note.value = arguments[1];'
                . ' note.form.querySelector("button[type=submit]").click();',
                [$note, 'Answered for the wrong visit']
            );
            $this->waitForNotice('The submission was denied. Nothing was added to the chart.');

            $this->assertSame('denied', $this->requestStatus());
            $filed = QueryUtils::fetchSingleValue(
                'SELECT COUNT(*) AS c FROM questionnaire_response WHERE patient_id = ?',
                'c',
                [$this->pid]
            );
            $this->assertSame(0, $this->toInt($filed), 'A denied response must not be added to the chart');

            $read = $this->readBack();
            $this->assertSame(PatientWriteReadOutcome::Gone, $read->outcome, 'The app must read its denied write as gone');

            $this->assertSame(
                [
                    [self::PORTAL_USERNAME, 'submitted as pending', ''],
                    [self::PORTAL_USERNAME, 'read', ''],
                    [LoginTestData::username, 'pending to denied', 'Answered for the wrong visit'],
                ],
                $this->auditTrail(),
                'The denial must be audited as the staff user, with the reason'
            );
        } finally {
            try {
                if (isset($this->client)) {
                    $this->client->quit();
                }
            } finally {
                $this->cleanUpFixtureData();
            }
        }
    }

    /**
     * Patient App Submissions, with the fixture's card showing.
     */
    private function openSubmission(): void
    {
        $this->client->request('GET', self::SUBMISSIONS_URL);
        $this->client->waitFor('#submission-body-' . $this->requestId, 30);
        // The page is loaded on its own here, not in the tabs, so there is no top.restoreSession.
        $this->client->executeScript('window.restoreSession = function () { return true; };');
    }

    /**
     * Waits for the success alert the page shows after a decision.
     */
    private function waitForNotice(string $text): void
    {
        $this->waitForAlert('.alert-success', $text);
    }

    /**
     * Waits until the first alert matching the selector reads exactly the given text.
     */
    private function waitForAlert(string $selector, string $text): void
    {
        $this->client->wait(30)->until(
            fn(): bool => $this->client->executeScript(
                'var alert = document.querySelector(arguments[0]); return alert ? alert.innerText : "";',
                [$selector]
            ) === $text,
            'The page did not show: ' . $text
        );
    }

    /**
     * The fixture request's status as stored, e.g. pending, completed or denied.
     */
    private function requestStatus(): string
    {
        $status = QueryUtils::fetchSingleValue('SELECT status FROM patient_review_request WHERE id = ?', 'status', [$this->requestId]);
        $this->assertIsString($status);
        return $status;
    }

    /**
     * The patient-review audit entries for the fixture's request, oldest first, as
     * [user, what happened, note]. Matched on the request id, which is never reused.
     *
     * @return list<array{string, string, string}>
     */
    private function auditTrail(): array
    {
        $rows = QueryUtils::fetchRecords(
            "SELECT user, comments, user_notes FROM log WHERE event = 'patient-review' AND patient_id = ? ORDER BY id",
            [$this->pid]
        );
        $prefix = 'Review request ' . $this->requestId . ' (fhir-write) ';
        $suffix = ' for QuestionnaireResponse via API client ' . self::CLIENT_ID;
        $trail = [];
        foreach ($rows as $row) {
            $comment = is_string($row['comments']) ? base64_decode($row['comments'], true) : false;
            if ($comment === false || !str_starts_with($comment, $prefix)) {
                continue;
            }
            $this->assertStringEndsWith($suffix, $comment, 'The entry must name the resource type and the app');
            $trail[] = [
                is_string($row['user']) ? $row['user'] : '',
                substr($comment, strlen($prefix), -strlen($suffix)),
                is_string($row['user_notes']) ? $row['user_notes'] : '',
            ];
        }
        return $trail;
    }

    /**
     * The fixture patient's app, as the bearer token check describes it to the hold.
     */
    private function context(): PatientWriteContext
    {
        $uuid = QueryUtils::fetchSingleValue('SELECT uuid FROM patient_data WHERE pid = ?', 'uuid', [$this->pid]);
        $this->assertIsString($uuid);
        return new PatientWriteContext(
            UuidRegistry::uuidToString($uuid),
            self::CLIENT_ID,
            PatientWriteClientType::Confidential,
            ReviewActor::patient($this->pid, self::PORTAL_USERNAME),
        );
    }

    /**
     * What the app gets when it reads its write back, as a FHIR read does.
     */
    private function readBack(): PatientWriteRead
    {
        return (new PatientWriteReadBack(ReviewQueueFactory::create()))
            ->read($this->context(), PatientWritableType::QuestionnaireResponse, $this->requestUuid);
    }

    /**
     * Adds a patient and a questionnaire, then holds a response to it as the patient's app
     * writes one and reads it back, so the write and the read are both audited.
     */
    private function insertFixtureData(): void
    {
        $this->cleanUpFixtureData();

        $this->pid = $this->toInt(QueryUtils::fetchSingleValue('SELECT COALESCE(MAX(pid), 0) + 1 AS pid FROM patient_data', 'pid'));
        QueryUtils::sqlInsert(
            'INSERT INTO patient_data (pid, uuid, pubpid, fname, lname, DOB, sex, date) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())',
            [$this->pid, UuidRegistry::getRegistryForTable('patient_data')->createUuid(), self::FIXTURE_MARKER, 'Appwrite', 'Writer', '1970-01-01', 'Female']
        );

        $this->questionnaireTitle = self::FIXTURE_MARKER . ' ' . bin2hex(random_bytes(4));
        $rowId = (new QuestionnaireService())->saveQuestionnaireResource([
            'resourceType' => 'Questionnaire',
            'status' => 'active',
            'title' => $this->questionnaireTitle,
            'item' => [['linkId' => '1', 'text' => 'How are you feeling today?', 'type' => 'string']],
        ]);
        $questionnaireUuid = QueryUtils::fetchSingleValue('SELECT uuid FROM questionnaire_repository WHERE id = ?', 'uuid', [$rowId]);
        $this->assertIsString($questionnaireUuid);

        // As the FHIR create holds it for a patient app with write access.
        $handler = ReviewQueueFactory::fhirWriteHandler();
        $hold = new PatientWriteHold(
            ReviewQueueFactory::create([$handler]),
            new PatientWritePolicy(PatientWriteMode::ConfidentialAndPublic, [PatientWritableType::QuestionnaireResponse]),
            $handler,
        );
        $request = $hold->hold($this->context(), PatientWritableType::QuestionnaireResponse, [
            'resourceType' => 'QuestionnaireResponse',
            'questionnaire' => 'Questionnaire/' . UuidRegistry::uuidToString($questionnaireUuid),
            'status' => 'completed',
            'authored' => '2026-11-10T09:30:00+00:00',
            'item' => [['linkId' => '1', 'text' => 'How are you feeling today?', 'answer' => [['valueString' => 'Better, thanks']]]],
        ]);
        $this->requestId = $request->id;
        $this->requestUuid = $request->uuid;

        $read = $this->readBack();
        $this->assertSame(PatientWriteReadOutcome::Held, $read->outcome, 'The app must read back the write it was given an id for');
    }

    /**
     * Removes every fixture row this test adds, including any a failed run left behind.
     */
    private function cleanUpFixtureData(): void
    {
        $pids = QueryUtils::fetchTableColumn('SELECT pid FROM patient_data WHERE pubpid = ?', 'pid', [self::FIXTURE_MARKER]);
        foreach ($pids as $pid) {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM uuid_registry WHERE uuid IN (SELECT uuid FROM questionnaire_response WHERE patient_id = ?)',
                [$pid]
            );
            QueryUtils::sqlStatementThrowException('DELETE FROM questionnaire_response WHERE patient_id = ?', [$pid]);
            foreach (['patient_review_secret', 'patient_review_event'] as $table) {
                QueryUtils::sqlStatementThrowException(
                    'DELETE FROM ' . QueryUtils::escapeTableName($table)
                    . ' WHERE request_id IN (SELECT id FROM patient_review_request WHERE pid = ?)',
                    [$pid]
                );
            }
            QueryUtils::sqlStatementThrowException('DELETE FROM patient_review_request WHERE pid = ?', [$pid]);
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM uuid_registry WHERE uuid IN (SELECT uuid FROM patient_data WHERE pid = ?)',
                [$pid]
            );
        }
        // Saving the questionnaire also assigned it to the dashboard; that row goes first.
        $questionnaires = QueryUtils::fetchTableColumn('SELECT id FROM questionnaire_repository WHERE name LIKE ?', 'id', [self::FIXTURE_MARKER . ' %']);
        foreach ($questionnaires as $id) {
            QueryUtils::sqlStatementThrowException("DELETE FROM content_assignments WHERE resource_type = 'questionnaire' AND resource_id = ?", [$id]);
        }
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM uuid_registry WHERE uuid IN (SELECT uuid FROM questionnaire_repository WHERE name LIKE ?)',
            [self::FIXTURE_MARKER . ' %']
        );
        QueryUtils::sqlStatementThrowException('DELETE FROM questionnaire_repository WHERE name LIKE ?', [self::FIXTURE_MARKER . ' %']);
        QueryUtils::sqlStatementThrowException('DELETE FROM patient_data WHERE pubpid = ?', [self::FIXTURE_MARKER]);
    }

    /**
     * A database value as an int, failing the test when it is not numeric.
     */
    private function toInt(mixed $value): int
    {
        if (!is_numeric($value)) {
            $this->fail('Expected a number from the database, got ' . get_debug_type($value));
        }
        return (int) $value;
    }
}
