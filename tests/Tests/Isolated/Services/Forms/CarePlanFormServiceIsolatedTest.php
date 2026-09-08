<?php

/**
 * Isolated tests for the pure helpers on CarePlanFormService.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (C) 2025 Open Plan IT Ltd. <support@openplanit.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Forms;

use OpenEMR\Services\Forms\CarePlanFormService;
use OpenEMR\Services\FormService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class CarePlanFormServiceIsolatedTest extends TestCase
{
    private CarePlanFormService $service;

    protected function setUp(): void
    {
        $this->service = new CarePlanFormService($this->createMock(FormService::class));
    }

    #[Test]
    #[DataProvider('parseNoteProvider')]
    public function parseNoteExtractsIssueReferences(string $note, string $expected): void
    {
        self::assertSame($expected, $this->service->parseNote($note));
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function parseNoteProvider(): array
    {
        return [
            'no markers' => ['just a plain description', '[]'],
            'empty string' => ['', '[]'],
            'single marker' => ['see {|Diabetes|} today', '["Diabetes"]'],
            'empty marker' => ['{||}', '[""]'],
            // Documents current behaviour, which is not what you would want. The
            // character class [^\]] excludes only ']', so the capture runs greedily
            // across the closing '|}' of the first marker and swallows everything up
            // to the last one. Faithfully ported from the global parse_note() in
            // library/global_functions.inc.php:677 -- fixing it changes the
            // note_related_to values written for every multi-issue description, so it
            // belongs in its own change, not a refactor.
            'multiple markers collapse into one capture' => [
                '{|Asthma|} and {|COPD|}',
                '["Asthma|} and {|COPD"]',
            ],
        ];
    }

    #[Test]
    #[DataProvider('normalizeNullableStringProvider')]
    public function normalizeNullableStringTrimsAndNullsBlanks(string $value, ?string $expected): void
    {
        self::assertSame($expected, $this->service->normalizeNullableString($value));
    }

    /**
     * @return array<string, array{string, string|null}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function normalizeNullableStringProvider(): array
    {
        return [
            'empty string' => ['', null],
            'whitespace only' => ['   ', null],
            'padded value' => ['  2026-01-05 09:00  ', '2026-01-05 09:00'],
            'plain value' => ['2026-01-05 09:00', '2026-01-05 09:00'],
        ];
    }
}
