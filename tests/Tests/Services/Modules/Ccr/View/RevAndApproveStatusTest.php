<?php

/**
 * The CCR review screen posts each imported problem's and prescription's status, and the approval saves it as
 * 1 or 0 by comparing it with 'Active' and 'Inactive'. The screen filled an editable box with the translated word,
 * so on a site in another language a problem was saved with no status and a prescription stopped the approval.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Modules\Ccr\View;

use Application\Listener\Listener;
use Ccr\Model\CcrTable;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Laminas\Escaper\Escaper;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use PHPUnit\Framework\TestCase;

class RevAndApproveStatusTest extends TestCase
{
    private const PID = 990000321;

    private string $template;

    protected function setUp(): void
    {
        $template = realpath(__DIR__ . '/../../../../../../interface/modules/zend_modules/module/Ccr/view/ccr/ccr/revandapprove.phtml');
        $this->assertIsString($template);
        $this->template = $template;

        $this->cleanUp();
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_data (pid, uuid, fname, lname, `date`) VALUES (?, ?, 'test-fixture-ccr', 'test-fixture-ccr', NOW())",
            [self::PID, random_bytes(16)]
        );
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
        $left = QueryUtils::fetchSingleValue(
            "SELECT (SELECT COUNT(*) FROM patient_data WHERE pid = ?) + (SELECT COUNT(*) FROM lists WHERE pid = ?)"
            . " + (SELECT COUNT(*) FROM prescriptions WHERE patient_id = ?) AS n",
            'n',
            [self::PID, self::PID, self::PID]
        );
        $this->assertEquals(0, $left, 'the synthetic rows are removed');
    }

    public function testATranslatedSiteSavesTheStatusTheDocumentCarries(): void
    {
        // A site whose language translates the two status words.
        $translated = new class {
            public function z_xlt(mixed $text): string
            {
                $words = ['Active' => 'Aktiv', 'Inactive' => 'Inaktiv'];
                $text = is_scalar($text) ? (string) $text : '';
                return htmlspecialchars($words[$text] ?? $text, ENT_NOQUOTES);
            }
        };

        $html = $this->render($translated);
        $this->assertStringContainsString('Aktiv', $html, 'the reviewer sees the translated words');

        (new CcrTable())->insertApprovedData($this->approve($html));

        $this->assertSame($this->expectedProblems(), $this->savedProblems());
        $this->assertSame($this->expectedPrescriptions(), $this->savedPrescriptions());
    }

    public function testAnEnglishSiteSavesTheStatusTheDocumentCarries(): void
    {
        $html = $this->render(new Listener());

        (new CcrTable())->insertApprovedData($this->approve($html));

        $this->assertSame($this->expectedProblems(), $this->savedProblems());
        $this->assertSame($this->expectedPrescriptions(), $this->savedPrescriptions());
    }

    /**
     * @return list<array{string, int}>
     */
    private function expectedProblems(): array
    {
        return [['test-fixture-ccr active problem', 1], ['test-fixture-ccr inactive problem', 0]];
    }

    /**
     * @return list<array{string, int}>
     */
    private function expectedPrescriptions(): array
    {
        return [['test-fixture-ccr active drug', 1], ['test-fixture-ccr inactive drug', 0]];
    }

    /**
     * @return list<array{string, int|null}>
     */
    private function savedProblems(): array
    {
        $rows = QueryUtils::fetchRecords("SELECT title, activity FROM lists WHERE pid = ? ORDER BY id", [self::PID]);
        return array_map(static fn(array $row): array => [is_string($row['title']) ? $row['title'] : '', is_numeric($row['activity']) ? (int) $row['activity'] : null], $rows);
    }

    /**
     * @return list<array{string, int|null}>
     */
    private function savedPrescriptions(): array
    {
        $rows = QueryUtils::fetchRecords("SELECT drug, active FROM prescriptions WHERE patient_id = ? ORDER BY id", [self::PID]);
        return array_map(static fn(array $row): array => [is_string($row['drug']) ? $row['drug'] : '', is_numeric($row['active']) ? (int) $row['active'] : null], $rows);
    }

    /**
     * Renders the review screen for a document carrying two problems and two prescriptions, one active and one
     * inactive of each, as the controller does (the audit rows hold the status as 1 or 0).
     */
    private function render(object $listener): string
    {
        $variables = [
            'listenerObject' => $listener,
            'commonplugin' => null,
            'amid' => 0,
            'pid' => self::PID,
            'document_id' => 0,
            'demographics' => [],
            'demographics_old' => [[]],
            'problems' => [],
            'problems_audit' => ['lists1' => [
                1 => ['title' => 'test-fixture-ccr active problem', 'date' => '2026-02-03', 'diagnosis' => 'ZZ9.321', 'activity' => '1'],
                2 => ['title' => 'test-fixture-ccr inactive problem', 'date' => '2026-02-04', 'diagnosis' => 'ZZ9.322', 'activity' => '0'],
            ]],
            'allergies' => [],
            'allergies_audit' => ['lists2' => []],
            'medications' => [],
            'medications_audit' => ['prescriptions' => [
                1 => ['drug' => 'test-fixture-ccr active drug', 'date_added' => '2026-02-03', 'active' => '1', 'form' => '', 'size' => '', 'quantity' => '', 'refills' => ''],
                2 => ['drug' => 'test-fixture-ccr inactive drug', 'date_added' => '2026-02-04', 'active' => '0', 'form' => '', 'size' => '', 'quantity' => '', 'refills' => ''],
            ]],
            'immunizations' => [],
            'immunizations_audit' => ['immunizations' => []],
            'lab_results' => [],
            'lab_results_audit' => ['procedure_result,procedure_type' => []],
        ];

        // The template's view, as Laminas' PhpRenderer gives it: the controller's variables and the two helpers it
        // calls.
        $view = new class ($this->template, $variables) {
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
        // The template asks ApplicationTable::dateFormat() for a format without passing the site's, so each date
        // it shows raises two deprecations there and renders empty. That is a separate defect; only those two are
        // set aside here.
        $previous = null;
        $previous = set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline) use (&$previous): bool {
            if ($errno === E_DEPRECATED && str_ends_with($errfile, '/Application/Model/ApplicationTable.php')) {
                return true;
            }
            return is_callable($previous) && (bool) $previous($errno, $errstr, $errfile, $errline);
        });
        try {
            return $view->render();
        } finally {
            restore_error_handler();
            if (!$hadKey) {
                $session->remove('csrf_private_key');
            }
        }
    }

    /**
     * What the browser posts when the reviewer leaves every field as shown and chooses Insert for each problem
     * and prescription, with the identifiers the controller binds before it calls insertApprovedData().
     *
     * @return array<mixed>
     */
    private function approve(string $html): array
    {
        $document = new DOMDocument();
        $this->assertTrue(@$document->loadHTML($html));
        $xpath = new DOMXPath($document);

        $pairs = [];
        $inputs = $xpath->query("//form//input[@name][not(@type='submit')]");
        $this->assertNotFalse($inputs);
        foreach ($inputs as $input) {
            $this->assertInstanceOf(DOMElement::class, $input);
            $pairs[] = rawurlencode($input->getAttribute('name')) . '=' . rawurlencode($input->getAttribute('value'));
        }
        $selects = $xpath->query('//form//select[@name]');
        $this->assertNotFalse($selects);
        foreach ($selects as $select) {
            $this->assertInstanceOf(DOMElement::class, $select);
            $name = $select->getAttribute('name');
            if ($name === 'lists1-sel[]' || $name === 'prescriptions-sel[]') {
                $value = 'insert';
            } else {
                $options = $xpath->query('option[@selected]', $select);
                $this->assertNotFalse($options);
                $chosen = $options->item(0) ?? $select->getElementsByTagName('option')->item(0);
                $this->assertInstanceOf(DOMElement::class, $chosen);
                $value = $chosen->getAttribute('value');
            }
            $pairs[] = rawurlencode($name) . '=' . rawurlencode($value);
        }

        parse_str(implode('&', $pairs), $post);
        $post['amid'] = 0;
        $post['pid'] = self::PID;
        $post['document_id'] = 0;
        return $post;
    }

    private function cleanUp(): void
    {
        QueryUtils::sqlStatementThrowException("DELETE FROM lists WHERE pid = ?", [self::PID]);
        QueryUtils::sqlStatementThrowException("DELETE FROM prescriptions WHERE patient_id = ?", [self::PID]);
        QueryUtils::sqlStatementThrowException("DELETE FROM patient_data WHERE pid = ?", [self::PID]);
    }
}
