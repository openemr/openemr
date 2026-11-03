<?php

/**
 * Isolated tests for layout editor description translation cells.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @copyright Copyright (c) 2026 Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Layouts;

use OpenEMR\Common\Layouts\LayoutsUtils;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
final class LayoutEditorDescriptionTranslationIsolatedTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        // The real escaping helpers, so these assertions cover the escaping the
        // editor actually emits rather than a stub's approximation.
        require_once __DIR__ . '/../../../../../library/htmlspecialchars.inc.php';
    }

    #[Test]
    public function translationCellIsShownForNonEnglishWhenEnabled(): void
    {
        $this->assertTrue(LayoutsUtils::includeDescriptionTranslation(true, 2));
        $this->assertTrue(LayoutsUtils::includeDescriptionTranslation(true, '5'));
        $this->assertFalse(LayoutsUtils::includeDescriptionTranslation(true, 1));
        $this->assertFalse(LayoutsUtils::includeDescriptionTranslation(false, 2));
        $this->assertFalse(LayoutsUtils::includeDescriptionTranslation(true, null));
    }

    #[Test]
    public function staticTextDescriptionControlDoesNotSkipTranslationGate(): void
    {
        $this->assertTrue(LayoutsUtils::descriptionUsesTextarea(31));
        $this->assertTrue(LayoutsUtils::descriptionUsesTextarea('31'));
        $this->assertFalse(LayoutsUtils::descriptionUsesTextarea(null));
        $this->assertFalse(LayoutsUtils::descriptionUsesTextarea(2));
        $this->assertTrue(LayoutsUtils::includeDescriptionTranslation(true, 2));
    }

    #[Test]
    public function staticTextRowRendersTextareaAndTranslationCell(): void
    {
        $html = LayoutsUtils::descriptionEditorCellsHtml(
            31,
            'Hello <b>x</b>',
            4,
            true,
            'Bonjour <b>x</b>',
        );
        $this->assertStringContainsString("<textarea name='fld[4][desc]'", $html);
        $this->assertStringContainsString('Hello &lt;b&gt;x&lt;/b&gt;', $html);
        $this->assertStringContainsString("<td class='text-center translation'>", $html);
        $this->assertStringContainsString('Bonjour &lt;b&gt;x&lt;/b&gt;', $html);
        $this->assertStringNotContainsString("<input type='text' name='fld[4][desc]'", $html);
    }

    #[Test]
    public function staticTextRowOmitsTranslationCellWhenDisabled(): void
    {
        $html = LayoutsUtils::descriptionEditorCellsHtml(31, 'Hello', 4, false);
        $this->assertStringContainsString("<textarea name='fld[4][desc]'", $html);
        $this->assertStringNotContainsString("class='text-center translation'", $html);
    }

    #[Test]
    public function ordinaryFieldStillRendersTranslationCell(): void
    {
        $html = LayoutsUtils::descriptionEditorCellsHtml(2, 'Note', 1, true, 'Remarque');
        $this->assertStringContainsString("<input type='text' name='fld[1][desc]'", $html);
        $this->assertStringContainsString("<td class='text-center translation'>", $html);
        $this->assertStringNotContainsString('<textarea', $html);
    }
}
