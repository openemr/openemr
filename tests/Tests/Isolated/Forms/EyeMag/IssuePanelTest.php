<?php

/**
 * Isolated tests for the eye exam issue panels and their subtype filters.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Forms\EyeMag;

use OpenEMR\Forms\EyeMag\IssueQuickPick;
use OpenEMR\Forms\EyeMag\PmsfhPanel;
use OpenEMR\Forms\EyeMag\SubtypeFilter;
use PHPUnit\Framework\TestCase;

class IssuePanelTest extends TestCase
{
    /**
     * Panels built from other sources have no case, so callers can recognize
     * them by a null lookup.
     */
    public function testDerivedPanelsHaveNoCase(): void
    {
        foreach (['FH', 'SOCH', 'ROS'] as $derived) {
            $this->assertNull(PmsfhPanel::tryFrom($derived));
            $this->assertNull(IssueQuickPick::tryFrom($derived));
        }
    }

    public function testEveryFilterBindsItsSubtypeRatherThanInterpolatingIt(): void
    {
        foreach (SubtypeFilter::cases() as $filter) {
            $condition = $filter->condition();

            $this->assertSame(
                substr_count($condition->sql, '?'),
                count($condition->params),
                "{$filter->name} has a placeholder without a bind",
            );
        }
    }

    public function testAnyFilterAddsNoPredicate(): void
    {
        $condition = SubtypeFilter::Any->condition();

        $this->assertSame('', $condition->sql);
        $this->assertSame([], $condition->params);
    }

    public function testBlankOrNullFilterMatchesRowsStoredEitherWay(): void
    {
        $condition = SubtypeFilter::BlankOrNull->condition();

        $this->assertSame('AND (subtype = ? OR subtype IS NULL)', $condition->sql);
        $this->assertSame([''], $condition->params);
    }

    public function testOphthalmicPanelsFilterOnTheEyeSubtype(): void
    {
        foreach ([PmsfhPanel::POH, PmsfhPanel::POS, PmsfhPanel::EyeMeds] as $panel) {
            $this->assertSame(SubtypeFilter::Eye, $panel->subtypeFilter());
        }

        foreach ([IssueQuickPick::POH, IssueQuickPick::POS, IssueQuickPick::EyeMeds] as $panel) {
            $this->assertSame(SubtypeFilter::Eye, $panel->subtypeFilter());
        }
    }

    public function testOphthalmicAndGeneralPanelsShareAnIssueTypeButNotASubtype(): void
    {
        $this->assertSame(PmsfhPanel::POH->issueType(), PmsfhPanel::PMH->issueType());
        $this->assertNotSame(PmsfhPanel::POH->subtypeFilter(), PmsfhPanel::PMH->subtypeFilter());

        $this->assertSame(PmsfhPanel::POS->issueType(), PmsfhPanel::Surgery->issueType());
        $this->assertSame(PmsfhPanel::EyeMeds->issueType(), PmsfhPanel::Medication->issueType());
    }

    public function testOnlySurgeryReadsMostRecentFirst(): void
    {
        $this->assertSame('ORDER BY begdate DESC', PmsfhPanel::Surgery->orderBy());

        foreach (PmsfhPanel::cases() as $panel) {
            if ($panel !== PmsfhPanel::Surgery) {
                $this->assertSame('ORDER BY title', $panel->orderBy());
            }
        }
    }

    public function testRecentTitlesQueryBindsEveryPlaceholder(): void
    {
        foreach (IssueQuickPick::cases() as $panel) {
            $query = $panel->recentTitlesQuery(42);

            $this->assertSame(
                substr_count($query->sql, '?'),
                count($query->params),
                "{$panel->value} has a placeholder without a bind",
            );
            $this->assertSame($panel->issueType(), $query->params[0]);
            $this->assertContains(42, $query->params);
            $this->assertContains($panel->recentLimit(), $query->params);
        }
    }

    public function testRecentTitlesQueryBindsTheLimitAsAnInt(): void
    {
        // ADODB's emulated binds quote string values, so a string limit would
        // render as LIMIT '20' and fail.
        $query = IssueQuickPick::PMH->recentTitlesQuery(1);

        $this->assertStringContainsString('LIMIT ?', $query->sql);
        $this->assertSame(20, $query->params[array_key_last($query->params)]);
    }

    public function testStockTitlesQuerySplitsOphthalmicFromGeneralLists(): void
    {
        $this->assertStringContainsString('AND subtype = ?', IssueQuickPick::POH->stockTitlesQuery()->sql);
        $this->assertStringContainsString('AND subtype NOT LIKE ?', IssueQuickPick::PMH->stockTitlesQuery()->sql);

        foreach (IssueQuickPick::cases() as $panel) {
            $query = $panel->stockTitlesQuery();

            $this->assertSame([$panel->listOptionsId(), 'eye'], $query->params);
        }
    }

    public function testQuickPickPanelsAgreeWithPmsfhPanelsOnIssueType(): void
    {
        foreach (IssueQuickPick::cases() as $panel) {
            $pmsfh = PmsfhPanel::from($panel->value);

            $this->assertSame($pmsfh->issueType(), $panel->issueType());
        }
    }
}
