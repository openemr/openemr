<?php

/**
 * Isolated tests for AssessmentCategoryGroups.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Questionnaire;

use OpenEMR\Services\Globals\CollapsedSections;
use OpenEMR\Services\Questionnaire\AssessmentCategoryGroups;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AssessmentCategoryGroupsTest extends TestCase
{
    #[Test]
    public function rowsAreGroupedByCategoryIdInTheOrderTheyArrive(): void
    {
        $groups = AssessmentCategoryGroups::fromRecords([
            ['id' => 1, 'category' => 'survey', 'category_title' => 'Survey'],
            ['id' => '2', 'category' => 'sdoh', 'category_title' => 'SDOH'],
            ['id' => 3, 'category' => 'survey', 'category_title' => 'Survey'],
        ], 'Unassigned');

        $this->assertSame(
            [CollapsedSections::categoryKey('survey'), CollapsedSections::categoryKey('sdoh')],
            array_keys($groups)
        );
        $survey = $groups[CollapsedSections::categoryKey('survey')];
        $this->assertSame('Survey', $survey['title']);
        $this->assertSame([1, 3], array_column($survey['records'], 'id'));
    }

    #[Test]
    public function idsThatShareAFallbackTitleStayInSeparateGroups(): void
    {
        $groups = AssessmentCategoryGroups::fromRecords([
            ['id' => 1, 'category' => 'a-b'],
            ['id' => 2, 'category' => 'a_b'],
        ], 'Unassigned');

        $this->assertCount(2, $groups);
        $this->assertSame(['A B', 'A B'], array_column($groups, 'title'));
    }

    #[Test]
    public function aRowWithNoCategoryGetsTheUnassignedHeading(): void
    {
        $groups = AssessmentCategoryGroups::fromRecords([['id' => 7, 'category' => null]], 'Sin asignar');

        $this->assertSame('Sin asignar', $groups[CollapsedSections::categoryKey('')]['title']);
    }

    #[Test]
    public function rowsWithoutAPositiveIdAreLeftOut(): void
    {
        $groups = AssessmentCategoryGroups::fromRecords([
            ['id' => 0, 'category' => 'survey'],
            ['id' => 'x', 'category' => 'survey'],
            ['category' => 'survey'],
        ], 'Unassigned');

        $this->assertSame([], $groups);
    }
}
