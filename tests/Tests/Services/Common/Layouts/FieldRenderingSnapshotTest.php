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

use OpenEMR\Common\Session\PatientSessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Tests\Fixtures\LayoutFieldFixtureManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

final class FieldRenderingSnapshotTest extends TestCase
{
    private const FIXTURE_DIR = __DIR__ . '/fixtures';

    // Stable test sentinels used by the session wiring so any value that leaks
    // into rendered HTML is recognizable and deterministic. The pid is
    // intentionally a high integer that no seed data will collide with —
    // patient-allergies (24) reads pid from the session and queries lists;
    // with no rows for this pid, every environment renders the same empty
    // fragment.
    private const SITE_ID = 'default';
    private const AUTH_USER_ID = '1';
    private const TEST_PID = 999999;

    private static ?LayoutFieldFixtureManager $fixtures = null;

    /** @var array<string, array{present: bool, value: mixed}> previous state of touched $GLOBALS keys */
    private static array $previousGlobals = [];

    /** @var array{hadSession: bool, session: ?SessionInterface} */
    private static array $previousSession = ['hadSession' => false, 'session' => null];

    private static int $previousPid = 0;

    private static ?string $previousTimezone = null;

    /**
     * $GLOBALS keys this test class mutates (directly in setUp, or indirectly
     * by exercising the renderer) and is responsible for restoring. The
     * second group covers global state the renderer itself touches:
     *   - 'pid' is written by generate_form_field() for data_types 52–56
     *     (set to 0 / null when blank_form=true) via `global $pid`.
     *   - 'date_init' is appended to by data_type 40 (canvas) via
     *     `global $date_init`.
     *   - 'membership_group_number' is incremented by data_type 27 (radio
     *     buttons) via `global $membership_group_number`.
     */
    private const TOUCHED_GLOBALS = [
        'disable_translation',
        'fileroot',
        'webroot',
        'rootdir',
        'date_display_format',
        'time_display_format',
        'gbl_time_zone',
        'pid',
        'date_init',
        'membership_group_number',
    ];

    /**
     * @codeCoverageIgnore PHPUnit runs setUpBeforeClass before coverage
     * instrumentation starts, so its lines never register as hit even though
     * every test in the class depends on it.
     */
    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/../../../../../library/options.inc.php';

        // Snapshot pre-existing state so tearDownAfterClass can restore it.
        // phpunit.xml runs with processIsolation="false", so any global state
        // we mutate here can leak into unrelated tests and cause order-
        // dependent failures.
        // Track presence separately from value: a key may legitimately hold
        // null, which is not the same as being absent.
        foreach (self::TOUCHED_GLOBALS as $key) {
            self::$previousGlobals[$key] = [
                'present' => array_key_exists($key, $GLOBALS),
                'value'   => $GLOBALS[$key] ?? null,
            ];
        }
        $factory = SessionWrapperFactory::getInstance();
        self::$previousSession = [
            'hadSession' => $factory->isSessionActive(),
            'session'    => $factory->isSessionActive() ? $factory->getActiveSession() : null,
        ];
        self::$previousPid = PatientSessionUtil::getPid();

        // Pin the timezone so any renderer code path that reads PHP's
        // default timezone (e.g. Twig 'now'|date(...)) computes the same
        // value regardless of the host environment. UTC is the conservative
        // choice; restore in tearDownAfterClass.
        self::$previousTimezone = date_default_timezone_get();
        date_default_timezone_set('UTC');

        $GLOBALS['disable_translation'] = true;
        $GLOBALS['fileroot'] ??= dirname(__DIR__, 5);
        $GLOBALS['webroot'] ??= '';
        $GLOBALS['rootdir'] ??= '/interface';
        $GLOBALS['date_display_format'] ??= 0;
        $GLOBALS['time_display_format'] ??= 0;
        $GLOBALS['gbl_time_zone'] ??= 'UTC';

        // Inject a deterministic in-memory session with the keys the renderer
        // reads (site_id for canvas image lookup, authUserID for the
        // patient/admin signature branches).
        $session = new Session(new MockArraySessionStorage());
        $session->set('site_id', self::SITE_ID);
        $session->set('authUserID', self::AUTH_USER_ID);
        // CsrfUtils::collectCsrfToken throws when this key is missing; the
        // relation_form template calls it during data_type 56 rendering. The
        // value just has to be present and stable — the test sentinel makes
        // the derived token reproducible across runs.
        $session->set('csrf_private_key', '__test_layout_field_csrf__');
        $factory->setActiveSession($session);

        // Set the test pid via the session utility so all code paths see it.
        PatientSessionUtil::setPid(self::TEST_PID);

        self::$fixtures = new LayoutFieldFixtureManager();
        self::$fixtures->seed();
    }

    /**
     * @codeCoverageIgnore Runs after the last test in the class — PHPUnit
     * stops coverage instrumentation before invoking it.
     */
    public static function tearDownAfterClass(): void
    {
        self::$fixtures?->cleanup();
        self::$fixtures = null;

        // Restore $GLOBALS to its pre-setUp state. Only unset keys that were
        // genuinely absent before — a key that held null is restored to null.
        foreach (self::TOUCHED_GLOBALS as $key) {
            if (!self::$previousGlobals[$key]['present']) {
                unset($GLOBALS[$key]);
            } else {
                $GLOBALS[$key] = self::$previousGlobals[$key]['value'];
            }
        }

        // Restore the active session. SessionWrapperFactory has no public API
        // for clearing $activeSession back to null, so when no session was
        // active before, reset it via reflection.
        $factory = SessionWrapperFactory::getInstance();
        if (self::$previousSession['hadSession'] && self::$previousSession['session'] !== null) {
            $factory->setActiveSession(self::$previousSession['session']);
        } else {
            (new ReflectionProperty(SessionWrapperFactory::class, 'activeSession'))
                ->setValue($factory, null);
        }

        // Restore pid via the session utility.
        PatientSessionUtil::setPid(self::$previousPid);

        if (self::$previousTimezone !== null) {
            date_default_timezone_set(self::$previousTimezone);
            self::$previousTimezone = null;
        }
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
            $dir = dirname($fixturePath);
            if (!is_dir($dir) && !mkdir($dir, 0o755, true) && !is_dir($dir)) {
                self::fail("UPDATE_FIXTURES could not create directory: $dir");
            }
            if (file_put_contents($fixturePath, $rendered) === false) {
                self::fail("UPDATE_FIXTURES could not write fixture: $fixturePath");
            }
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
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     *
     * @codeCoverageIgnore Called from the data provider, which runs before
     * coverage instrumentation starts.
     */
    private static function baseFrow(int $dataType, string $fieldId, array $overrides = []): array
    {
        $defaults = [
            'data_type'      => $dataType,
            'field_id'       => $fieldId,
            'title'          => 'Test Field',
            'description'    => 'Test description',
            'list_id'        => null,
            'list_backup_id' => null,
            'edit_options'   => '',
            'form_id'        => 'DEM',
            'fld_length'     => 20,
            'fld_rows'       => 3,
            'max_length'     => 255,
            'seq'            => 1,
            'uor'            => 1,
            'source'         => 'F',
            'group_id'       => '1',
            'validation'     => '',
        ];
        // Overrides win over defaults; PHP's array_merge keeps right-hand keys.
        return array_merge($defaults, $overrides);
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
        $listId = LayoutFieldFixtureManager::LIST_ID;
        $optionId = LayoutFieldFixtureManager::LIST_OPTION_IDS[0];

        yield 'list-box' => [
            'data_type' => 1,
            'frow'      => self::baseFrow(1, 'test_list_box', ['list_id' => $listId]),
            'currvalue' => $optionId,
        ];
        yield 'textbox' => [
            'data_type' => 2,
            'frow'      => self::baseFrow(2, 'test_textbox'),
            'currvalue' => 'sample text',
        ];
        yield 'textarea' => [
            'data_type' => 3,
            'frow'      => self::baseFrow(3, 'test_textarea'),
            'currvalue' => "line one\nline two",
        ];
        yield 'text-date' => [
            'data_type' => 4,
            'frow'      => self::baseFrow(4, 'test_text_date'),
            'currvalue' => '2026-01-15',
        ];
        yield 'checkboxes' => [
            'data_type' => 21,
            'frow'      => self::baseFrow(21, 'test_checkboxes', ['list_id' => $listId]),
            'currvalue' => $optionId,
        ];
        yield 'textbox-list' => [
            'data_type' => 22,
            'frow'      => self::baseFrow(22, 'test_textbox_list', ['list_id' => $listId]),
            'currvalue' => $optionId . ':typed',
        ];
        yield 'checkboxes-with-text' => [
            'data_type' => 25,
            'frow'      => self::baseFrow(25, 'test_checkboxes_with_text', ['list_id' => $listId]),
            'currvalue' => $optionId . ':typed',
        ];
        yield 'list-box-with-add' => [
            'data_type' => 26,
            'frow'      => self::baseFrow(26, 'test_list_box_with_add', ['list_id' => $listId]),
            'currvalue' => $optionId,
        ];
        yield 'radio-buttons' => [
            'data_type' => 27,
            'frow'      => self::baseFrow(27, 'test_radio_buttons', ['list_id' => $listId]),
            'currvalue' => $optionId,
        ];
        yield 'static-text' => [
            'data_type' => 31,
            'frow'      => self::baseFrow(31, 'test_static_text', ['description' => 'Static label']),
            'currvalue' => '',
        ];
        yield 'multiple-select-list' => [
            'data_type' => 36,
            'frow'      => self::baseFrow(36, 'test_multiple_select_list', ['list_id' => $listId]),
            'currvalue' => $optionId,
        ];
        yield 'list-box-with-search' => [
            'data_type' => 43,
            'frow'      => self::baseFrow(43, 'test_list_box_with_search', ['list_id' => $listId]),
            'currvalue' => $optionId,
        ];
        yield 'list-box-with-comment' => [
            'data_type' => 46,
            'frow'      => self::baseFrow(46, 'test_list_box_with_comment', ['list_id' => $listId]),
            'currvalue' => $optionId,
        ];
        yield 'issue-types' => [
            'data_type' => 17,
            'frow'      => self::baseFrow(17, 'test_issue_types'),
            'currvalue' => 'medical_problem',
        ];
        yield 'lifestyle-status' => [
            'data_type' => 28,
            'frow'      => self::baseFrow(28, 'test_lifestyle_status', ['list_id' => $listId]),
            'currvalue' => $optionId . '|current|2026-01-15',
        ];
        yield 'smoking-status' => [
            'data_type' => 32,
            'frow'      => self::baseFrow(32, 'test_smoking_status', ['list_id' => 'smoking_status']),
            'currvalue' => '449868002|current|2026-01-15',
        ];
        yield 'race-ethnicity' => [
            'data_type' => 33,
            'frow'      => self::baseFrow(33, 'test_race_ethnicity', ['list_id' => $listId]),
            'currvalue' => $optionId,
        ];
        yield 'nation-notes' => [
            'data_type' => 34,
            'frow'      => self::baseFrow(34, 'test_nation_notes', ['list_id' => $listId]),
            'currvalue' => 'note body|*|*|*|',
        ];
        yield 'lab-results' => [
            'data_type' => 37,
            'frow'      => self::baseFrow(37, 'test_lab_results', ['list_id' => $listId]),
            'currvalue' => $optionId . '|positive|note',
        ];
        yield 'image-canvas' => [
            'data_type' => 40,
            'frow'      => self::baseFrow(40, 'test_image_canvas'),
            'currvalue' => '/images/test.png',
        ];
        yield 'patient-signature' => [
            'data_type' => 41,
            'frow'      => self::baseFrow(41, 'test_patient_signature'),
            'currvalue' => '/sig/patient.png',
        ];
        yield 'user-signature' => [
            'data_type' => 42,
            'frow'      => self::baseFrow(42, 'test_user_signature'),
            'currvalue' => '/sig/user.png',
        ];
        yield 'providers' => [
            'data_type' => 10,
            'frow'      => self::baseFrow(10, 'test_providers'),
            'currvalue' => '',
        ];
        yield 'providers-npi' => [
            'data_type' => 11,
            'frow'      => self::baseFrow(11, 'test_providers_npi'),
            'currvalue' => '',
        ];
        yield 'pharmacies' => [
            'data_type' => 12,
            'frow'      => self::baseFrow(12, 'test_pharmacies'),
            'currvalue' => '',
        ];
        yield 'squads' => [
            'data_type' => 13,
            'frow'      => self::baseFrow(13, 'test_squads'),
            'currvalue' => '',
        ];
        yield 'address-book' => [
            'data_type' => 14,
            'frow'      => self::baseFrow(14, 'test_address_book'),
            'currvalue' => '',
        ];
        yield 'billing-codes' => [
            'data_type' => 15,
            'frow'      => self::baseFrow(15, 'test_billing_codes'),
            'currvalue' => '',
        ];
        yield 'insurances' => [
            'data_type' => 16,
            'frow'      => self::baseFrow(16, 'test_insurances'),
            'currvalue' => '',
        ];
        yield 'visit-categories' => [
            'data_type' => 18,
            'frow'      => self::baseFrow(18, 'test_visit_categories'),
            'currvalue' => '',
        ];
        yield 'exam-results' => [
            'data_type' => 23,
            'frow'      => self::baseFrow(23, 'test_exam_results', ['list_id' => $listId]),
            'currvalue' => $optionId . '|positive|note',
        ];
        yield 'facilities' => [
            'data_type' => 35,
            'frow'      => self::baseFrow(35, 'test_facilities'),
            'currvalue' => '',
        ];
        yield 'multi-select-facilities' => [
            'data_type' => 44,
            'frow'      => self::baseFrow(44, 'test_multi_select_facilities'),
            'currvalue' => '',
        ];
        yield 'multi-select-provider' => [
            'data_type' => 45,
            'frow'      => self::baseFrow(45, 'test_multi_select_provider'),
            'currvalue' => '',
        ];
        yield 'patient-allergies' => [
            'data_type' => 24,
            'frow'      => self::baseFrow(24, 'test_patient_allergies'),
            'currvalue' => '',
        ];
        yield 'patient-name' => [
            'data_type' => 51,
            'frow'      => self::baseFrow(51, 'test_patient_name'),
            'currvalue' => '',
        ];
        yield 'previous-names' => [
            'data_type' => 52,
            'frow'      => self::baseFrow(52, 'test_previous_names'),
            'currvalue' => '',
        ];
        yield 'patient-encounters-list' => [
            'data_type' => 53,
            'frow'      => self::baseFrow(53, 'test_patient_encounters_list'),
            'currvalue' => '',
        ];
        yield 'address-list' => [
            'data_type' => 54,
            'frow'      => self::baseFrow(54, 'test_address_list', ['blank_form' => true]),
            'currvalue' => '',
        ];
        yield 'telecom-list' => [
            'data_type' => 55,
            'frow'      => self::baseFrow(55, 'test_telecom_list', ['blank_form' => true]),
            'currvalue' => '',
        ];
        yield 'related-person-list' => [
            'data_type' => 56,
            'frow'      => self::baseFrow(56, 'test_related_person_list', ['blank_form' => true]),
            'currvalue' => '',
        ];
    }

    /**
     * @param array<string, mixed> $frow
     */
    private static function captureRendererOutput(string $mode, array $frow, string $currvalue): string
    {
        // The legacy renderer triggers E_DEPRECATED in several branches (null
        // arguments to substr(), htmlspecialchars(), etc.). Those are real
        // issues, but fixing them is out of scope for this snapshot harness,
        // which exists to capture the bytes the renderer emits today.
        // Suppress E_DEPRECATED for the duration of the call so PHPUnit's
        // failOnDeprecation does not fight the snapshot.
        $returned = '';
        set_error_handler(static fn (int $errno): bool => ($errno & (E_DEPRECATED | E_USER_DEPRECATED)) !== 0, E_DEPRECATED | E_USER_DEPRECATED);
        ob_start();
        try {
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
                // @codeCoverageIgnoreStart
                // Defensive: every $mode value comes from the static modes
                // list in renderCases(), so this arm only fires if that list
                // gains a value the switch hasn't been updated for. Excluded
                // from coverage because no normal test execution reaches it.
                default:
                    throw new \InvalidArgumentException("Unknown render mode: $mode");
                // @codeCoverageIgnoreEnd
            }
        } finally {
            // ob_get_clean() both retrieves and closes the buffer, so the
            // success and exception paths share one cleanup. restore_error_handler()
            // also runs unconditionally so the suppressed-deprecation handler
            // never leaks past this call.
            $echoed = ob_get_clean();
            restore_error_handler();
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
        // uniqid() returns 13 hex chars. Address/telecom/relation templates
        // build DOM ids like "table_edit_addresses_<uniqid>" — match the
        // underscore prefix so the regex doesn't lock onto coincidental hex
        // runs in unrelated content (the prior \b-anchored version missed
        // these matches because _ is a word character).
        $deflaked = (string) preg_replace('/_[0-9a-f]{13}\b/', '___UNIQ__', $stripped);
        // ContactService::getOrCreateForEntity('patient_data', 0) creates a
        // contact row on first call and reuses it after; its autoinc id
        // varies across CI shards and across local dev databases. Replace
        // the value attribute when it follows a contact_id-style hidden
        // field name so the address/telecom/relation fixtures stay stable.
        $deflaked = (string) preg_replace(
            '/(\[contact(?:_address)?_id\]" value=)"\d+"/',
            '$1"__ID__"',
            $deflaked
        );
        // The relation_form template also embeds the contact id in an inline
        // JS literal: `const ownerContactId = N;`. Same volatility, same fix.
        $deflaked = (string) preg_replace(
            '/(const ownerContactId = )\d+;/',
            '$1__ID__;',
            $deflaked
        );
        // DB-driven dropdowns (data_types 10, 11, 14, 16, 35, 44, 45) render
        // <option> rows pulled from users / facility / insurance_companies /
        // etc. Their contents vary between a fresh install and the
        // 5.0.0→current SQL-upgrade chain shard, so capturing them verbatim
        // would couple the snapshot to a specific database seed. Elide the
        // option block but keep the surrounding <select> attributes — those
        // are what the renderer is responsible for.
        $dbDrivenIds = [
            'providers',
            'providers_npi',
            'address_book',
            'insurances',
            'facilities',
            'multi_select_facilities',
            'multi_select_provider',
        ];
        foreach ($dbDrivenIds as $id) {
            $deflaked = (string) preg_replace(
                "/(<select\\b[^>]*\\bid=['\"]form_test_" . preg_quote($id, '/') . "['\"][^>]*>).*?(<\\/select>)/s",
                "$1<!-- options elided -->$2",
                $deflaked
            );
        }
        // Address/telecom/relation Twig templates default period_start and
        // start_date inputs to {{ 'now'|date('Y-m-d') }}; without
        // normalization today's date leaks into the rendered output and the
        // snapshot starts failing on the next calendar day. Scope the
        // substitution to those specific bracketed field names so a fixed
        // test-input date that happens to equal today (e.g. data_type 4's
        // currvalue on the literal day '2026-01-15') is not rewritten.
        $deflaked = (string) preg_replace(
            "/(\\[(?:period_start|start_date)\\][^>]*value=')\\d{4}-\\d{2}-\\d{2}'/",
            "$1__TODAY__'",
            $deflaked
        );
        // Pre-commit end-of-file-fixer leaves empty files empty and otherwise
        // enforces a single trailing newline. Match that exactly so fixtures
        // round-trip without drift.
        $trimmed = rtrim($deflaked, "\n");
        return $trimmed === '' ? '' : $trimmed . "\n";
    }
}
