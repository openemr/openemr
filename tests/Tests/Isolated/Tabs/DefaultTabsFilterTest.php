<?php

/**
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Tabs;

use OpenEMR\Tabs\DefaultTabsFilter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class DefaultTabsFilterTest extends TestCase
{
    /**
     * Real file inside the repository that every fixture can resolve to.
     * Picked because it lives at a stable project-root-relative path
     * and is not a generated artifact.
     */
    private const FIXTURE_NOTES = 'composer.json';

    private static function projectRoot(): string
    {
        return dirname(__DIR__, 4);
    }

    public function testFilterReturnsEmptyWhenInputIsNotAnArray(): void
    {
        $filter = new DefaultTabsFilter();
        $this->assertSame([], $filter->filter('not-an-array', self::projectRoot()));
        $this->assertSame([], $filter->filter(null, self::projectRoot()));
        $this->assertSame([], $filter->filter(42, self::projectRoot()));
    }

    public function testFilterReturnsEmptyForEmptyArray(): void
    {
        $filter = new DefaultTabsFilter();
        $this->assertSame([], $filter->filter([], self::projectRoot()));
    }

    public function testFilterReturnsEmptyWhenProjectRootDoesNotResolve(): void
    {
        $filter = new DefaultTabsFilter();
        $tabs = [
            ['notes' => self::FIXTURE_NOTES, 'option_id' => 'cal', 'title' => 'Calendar'],
        ];
        $missingRoot = '/nonexistent_root_' . uniqid('', true);
        $this->assertSame([], $filter->filter($tabs, $missingRoot));
    }

    public function testFilterAcceptsModernShape(): void
    {
        $filter = new DefaultTabsFilter();
        $tabs = [
            ['notes' => self::FIXTURE_NOTES, 'option_id' => 'cal', 'title' => 'Calendar'],
        ];

        $this->assertSame(
            [['notes' => self::FIXTURE_NOTES, 'option_id' => 'cal', 'title' => 'Calendar']],
            $filter->filter($tabs, self::projectRoot()),
        );
    }

    public function testFilterNormalizesLegacyIdAndLabelKeys(): void
    {
        $filter = new DefaultTabsFilter();
        $tabs = [
            ['notes' => self::FIXTURE_NOTES, 'id' => 'adm', 'label' => 'Password Reset'],
        ];

        $this->assertSame(
            [['notes' => self::FIXTURE_NOTES, 'option_id' => 'adm', 'title' => 'Password Reset']],
            $filter->filter($tabs, self::projectRoot()),
        );
    }

    public function testFilterPrefersModernKeysWhenBothShapesArePresent(): void
    {
        $filter = new DefaultTabsFilter();
        $tabs = [
            [
                'notes' => self::FIXTURE_NOTES,
                'option_id' => 'modern_id',
                'id' => 'legacy_id',
                'title' => 'Modern Title',
                'label' => 'Legacy Label',
            ],
        ];

        $this->assertSame(
            [['notes' => self::FIXTURE_NOTES, 'option_id' => 'modern_id', 'title' => 'Modern Title']],
            $filter->filter($tabs, self::projectRoot()),
        );
    }

    public function testFilterStripsQueryStringFromNotesBeforeResolving(): void
    {
        $filter = new DefaultTabsFilter();
        $tabs = [
            ['notes' => self::FIXTURE_NOTES . '?foo=bar&baz=qux', 'option_id' => 'cal', 'title' => 'Calendar'],
        ];

        // The notes value itself is preserved so the rendered URL keeps its query string.
        $this->assertSame(
            [['notes' => self::FIXTURE_NOTES . '?foo=bar&baz=qux', 'option_id' => 'cal', 'title' => 'Calendar']],
            $filter->filter($tabs, self::projectRoot()),
        );
    }

    /**
     * @return array<string, array{0: array<int, mixed>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function dropProvider(): array
    {
        $valid_notes = self::FIXTURE_NOTES;
        return [
            'non-array entry' => [['not-an-array']],
            'missing notes' => [[['option_id' => 'cal', 'title' => 'Calendar']]],
            'missing both option_id and id' => [[['notes' => $valid_notes, 'title' => 'Calendar']]],
            'missing both title and label' => [[['notes' => $valid_notes, 'option_id' => 'cal']]],
            'non-string notes' => [[['notes' => 42, 'option_id' => 'cal', 'title' => 'Calendar']]],
            'non-string option_id' => [[['notes' => $valid_notes, 'option_id' => 42, 'title' => 'Calendar']]],
            'non-string title' => [[['notes' => $valid_notes, 'option_id' => 'cal', 'title' => 42]]],
            'notes points outside project root' => [[['notes' => '../etc/passwd', 'option_id' => 'cal', 'title' => 'Calendar']]],
            'notes contains dotdot segment' => [[['notes' => 'src/../etc/passwd', 'option_id' => 'cal', 'title' => 'Calendar']]],
            'notes targets nonexistent file' => [[['notes' => 'no_such_file_' . self::class . '.php', 'option_id' => 'cal', 'title' => 'Calendar']]],
            'notes targets a directory' => [[['notes' => 'src', 'option_id' => 'cal', 'title' => 'Calendar']]],
            'notes is empty after stripping query' => [[['notes' => '?foo=bar', 'option_id' => 'cal', 'title' => 'Calendar']]],
        ];
    }

    /**
     * @param array<int, mixed> $tabs
     */
    #[DataProvider('dropProvider')]
    public function testFilterDropsInvalidEntries(array $tabs): void
    {
        $filter = new DefaultTabsFilter();
        $this->assertSame([], $filter->filter($tabs, self::projectRoot()));
    }

    public function testFilterPreservesValidEntriesAlongsideInvalidOnes(): void
    {
        $filter = new DefaultTabsFilter();
        $tabs = [
            ['not-an-array'],
            ['notes' => self::FIXTURE_NOTES, 'option_id' => 'cal', 'title' => 'Calendar'],
            ['notes' => '../etc/passwd', 'option_id' => 'bad', 'title' => 'Bad'],
            ['notes' => self::FIXTURE_NOTES, 'id' => 'adm', 'label' => 'Password Reset'],
        ];

        $this->assertSame(
            [
                ['notes' => self::FIXTURE_NOTES, 'option_id' => 'cal', 'title' => 'Calendar'],
                ['notes' => self::FIXTURE_NOTES, 'option_id' => 'adm', 'title' => 'Password Reset'],
            ],
            $filter->filter($tabs, self::projectRoot()),
        );
    }
}
