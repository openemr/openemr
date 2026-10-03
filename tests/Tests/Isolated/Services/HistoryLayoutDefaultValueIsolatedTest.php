<?php

/**
 * Isolated tests for HIS layout defaults on new history rows.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @copyright Copyright (c) 2026 Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services;

use OpenEMR\Common\Layouts\HistoryLayoutDefaults;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
final class HistoryLayoutDefaultValueIsolatedTest extends TestCase
{
    #[Test]
    public function layoutDefaultsFillMissingHistoryFields(): void
    {
        $merged = HistoryLayoutDefaults::apply(
            [],
            [
                ['field_id' => 'coffee', 'default_value' => 'never'],
                ['field_id' => 'tobacco', 'default_value' => 'current'],
                ['field_id' => 'pid', 'default_value' => '999'],
                ['field_id' => 'not_a_column', 'default_value' => 'x'],
            ],
            ['coffee', 'tobacco', 'pid'],
        );
        $this->assertSame('never', $merged['coffee']);
        $this->assertSame('current', $merged['tobacco']);
        $this->assertArrayNotHasKey('pid', $merged);
        $this->assertArrayNotHasKey('not_a_column', $merged);
    }

    #[Test]
    public function explicitlyClearedHistoryFieldsArePreserved(): void
    {
        $merged = HistoryLayoutDefaults::apply(
            ['coffee' => ''],
            [['field_id' => 'coffee', 'default_value' => 'never']],
            ['coffee'],
        );
        $this->assertSame('', $merged['coffee']);
    }

    #[Test]
    public function existingHistoryValuesAreNotOverwritten(): void
    {
        $merged = HistoryLayoutDefaults::apply(
            ['coffee' => 'often'],
            [['field_id' => 'coffee', 'default_value' => 'never']],
            ['coffee'],
        );
        $this->assertSame('often', $merged['coffee']);
    }

    #[Test]
    public function blankAndInvalidLayoutRowsAreIgnored(): void
    {
        $merged = HistoryLayoutDefaults::apply(
            [],
            [
                ['field_id' => 'coffee', 'default_value' => ''],
                ['field_id' => '1bad', 'default_value' => 'x'],
                ['field_id' => 'exercise', 'default_value' => 'walk'],
            ],
            ['coffee', 'exercise'],
        );
        $this->assertArrayNotHasKey('coffee', $merged);
        $this->assertArrayNotHasKey('1bad', $merged);
        $this->assertSame('walk', $merged['exercise']);
    }
}
