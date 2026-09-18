<?php

/**
 * Exercises history creation against stored HIS layout defaults.
 *
 * @package OpenEMR
 * @author Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @copyright Copyright (c) 2026 Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\SocialHistoryService;
use PHPUnit\Framework\TestCase;

final class HistoryLayoutDefaultsTest extends TestCase
{
    protected function setUp(): void
    {
        // @phpstan-ignore openemr.deprecatedSqlFunction (Tests roll back fixture changes in tearDown.)
        QueryUtils::startTransaction();
        QueryUtils::sqlStatementThrowException(
            "UPDATE layout_options SET default_value = ?, uor = 1 WHERE form_id = ? AND field_id = ?",
            ['never', 'HIS', 'coffee']
        );
        QueryUtils::sqlStatementThrowException(
            "UPDATE layout_options SET default_value = ?, uor = 0 WHERE form_id = ? AND field_id = ?",
            ['hidden-default', 'HIS', 'tobacco']
        );
    }

    protected function tearDown(): void
    {
        // @phpstan-ignore openemr.deprecatedSqlFunction (Tests must roll back even when assertions fail.)
        QueryUtils::rollbackTransaction();
    }

    public function testNewHistoryStoresDefaultsButSkipsDisabledFields(): void
    {
        newHistoryData(987654321);
        $history = (new SocialHistoryService())->getHistoryData(987654321);
        self::assertIsArray($history);
        self::assertSame('never', $history['coffee']);
        self::assertNotSame('hidden-default', $history['tobacco']);
        self::assertEquals(987654321, $history['pid']);
        self::assertNotEmpty($history['uuid']);
    }

    public function testExplicitValuesWinAndLaterLayoutEditsDoNotChangeStoredHistory(): void
    {
        newHistoryData(987654322, ['coffee' => 'often', 'pid' => 987654321]);
        QueryUtils::sqlStatementThrowException(
            "UPDATE layout_options SET default_value = ? WHERE form_id = ? AND field_id = ?",
            ['changed-default', 'HIS', 'coffee']
        );
        $history = (new SocialHistoryService())->getHistoryData(987654322);
        self::assertIsArray($history);
        self::assertSame('often', $history['coffee']);
        self::assertEquals(987654322, $history['pid']);
    }

    public function testExplicitlyClearedFieldsAreNotReplacedByLayoutDefaults(): void
    {
        newHistoryData(987654323, ['coffee' => '']);
        $history = (new SocialHistoryService())->getHistoryData(987654323);
        self::assertIsArray($history);
        self::assertSame('', $history['coffee']);
        self::assertEquals(987654323, $history['pid']);
    }
}
