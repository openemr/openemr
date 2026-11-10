<?php

/**
 * Isolated tests for CollapsedSections.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Globals;

use OpenEMR\Services\Globals\CollapsedSections;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CollapsedSectionsTest extends TestCase
{
    #[Test]
    public function aSectionWithNoSavedChoiceStartsOpen(): void
    {
        $this->assertFalse((new CollapsedSections())->isCollapsed('questionnaire_import_review_expand'));
    }

    #[Test]
    public function onlyTheNamedSectionsStartCollapsed(): void
    {
        $sections = new CollapsedSections(['questionnaire_import_review_expand']);

        $this->assertTrue($sections->isCollapsed('questionnaire_import_review_expand'));
        $this->assertFalse($sections->isCollapsed('questionnaire_import_add_expand'));
    }

    #[Test]
    public function categoryIdsThatDifferOnlyInPunctuationGetTheirOwnKey(): void
    {
        $this->assertNotSame(CollapsedSections::categoryKey('a-b'), CollapsedSections::categoryKey('a_b'));
        $this->assertSame(CollapsedSections::categoryKey('a-b'), CollapsedSections::categoryKey('a-b'));
        $this->assertMatchesRegularExpression('/^questionnaire_category_[0-9a-f]+_expand$/', CollapsedSections::categoryKey('a-b'));
        $this->assertSame('questionnaire_category_unassigned_expand', CollapsedSections::categoryKey(''));
    }
}
