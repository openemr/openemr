<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Patient;

use OpenEMR\Services\Patient\DuplicatePatientColumn;
use OpenEMR\Services\Patient\DuplicatePatientRow;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class DuplicatePatientRowTest extends TestCase
{
    /** The score above which a chart is flagged on the report. */
    private const HIGHLIGHT = 17;

    protected function setUp(): void
    {
        $GLOBALS['date_display_format'] ??= 0;
        $GLOBALS['disable_translation'] = true;
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    private static function patientRow(array $overrides = []): array
    {
        return array_merge([
            'pid' => '7',
            'pubpid' => 'PUB7',
            'dupscore' => '14',
            'lname' => 'Nakamura',
            'fname' => 'Aiko',
            'mname' => 'R',
            'DOB' => '1984-11-02 00:00:00',
            'sex' => 'Female',
            'email' => 'aiko@example.com',
            'phone_home' => ' 555-1000 ',
            'phone_biz' => '',
            'phone_cell' => '555-2000',
            'regdate' => '2020-04-01',
            'street' => '12 Elm St',
        ], $overrides);
    }

    #[Test]
    public function primaryRowUsesTheStoredScoreAndPointsAtItself(): void
    {
        $row = DuplicatePatientRow::forPrimary(self::patientRow(), self::HIGHLIGHT);

        $this->assertSame(7, $row->pid);
        $this->assertSame(7, $row->topPid, 'a primary row is its own group anchor');
        $this->assertFalse($row->isMatch);
        $this->assertSame(14, $row->score);
    }

    #[Test]
    public function matchRowUsesItsScoreAgainstThePrimaryAndKeepsTheGroupAnchor(): void
    {
        $row = DuplicatePatientRow::forMatch(
            self::patientRow(['pid' => '9', 'myscore' => '20']),
            7,
            self::HIGHLIGHT
        );

        $this->assertSame(9, $row->pid);
        $this->assertSame(7, $row->topPid);
        $this->assertTrue($row->isMatch);
        $this->assertSame(20, $row->score, 'a match is scored against the primary, not by its cached dupscore');
    }

    /**
     * Column renderers read the raw patient_data row through field(), so any column present in the
     * query is reachable without the row having to know about it.
     */
    #[Test]
    public function exposesRawPatientFieldsToColumnRenderers(): void
    {
        $row = DuplicatePatientRow::forPrimary(self::patientRow(['ss' => '111-22-3333']), self::HIGHLIGHT);

        $this->assertSame('Nakamura', $row->field('lname'));
        $this->assertSame('111-22-3333', $row->field('ss'), 'a field core does not display is still readable');
        $this->assertSame('', $row->field('no_such_column'));
    }

    #[Test]
    public function rendersCellsKeyedByColumn(): void
    {
        $row = DuplicatePatientRow::forPrimary(self::patientRow(), self::HIGHLIGHT);
        $this->assertSame([], $row->getValues(), 'nothing is rendered until the column list is known');

        $row->renderCells([
            DuplicatePatientColumn::forField('pubpid', 'Public'),
            new DuplicatePatientColumn('shouty', 'Shouty', static fn(DuplicatePatientRow $r): string
                => strtoupper($r->field('lname'))),
        ]);

        $this->assertSame(['pubpid' => 'PUB7', 'shouty' => 'NAKAMURA'], $row->getValues());
    }

    /**
     * The stored dupscore drives the highlight for every row; a high score against this particular
     * primary then promotes a match to the chart everything should be merged into.
     *
     * @param array<string, mixed> $overrides
     */
    #[Test]
    #[DataProvider('highlightProvider')]
    public function highlightsRowsByScore(
        array $overrides,
        bool $isMatch,
        string $expectedClass,
        string $expectedScope
    ): void {
        $source = self::patientRow($overrides);
        $row = $isMatch
            ? DuplicatePatientRow::forMatch($source, 7, self::HIGHLIGHT)
            : DuplicatePatientRow::forPrimary($source, self::HIGHLIGHT);

        $this->assertSame($expectedClass, $row->highlightClass);
        $this->assertSame($expectedScope, $row->scopeLabel);
    }

    /**
     * @return array<string, array{array<string, mixed>, bool, string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function highlightProvider(): array
    {
        return [
            'primary below the highlight threshold' => [
                ['dupscore' => '17'], false, '', '',
            ],
            'primary above the highlight threshold' => [
                ['dupscore' => '18'], false, 'highlight', DuplicatePatientRow::SCOPE_MERGE_FROM,
            ],
            'match scoring low against this primary keeps its own highlight' => [
                ['dupscore' => '18', 'myscore' => '13'], true, 'highlight', DuplicatePatientRow::SCOPE_MERGE_FROM,
            ],
            'match scoring high against this primary becomes the merge target' => [
                ['dupscore' => '14', 'myscore' => '20'], true, 'highlight-master', DuplicatePatientRow::SCOPE_MERGE_TO,
            ],
            'unscored row is not highlighted' => [
                ['dupscore' => '0'], false, '', '',
            ],
        ];
    }

    /**
     * A deployment that lowers the threshold flags more charts without any other change.
     */
    #[Test]
    public function respectsAConfiguredHighlightThreshold(): void
    {
        $source = self::patientRow(['dupscore' => '10']);

        $this->assertSame('', DuplicatePatientRow::forPrimary($source, 17)->highlightClass);
        $this->assertSame('highlight', DuplicatePatientRow::forPrimary($source, 7)->highlightClass);
    }

    #[Test]
    public function toleratesMissingColumns(): void
    {
        $row = DuplicatePatientRow::forPrimary(['pid' => 3], self::HIGHLIGHT);

        $this->assertSame(3, $row->pid);
        $this->assertSame(0, $row->score);
        $this->assertSame('', $row->field('street'));
    }
}
