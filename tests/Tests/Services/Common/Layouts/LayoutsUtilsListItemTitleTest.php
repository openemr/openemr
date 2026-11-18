<?php

/**
 * Database tests for LayoutsUtils::getListItemTitle()
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Common\Layouts;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Layouts\LayoutsUtils;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class LayoutsUtilsListItemTitleTest extends TestCase
{
    private const LIST_ID = 'zz_layouts_utils_test';

    protected function setUp(): void
    {
        $this->cleanUp();
        $rows = [
            ['active', 'Active title', 1],
            ['untitled', '', 1],
            ['zero-titled', '0', 1],
            ['inactive', 'Inactive title', 0],
        ];
        foreach ($rows as [$optionId, $title, $activity]) {
            QueryUtils::sqlStatementThrowException(
                'INSERT INTO list_options (list_id, option_id, title, activity) VALUES (?, ?, ?, ?)',
                [self::LIST_ID, $optionId, $title, $activity]
            );
        }
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
    }

    #[DataProvider('titleProvider')]
    public function testGetListItemTitle(string $optionId, string $expected, bool $translated): void
    {
        self::assertSame($translated ? xl_list_label($expected) : $expected, LayoutsUtils::getListItemTitle(self::LIST_ID, $optionId));
    }

    /**
     * @return array<string, array{string, string, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function titleProvider(): array
    {
        return [
            'active option with a title' => ['active', 'Active title', true],
            'blank title falls back to the option' => ['untitled', 'untitled', false],
            'title "0" falls back to the option' => ['zero-titled', 'zero-titled', false],
            'inactive option falls back to the option' => ['inactive', 'inactive', false],
            'unknown option falls back to the option' => ['missing', 'missing', false],
        ];
    }

    private function cleanUp(): void
    {
        QueryUtils::sqlStatementThrowException('DELETE FROM list_options WHERE list_id = ?', [self::LIST_ID]);
    }
}
