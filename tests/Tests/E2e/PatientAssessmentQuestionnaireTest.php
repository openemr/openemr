<?php

/**
 * E2e test: staff start, save, reopen and change a questionnaire assessment on a patient's
 * Assessments page.
 *
 * A questionnaire added to the repository is offered on the patient dashboard. Starting it
 * opens the form in the page's workspace; Save refuses a form missing a required answer and
 * saves nothing. Once answered, it is saved as one completed QuestionnaireResponse for the
 * patient and listed in Assessment History. View / Edit reopens it with the saved answers
 * filled in, and saving a change updates that same response, one version on and amended,
 * rather than adding another.
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
use OpenEMR\Services\QuestionnaireService;
use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTestData;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Panther\PantherTestCase;

class PatientAssessmentQuestionnaireTest extends PantherTestCase
{
    use BaseTrait;
    use LoginTrait;

    private const FIXTURE_MARKER = 'e2e-patient-assessment';
    private const ASSESSMENTS_URL = '/interface/patient_file/assessment/fhir_assessments.php';
    private const FEELING = 'feeling';
    private const HOW_OFTEN = 'how-often';

    private int $pid = 0;
    private string $questionnaireTitle = '';

    /**
     * Start new, save, then View / Edit, change the answers and save again: one response,
     * updated in place and marked amended.
     */
    #[Test]
    #[Depends('testLoginAuthorized')]
    public function testANewAssessmentIsSavedThenEditedInPlace(): void
    {
        // Inside the try, so a setup that fails part way is still cleaned up.
        try {
            $this->insertFixtureData();
            $this->base();
            $this->login(LoginTestData::username, LoginTestData::password);
            // Built while the app shell is loaded: it carries the shell's CSRF token.
            $this->client->request('GET', $this->dashboardUrl($this->pid));
            $this->openAssessmentsPage();

            // Start new, from the questionnaire's card.
            $this->launch('#assessment-catalog .assessment-launch');
            $this->inForm(function (): void {
                $this->assertSame('', $this->formText(self::FEELING), 'A new assessment starts blank');
            });

            // Save with the required answer missing: the form says so and nothing is saved.
            $this->save();
            $this->inForm(function (): void {
                $this->client->wait(30)->until(
                    fn(): bool => $this->client->executeScript(
                        'return document.getElementById("native-assessment-alert").classList.contains("alert-danger");'
                    ) === true,
                    'The form did not report the missing required answer'
                );
            });
            $this->client->wait(10)->until(
                fn(): bool => $this->client->executeScript('return document.getElementById("assessment-save").disabled;') === false,
                'Save must be usable again after a failed check'
            );
            $this->assertSame([], $this->savedResponses(), 'A form missing a required answer must not be saved');

            $this->inForm(function (): void {
                $this->answerText(self::FEELING, 'Tired but better');
                $this->chooseOption(self::HOW_OFTEN, 'Several days');
            });
            $this->save();
            $this->waitForVersion(1);

            $first = $this->savedResponses();
            $this->assertCount(1, $first, 'Saving must add one response');
            $this->assertSame('completed', $first[0]['status']);
            $this->assertSame(0, $first[0]['encounter'], 'A dashboard assessment belongs to no encounter');
            $this->assertSame([self::FEELING => 'Tired but better', self::HOW_OFTEN => 'several-days'], $first[0]['answers']);

            $this->openAssessmentsPage();
            $this->assertSame([$this->questionnaireTitle . "\tcompleted\t1"], $this->historyRows(), 'Assessment History lists the saved response');

            // View / Edit, from Assessment History: the saved answers are filled in.
            $this->launch('[aria-labelledby="assessment-history-tab"] .assessment-launch');
            $this->inForm(function (): void {
                $this->client->wait(30)->until(
                    fn(): bool => $this->formText(self::FEELING) === 'Tired but better',
                    'The saved text answer was not filled in'
                );
                $this->assertSame('Several days', $this->chosenOption(self::HOW_OFTEN), 'The saved choice must be selected');
                $this->answerText(self::FEELING, 'Much better now');
                $this->chooseOption(self::HOW_OFTEN, 'Not at all');
            });
            $this->save();
            $this->waitForVersion(2);

            $edited = $this->savedResponses();
            $this->assertCount(1, $edited, 'Editing must update the response, not add another');
            $this->assertSame($first[0]['id'], $edited[0]['id']);
            $this->assertSame($first[0]['response_id'], $edited[0]['response_id']);
            // FHIR R4: a completed response that is changed afterwards is amended.
            $this->assertSame('amended', $edited[0]['status'], 'A changed completed response must be amended');
            $this->assertSame([self::FEELING => 'Much better now', self::HOW_OFTEN => 'not-at-all'], $edited[0]['answers']);

            $this->openAssessmentsPage();
            $this->assertSame([$this->questionnaireTitle . "\tamended\t2"], $this->historyRows(), 'Assessment History shows the one response, amended at version 2');
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
     * The patient's Assessments page, ready for a launch. It is loaded on its own here, not in
     * the tabs, so there is no top.restoreSession for the page or its form to call.
     */
    private function openAssessmentsPage(): void
    {
        $this->client->request('GET', self::ASSESSMENTS_URL);
        $this->client->waitFor('#assessment-catalog', 30);
        $this->client->executeScript('window.restoreSession = function () { return true; };');
    }

    /**
     * Clicks the fixture questionnaire's launch button among those the selector finds, and
     * waits for its form to load in the workspace.
     */
    private function launch(string $selector): void
    {
        $found = $this->client->executeScript(
            'var title = arguments[1];'
            . ' var buttons = Array.from(document.querySelectorAll(arguments[0]))'
            . '.filter(function (b) { return b.dataset.assessmentTitle === title; });'
            . ' if (buttons.length !== 1) { return buttons.length; }'
            . ' buttons[0].click(); return 1;',
            [$selector, $this->questionnaireTitle]
        );
        $this->assertSame(1, $found, 'Expected one launch button for the questionnaire under ' . $selector);
        $this->inForm(function (): void {
            $this->client->waitFor('[data-link-id="' . self::FEELING . '"] input', 30);
        });
    }

    /**
     * Runs the callback inside the workspace's form frame, then returns to the page.
     */
    private function inForm(callable $inFrame): void
    {
        $this->switchToIFrame('//iframe[@id="assessment-frame"]');
        try {
            $inFrame();
        } finally {
            $this->client->switchTo()->defaultContent();
        }
    }

    /**
     * Presses the workspace's Save, as the user does.
     */
    private function save(): void
    {
        $this->client->executeScript('document.getElementById("assessment-save").click();');
    }

    /**
     * Waits until the patient's one response has been saved at this version.
     */
    private function waitForVersion(int $version): void
    {
        $this->client->wait(30)->until(
            fn(): bool => array_column($this->savedResponses(), 'version') === [$version],
            'The assessment was not saved at version ' . $version
        );
    }

    /**
     * The value of the text answer for a question, in the form frame.
     */
    private function formText(string $linkId): string
    {
        $value = $this->client->executeScript(
            'var input = document.querySelector("[data-link-id=\'" + arguments[0] + "\'] input");'
            . ' return input ? input.value : null;',
            [$linkId]
        );
        $this->assertIsString($value, 'No text input for question ' . $linkId);
        return $value;
    }

    /**
     * Types an answer into a question's text input, in the form frame.
     */
    private function answerText(string $linkId, string $text): void
    {
        $this->client->executeScript(
            'var input = document.querySelector("[data-link-id=\'" + arguments[0] + "\'] input");'
            . ' input.focus(); input.value = arguments[1];'
            . ' input.dispatchEvent(new Event("input", {bubbles: true}));'
            . ' input.dispatchEvent(new Event("change", {bubbles: true}));'
            . ' input.blur();',
            [$linkId, $text]
        );
    }

    /**
     * Clicks the radio button labelled with the given text, in the form frame.
     */
    private function chooseOption(string $linkId, string $label): void
    {
        $clicked = $this->client->executeScript(
            'var wanted = arguments[1];'
            . ' var radio = Array.from(document.querySelectorAll("[data-link-id=\'" + arguments[0] + "\'] input[type=radio]"))'
            . '.find(function (r) { return r.labels.length > 0 && r.labels[0].innerText.trim() === wanted; });'
            . ' if (!radio) { return false; } radio.click(); return true;',
            [$linkId, $label]
        );
        $this->assertTrue($clicked, 'No option labelled ' . $label . ' for question ' . $linkId);
    }

    /**
     * The label of the selected radio button for a question, in the form frame; '' for none.
     */
    private function chosenOption(string $linkId): string
    {
        $label = $this->client->executeScript(
            'var radio = document.querySelector("[data-link-id=\'" + arguments[0] + "\'] input[type=radio]:checked");'
            . ' return radio && radio.labels.length > 0 ? radio.labels[0].innerText.trim() : "";',
            [$linkId]
        );
        $this->assertIsString($label);
        return $label;
    }

    /**
     * Assessment History's rows for the fixture questionnaire, as name, status and version
     * separated by tabs.
     *
     * @return list<string>
     */
    private function historyRows(): array
    {
        $rows = $this->client->executeScript(
            'var title = arguments[0];'
            . ' return Array.from(document.querySelectorAll("[aria-labelledby=\'assessment-history-tab\'] tbody tr"))'
            . '.map(function (row) { return Array.from(row.cells).map(function (cell) { return cell.textContent.trim(); }); })'
            . '.filter(function (cells) { return cells[0] === title; })'
            . '.map(function (cells) { return [cells[0], cells[1], cells[4]].join("\t"); });',
            [$this->questionnaireTitle]
        );
        $this->assertIsArray($rows);
        return array_values(array_map(static fn(mixed $row): string => is_string($row) ? $row : '', $rows));
    }

    /**
     * The patient's saved responses, oldest first, with the first answer to each question:
     * the text, or the code of the option chosen.
     *
     * @return list<array{id: int, response_id: string, status: string, encounter: int, version: int, answers: array<string, string>}>
     */
    private function savedResponses(): array
    {
        $rows = QueryUtils::fetchRecords(
            'SELECT id, response_id, status, encounter, version, questionnaire_response FROM questionnaire_response WHERE patient_id = ? ORDER BY id',
            [$this->pid]
        );
        $saved = [];
        foreach ($rows as $row) {
            $json = is_string($row['questionnaire_response']) ? $row['questionnaire_response'] : '{}';
            $resource = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
            $this->assertIsArray($resource);
            $saved[] = [
                'id' => $this->toInt($row['id']),
                'response_id' => is_string($row['response_id']) ? $row['response_id'] : '',
                'status' => is_string($row['status']) ? $row['status'] : '',
                'encounter' => $this->toInt($row['encounter'] ?? 0),
                'version' => $this->toInt($row['version']),
                'answers' => self::answers($resource),
            ];
        }
        return $saved;
    }

    /**
     * The first answer to each top-level question: valueString, or valueCoding's code.
     *
     * @param array<mixed> $resource a QuestionnaireResponse
     * @return array<string, string>
     */
    private static function answers(array $resource): array
    {
        $answers = [];
        $items = is_array($resource['item'] ?? null) ? $resource['item'] : [];
        foreach ($items as $item) {
            if (!is_array($item) || !is_string($item['linkId'] ?? null)) {
                continue;
            }
            $answer = is_array($item['answer'] ?? null) && is_array($item['answer'][0] ?? null) ? $item['answer'][0] : [];
            $coding = is_array($answer['valueCoding'] ?? null) ? $answer['valueCoding'] : [];
            $value = $answer['valueString'] ?? $coding['code'] ?? null;
            if (is_string($value)) {
                $answers[$item['linkId']] = $value;
            }
        }
        return $answers;
    }

    /**
     * Adds a patient and a questionnaire with a required text question and a choice; the
     * repository offers a new questionnaire on the dashboard, as an import does.
     */
    private function insertFixtureData(): void
    {
        $this->cleanUpFixtureData();

        $this->pid = $this->toInt(QueryUtils::fetchSingleValue('SELECT COALESCE(MAX(pid), 0) + 1 AS pid FROM patient_data', 'pid'));
        QueryUtils::sqlInsert(
            'INSERT INTO patient_data (pid, uuid, pubpid, fname, lname, DOB, sex, date) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())',
            [$this->pid, UuidRegistry::getRegistryForTable('patient_data')->createUuid(), self::FIXTURE_MARKER, 'Assess', 'Mentee', '1970-01-01', 'Female']
        );

        $this->questionnaireTitle = self::FIXTURE_MARKER . ' ' . bin2hex(random_bytes(4));
        $option = static fn(string $code, string $display): array => [
            'valueCoding' => ['system' => 'http://example.org/e2e-frequency', 'code' => $code, 'display' => $display],
        ];
        (new QuestionnaireService())->saveQuestionnaireResource([
            'resourceType' => 'Questionnaire',
            'status' => 'active',
            'title' => $this->questionnaireTitle,
            'item' => [
                ['linkId' => self::FEELING, 'text' => 'How are you feeling today?', 'type' => 'string', 'required' => true],
                [
                    'linkId' => self::HOW_OFTEN,
                    'text' => 'How often have you felt down this week?',
                    'type' => 'choice',
                    'answerOption' => [
                        $option('not-at-all', 'Not at all'),
                        $option('several-days', 'Several days'),
                        $option('most-days', 'Most days'),
                    ],
                ],
            ],
        ]);
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
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM uuid_registry WHERE uuid IN (SELECT uuid FROM patient_data WHERE pid = ?)',
                [$pid]
            );
        }
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
