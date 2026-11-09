<?php

/**
 * The review screen for a C-CDA document shows the patient's latest history row beside what the document carries.
 * That row may hold a smoking status and no alcohol (a first import that carried only a smoking status) or neither
 * (a new patient's first row); either renders without warnings.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Modules\CareCoordination\View;

use Application\Listener\Listener;
use Carecoordination\Model\CarecoordinationTable;
use Laminas\Escaper\Escaper;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Services\Cda\CdaSocialHistoryWriter;
use OpenEMR\Services\SocialHistoryService;
use OpenEMR\Tests\Services\Cda\CdaSocialHistoryWriterTest;
use PHPUnit\Framework\TestCase;

class RevAndApproveSocialHistoryTest extends TestCase
{
    private int $pid;
    private string $template;

    protected function setUp(): void
    {
        $template = realpath(__DIR__ . '/../../../../../../interface/modules/zend_modules/module/Carecoordination/view/carecoordination/carecoordination/revandapprove.phtml');
        $this->assertIsString($template);
        $this->template = $template;

        $next = QueryUtils::fetchSingleValue("SELECT COALESCE(MAX(pid), 0) + 1 AS next_pid FROM patient_data", 'next_pid');
        $this->assertIsNumeric($next);
        $this->pid = (int) $next;
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_data (pid, uuid, fname, lname, `date`) VALUES (?, ?, 'test-fixture-review', 'test-fixture-review', NOW())",
            [$this->pid, random_bytes(16)]
        );
    }

    protected function tearDown(): void
    {
        CdaSocialHistoryWriterTest::removeHistory($this->pid);
        QueryUtils::sqlStatementThrowException("DELETE FROM patient_data WHERE pid = ?", [$this->pid]);
        $left = QueryUtils::fetchSingleValue(
            "SELECT (SELECT COUNT(*) FROM history_data WHERE pid = ?) + (SELECT COUNT(*) FROM patient_data WHERE pid = ?) AS n",
            'n',
            [$this->pid, $this->pid]
        );
        $this->assertEquals(0, $left, 'the synthetic rows are removed');
    }

    public function testAHistoryWrittenByATobaccoOnlyImportRendersWithoutWarnings(): void
    {
        (new CdaSocialHistoryWriter(new SocialHistoryService()))->write($this->pid, ['tobacco' => 'Ex-smoker (finding)|quittobacco|2024-01-21']);
        $table = new CarecoordinationTable();
        $history = $table->getSocialHistory(['pid' => $this->pid]);
        $this->assertCount(1, $history);
        $row = $history[0];
        $this->assertIsArray($row);
        $this->assertNull($row['alcohol'], 'the first import writes no alcohol');

        [$section, $warnings] = $this->renderSocialHistory($table, $history);

        $this->assertSame([], $warnings);
        $this->assertStringContainsString('Ex-smoker (finding)', $section);
        $this->assertStringContainsString('Quit', $section);
    }

    public function testANewPatientsEmptyHistoryRendersWithoutWarnings(): void
    {
        // As newHistoryData() leaves a patient created on the new patient screen.
        (new SocialHistoryService())->create(['pid' => $this->pid]);
        $table = new CarecoordinationTable();
        $history = $table->getSocialHistory(['pid' => $this->pid]);
        $this->assertCount(1, $history);
        $row = $history[0];
        $this->assertIsArray($row);
        $this->assertNull($row['tobacco']);

        [, $warnings] = $this->renderSocialHistory($table, $history);

        $this->assertSame([], $warnings);
    }

    /**
     * Renders the review screen with the patient's history and a document that carries only a smoking status, as
     * the controller does, and returns the Social History table and the warnings raised while rendering it.
     *
     * @param array<mixed> $history
     * @return array{string, list<string>}
     */
    private function renderSocialHistory(CarecoordinationTable $table, array $history): array
    {
        $lines = file($this->template);
        $this->assertIsArray($lines);
        $first = $last = null;
        foreach ($lines as $index => $line) {
            if (str_contains($line, "z_xlt('Social History')")) {
                $first = $index + 1;
            } elseif ($first !== null && str_contains($line, "z_xlt('Encounter')")) {
                $last = $index + 1;
                break;
            }
        }
        $this->assertNotNull($first, 'the template has a Social History table');
        $this->assertNotNull($last, 'an Encounter table follows it');

        // The template's view, as Laminas' PhpRenderer gives it: the controller's variables and the two helpers it
        // calls. The renderer's own setup (resolver, helper plugins) is deprecated in laminas-view 2.40.
        $view = new class ($this->template, $this->viewVariables($table, $history)) {
            /**
             * @param array<string, mixed> $variables
             */
            public function __construct(private readonly string $templateFile, private readonly array $variables)
            {
            }

            public function __get(string $name): mixed
            {
                return $this->variables[$name] ?? null;
            }

            public function escapeHtml(mixed $value): string
            {
                return (new Escaper('utf-8'))->escapeHtml(is_scalar($value) ? (string) $value : '');
            }

            public function basePath(): string
            {
                return '/interface/modules/zend_modules/public';
            }

            public function render(): string
            {
                ob_start();
                try {
                    include $this->templateFile;
                } finally {
                    $html = ob_get_clean();
                }
                return is_string($html) ? $html : '';
            }
        };

        // The form carries a CSRF token, made from the session's key, which a login sets up.
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $hadKey = $session->has('csrf_private_key');
        if (!$hadKey) {
            CsrfUtils::setupCsrfKey($session);
        }

        $warnings = [];
        set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline) use (&$warnings, $first, $last): bool {
            if ($errfile === $this->template && $errline >= $first && $errline <= $last) {
                $warnings[] = "line $errline: $errstr";
            }
            return true;
        });
        try {
            $html = $view->render();
        } finally {
            restore_error_handler();
            if (!$hadKey) {
                $session->remove('csrf_private_key');
            }
        }

        $start = strpos($html, 'Social History');
        $end = strpos($html, 'Encounter', $start === false ? 0 : $start);
        $this->assertIsInt($start);
        $this->assertIsInt($end);
        return [substr($html, $start, $end - $start), $warnings];
    }

    /**
     * The controller's view variables, empty but for the social history.
     *
     * @param array<mixed> $history
     * @return array<string, mixed>
     */
    private function viewVariables(CarecoordinationTable $table, array $history): array
    {
        $variables = [
            'carecoordinationTable' => $table,
            'commonplugin' => null,
            'listenerObject' => new Listener(),
            'social_history' => $history,
            // As the parser leaves a document that carries only a smoking status.
            'social_history_audit' => ['social_history' => [1 => ['extension' => 'review-history', 'smoking' => 'Former smoker|8517006|20240121']]],
            'tobacco' => $table->getList('smoking_status'),
            'amid' => 0,
            'pid' => $this->pid,
            'document_id' => 0,
            'documentationOf' => '',
            'demographics_old' => [[]],
        ];
        foreach (['demographics', 'problems', 'allergies', 'medications', 'immunizations', 'lab_results', 'vitals', 'encounter', 'procedure', 'care_plan', 'functional_cognitive_status', 'referral', 'discharge_summary', 'gender_list', 'country_list', 'marital_status_list', 'religion_list', 'race_list', 'ethnicity_list', 'state_list'] as $name) {
            $variables[$name] = [];
        }
        $audits = [
            'problems_audit' => 'lists1',
            'allergies_audit' => 'lists2',
            'medications_audit' => 'lists3',
            'immunizations_audit' => 'immunization',
            'lab_results_audit' => 'procedure_result',
            'vitals_audit' => 'vital_sign',
            'encounter_audit' => 'encounter',
            'procedure_audit' => 'procedure',
            'care_plan_audit' => 'care_plan',
            'functional_cognitive_status_audit' => 'functional_cognitive_status',
            'referral_audit' => 'referral',
            'discharge_medication_audit' => 'discharge_medication',
            'discharge_summary_audit' => 'discharge_summary',
        ];
        foreach ($audits as $name => $tableName) {
            $variables[$name] = [$tableName => []];
        }
        return $variables;
    }
}
