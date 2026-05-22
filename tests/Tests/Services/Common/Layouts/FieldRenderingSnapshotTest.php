<?php

/**
 * Snapshot the HTML output of every layout-field renderer branch.
 *
 * library/options.inc.php dispatches on the integer $frow['data_type'] through
 * a ~40-branch if/elseif cascade across three render modes (edit, on-screen
 * display, printable). This test captures the bytes each branch emits today,
 * so a future refactor of the cascade (most likely behind a FieldDataType
 * enum and a match expression) can be validated as behavior-preserving.
 *
 * Regenerate fixtures after intentional renderer changes:
 *   composer update-layout-field-fixtures
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Common\Layouts;

use OpenEMR\Tests\Fixtures\LayoutFieldFixtureManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FieldRenderingSnapshotTest extends TestCase
{
    private const FIXTURE_DIR = __DIR__ . '/fixtures';

    private static ?LayoutFieldFixtureManager $fixtures = null;

    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/../../../../../library/options.inc.php';

        $GLOBALS['disable_translation'] = true;
        $GLOBALS['fileroot'] ??= dirname(__DIR__, 5);
        $GLOBALS['webroot'] ??= '';
        $GLOBALS['rootdir'] ??= '/interface';
        $GLOBALS['date_display_format'] ??= 0;
        $GLOBALS['time_display_format'] ??= 0;
        $GLOBALS['gbl_time_zone'] ??= 'UTC';

        self::$fixtures = new LayoutFieldFixtureManager();
        self::$fixtures->seed();
    }

    public static function tearDownAfterClass(): void
    {
        self::$fixtures?->cleanup();
        self::$fixtures = null;
    }

    /**
     * @param array<string, mixed> $frow
     */
    #[Test]
    #[DataProvider('renderCases')]
    public function rendererProducesExpectedOutput(
        string $mode,
        int $dataType,
        array $frow,
        string $currvalue,
        string $fixturePath
    ): void {
        $rendered = self::normalize(self::captureRendererOutput($mode, $frow, $currvalue));

        // @codeCoverageIgnoreStart
        if (getenv('UPDATE_FIXTURES') === '1') {
            if (!is_dir(dirname($fixturePath))) {
                mkdir(dirname($fixturePath), 0o755, true);
            }
            file_put_contents($fixturePath, $rendered);
            self::markTestSkipped("Fixture updated: $fixturePath");
        }
        // @codeCoverageIgnoreEnd

        $expected = file_get_contents($fixturePath);
        self::assertIsString($expected, "Missing fixture: $fixturePath -- regenerate with: composer update-layout-field-fixtures");
        self::assertSame(
            $expected,
            $rendered,
            "Snapshot mismatch for mode={$mode} data_type={$dataType}\n"
            . "If the renderer change was intentional, regenerate with:\n"
            . "  composer update-layout-field-fixtures\n"
            . "Review the diff with `git diff -- " . dirname($fixturePath) . "` before committing."
        );
    }

    /**
     * Yields one tuple per (mode, data_type) snapshot case.
     *
     * @return iterable<string, array{string, int, array<string, mixed>, string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function renderCases(): iterable
    {
        // Render modes the dispatcher supports: 'edit' (generate_form_field),
        // 'display' (generate_display_field), 'print' (generate_print_field).
        $modes = ['edit', 'display', 'print'];

        foreach (self::layoutCases() as $caseId => $case) {
            foreach ($modes as $mode) {
                $slug = $mode . '/' . $caseId . '.html';
                yield "$mode $caseId" => [
                    $mode,
                    $case['data_type'],
                    $case['frow'],
                    $case['currvalue'],
                    self::FIXTURE_DIR . '/' . $slug,
                ];
            }
        }
    }

    /**
     * Base $frow used by every case. Per-case overrides are merged on top.
     *
     * @return array<string, mixed>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    private static function baseFrow(int $dataType, string $fieldId): array
    {
        return [
            'data_type'    => $dataType,
            'field_id'     => $fieldId,
            'title'        => 'Test Field',
            'description'  => 'Test description',
            'list_id'      => null,
            'list_backup_id' => null,
            'edit_options' => '',
            'form_id'      => 'DEM',
            'fld_length'   => 20,
            'fld_rows'     => 3,
            'max_length'   => 255,
            'seq'          => 1,
            'uor'          => 1,
            'source'       => 'F',
            'group_id'     => '1',
            'validation'   => '',
        ];
    }

    /**
     * Cases (data_type → frow + currvalue). Add cases incrementally; one
     * case will produce three fixtures (edit/display/print).
     *
     * @return iterable<string, array{data_type: int, frow: array<string, mixed>, currvalue: string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    private static function layoutCases(): iterable
    {
        yield 'textbox' => [
            'data_type' => 2,
            'frow'      => self::baseFrow(2, 'test_textbox'),
            'currvalue' => 'sample text',
        ];
    }

    /**
     * @param array<string, mixed> $frow
     */
    private static function captureRendererOutput(string $mode, array $frow, string $currvalue): string
    {
        // generate_form_field and generate_print_field echo; generate_display_field
        // returns a string. Wrap each call so both shapes capture cleanly.
        ob_start();
        try {
            $returned = '';
            switch ($mode) {
                case 'edit':
                    generate_form_field($frow, $currvalue);
                    break;
                case 'display':
                    $displayResult = generate_display_field($frow, $currvalue);
                    $returned = is_string($displayResult) ? $displayResult : '';
                    break;
                case 'print':
                    generate_print_field($frow, $currvalue);
                    break;
            }
            $echoed = ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        $echoedStr = $echoed === false ? '' : $echoed;
        return $echoedStr . $returned;
    }

    /**
     * Apply byte-stability normalizers in one place.
     *
     * - Trailing whitespace per line (pre-commit hooks strip these anyway).
     * - PHP uniqid()-style DOM ids → __UNIQ__ placeholder.
     */
    private static function normalize(string $html): string
    {
        $stripped = implode("\n", array_map(rtrim(...), explode("\n", $html)));
        // uniqid() returns 13 hex chars; widgets often embed it in DOM ids.
        $deflaked = (string) preg_replace('/\b[0-9a-f]{13}\b/', '__UNIQ__', $stripped);
        // Pre-commit end-of-file-fixer leaves empty files empty and otherwise
        // enforces a single trailing newline. Match that exactly so fixtures
        // round-trip without drift.
        $trimmed = rtrim($deflaked, "\n");
        return $trimmed === '' ? '' : $trimmed . "\n";
    }
}
