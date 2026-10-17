<?php

/**
 * Isolated tests for CdaSocialHistoryEntry.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Cda;

use OpenEMR\Services\Cda\CdaSocialHistoryEntry;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class CdaSocialHistoryEntryTest extends TestCase
{
    private const SMOKING = ['root' => '2.16.840.1.113883.10.20.22.4.78'];

    public function testReadsASmokingStatusWithEveryOptionalPartMissing(): void
    {
        $this->assertSame(
            ['field' => 'smoking', 'record' => [
                'extension' => null, 'root' => null, 'status' => '449868002', 'begdate' => null, 'enddate' => null, 'value' => null,
            ]],
            CdaSocialHistoryEntry::read(['observation' => ['templateId' => self::SMOKING, 'value' => ['code' => '449868002']]])
        );
    }

    public function testALowDateWinsOverAPointInTime(): void
    {
        $entry = CdaSocialHistoryEntry::read(['observation' => [
            'templateId' => self::SMOKING,
            'effectiveTime' => ['value' => '20240121', 'low' => ['value' => '20200101']],
            'value' => ['code' => '8517006'],
        ]]);
        $this->assertSame('20200101', $entry['record']['begdate'] ?? null);
    }

    #[DataProvider('skippedProvider')]
    public function testSkips(mixed $entry): void
    {
        $this->assertNull(CdaSocialHistoryEntry::read($entry));
    }

    /**
     * @return array<string, array{mixed}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function skippedProvider(): array
    {
        return [
            'null entry' => [null],
            'no observation' => [['act' => []]],
            'no value code' => [['observation' => ['templateId' => self::SMOKING, 'value' => ['nullFlavor' => 'UNK']]]],
            'empty value code' => [['observation' => ['templateId' => self::SMOKING, 'value' => ['code' => '']]]],
            'value code 0, as empty() treated it' => [['observation' => ['templateId' => self::SMOKING, 'value' => ['code' => '0']]]],
            'value code not a string' => [['observation' => ['templateId' => self::SMOKING, 'value' => ['code' => ['x']]]]],
            'another template' => [['observation' => ['templateId' => ['root' => '2.16.840.1.113883.10.20.22.4.85'], 'value' => ['code' => '1']]]],
            'no templateId' => [['observation' => ['value' => ['code' => '449868002']]]],
        ];
    }
}
