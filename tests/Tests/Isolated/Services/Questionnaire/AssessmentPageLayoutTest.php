<?php

/**
 * Isolated tests for AssessmentPageLayout.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Questionnaire;

use OpenEMR\Services\Questionnaire\AssessmentPageLayout;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AssessmentPageLayoutTest extends TestCase
{
    #[Test]
    public function aUserStartsInTheWidthTheQuestionnaireGlobalSets(): void
    {
        $this->assertSame('1', AssessmentPageLayout::defaultWidth(false));
        $this->assertSame('0', AssessmentPageLayout::defaultWidth(true));
    }

    #[Test]
    public function theSettingFollowsTheOemrUiNamingConvention(): void
    {
        $this->assertStringEndsWith('_xpd', AssessmentPageLayout::EXPAND_SETTING);
    }
}
