<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Utils;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Services\Utils\SQLUpgradeService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Runs upgrade scripts through the #If* guards, including guards that name
 * reserved words or quote-bearing values, which break the guard query unless
 * identifiers are quoted and values bound. Each script runs twice, the way a
 * repeated upgrade does.
 */
class SQLUpgradeServiceGuardTest extends TestCase
{
    private const TABLE = 'sql_upgrade_guard_test';

    private Filesystem $filesystem;
    private string $scriptDir;

    protected function setUp(): void
    {
        parent::setUp();

        QueryUtils::sqlStatementThrowException(<<<'SQL'
            CREATE TABLE IF NOT EXISTS `sql_upgrade_guard_test` (
              `id` INT NOT NULL AUTO_INCREMENT,
              `name` VARCHAR(64) NOT NULL,
              `function` VARCHAR(64) NULL,
              `label` VARCHAR(64) NOT NULL DEFAULT '',
              `kind` VARCHAR(8) NOT NULL DEFAULT 'row',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB
            SQL);
        QueryUtils::sqlStatementThrowException('DELETE FROM `' . self::TABLE . '`');

        $this->filesystem = new Filesystem();
        $this->scriptDir = sys_get_temp_dir() . '/' . uniqid('sql_upgrade_guard_', true);
        $this->filesystem->mkdir($this->scriptDir, 0700);
    }

    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException('DROP TABLE IF EXISTS `' . self::TABLE . '`');
        $this->filesystem->remove($this->scriptDir);

        parent::tearDown();
    }

    /**
     * @param list<array{name: string, function: ?string}> $expected
     */
    #[DataProvider('scriptProvider')]
    public function testGuardedScriptRunsTwice(string $script, array $expected): void
    {
        $this->filesystem->dumpFile($this->scriptDir . '/guard.sql', $script);
        $service = (new SQLUpgradeService())
            ->setRenderOutputToScreen(false)
            ->setThrowExceptionOnError(true);

        $service->upgradeFromSqlFile('guard.sql', $this->scriptDir);
        $service->upgradeFromSqlFile('guard.sql', $this->scriptDir);

        self::assertSame(
            $expected,
            QueryUtils::fetchRecords('SELECT `name`, `function` FROM `' . self::TABLE . '` ORDER BY `id`'),
        );
    }

    public function testBacktickInColumnNameStaysInsideTheIdentifier(): void
    {
        $this->filesystem->dumpFile($this->scriptDir . '/guard.sql', <<<'SQL'
            #IfNotRow sql_upgrade_guard_test name`=`name unknown
            INSERT INTO `sql_upgrade_guard_test` (`name`) VALUES ('unknown');
            #EndIf
            SQL);
        $service = (new SQLUpgradeService())
            ->setRenderOutputToScreen(false)
            ->setThrowExceptionOnError(true);

        // Wrapping the name without escaping would end the identifier at the first backtick.
        $this->expectException(SqlQueryException::class);

        $service->upgradeFromSqlFile('guard.sql', $this->scriptDir);
    }

    public function testTableGuardMatchesCaseTheWayTheServerDoes(): void
    {
        $this->filesystem->dumpFile($this->scriptDir . '/guard.sql', <<<'UPGRADE_SQL'
            #IfTable SQL_UPGRADE_GUARD_TEST
            INSERT INTO `sql_upgrade_guard_test` (`name`) VALUES ('upper-case table name matched');
            #EndIf
            UPGRADE_SQL);
        $service = (new SQLUpgradeService())
            ->setRenderOutputToScreen(false)
            ->setThrowExceptionOnError(true);
        $namesAreCaseSensitive = QueryUtils::fetchRecords('SELECT 1 FROM DUAL WHERE @@lower_case_table_names = 0') !== [];

        $service->upgradeFromSqlFile('guard.sql', $this->scriptDir);

        self::assertCount(
            $namesAreCaseSensitive ? 0 : 1,
            QueryUtils::fetchRecords('SELECT `name` FROM `' . self::TABLE . '`'),
        );
    }

    /**
     * @return array<string, array{string, list<array{name: string, function: ?string}>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function scriptProvider(): array
    {
        return [
            'reserved-word column in a row guard' => [
                <<<'SQL'
                #IfNotRow2D sql_upgrade_guard_test name WenoExchange function start_weno
                INSERT INTO `sql_upgrade_guard_test` (`name`, `function`) VALUES ('WenoExchange', 'start_weno');
                #EndIf
                SQL,
                [['name' => 'WenoExchange', 'function' => 'start_weno']],
            ],
            'reserved-word column in a null-row guard' => [
                <<<'SQL'
                #IfNotRow sql_upgrade_guard_test name unset
                INSERT INTO `sql_upgrade_guard_test` (`name`) VALUES ('unset');
                #EndIf
                #IfRowIsNull sql_upgrade_guard_test function
                UPDATE `sql_upgrade_guard_test` SET `function` = 'filled' WHERE `function` IS NULL;
                INSERT INTO `sql_upgrade_guard_test` (`name`, `function`) VALUES ('null row found', 'once');
                #EndIf
                SQL,
                [
                    ['name' => 'unset', 'function' => 'filled'],
                    ['name' => 'null row found', 'function' => 'once'],
                ],
            ],
            'guard value containing a quote' => [
                <<<'SQL'
                #IfNotRow sql_upgrade_guard_test name O'Brien
                INSERT INTO `sql_upgrade_guard_test` (`name`) VALUES ('O''Brien');
                #EndIf
                SQL,
                [[
                    'name' => <<<'NAME'
                        O'Brien
                        NAME,
                    'function' => null,
                ]],
            ],
            'reserved-word table in column guards' => [
                <<<'SQL'
                #IfNotColumnType keys name varchar(20)
                INSERT INTO `sql_upgrade_guard_test` (`name`) VALUES ('type differs');
                #EndIf
                #IfColumn keys name
                INSERT INTO `sql_upgrade_guard_test` (`name`) VALUES ('column exists');
                #EndIf
                #IfIndex keys name
                INSERT INTO `sql_upgrade_guard_test` (`name`) VALUES ('index exists');
                #EndIf
                #IfNotIndex keys name
                INSERT INTO `sql_upgrade_guard_test` (`name`) VALUES ('index missing');
                #EndIf
                #IfIndex keys no_such_index
                INSERT INTO `sql_upgrade_guard_test` (`name`) VALUES ('other index exists');
                #EndIf
                SQL,
                [
                    ['name' => 'column exists', 'function' => null],
                    ['name' => 'index exists', 'function' => null],
                    ['name' => 'column exists', 'function' => null],
                    ['name' => 'index exists', 'function' => null],
                ],
            ],
            'column name the directive already quoted' => [
                <<<'SQL'
                #IfNotRow2D sql_upgrade_guard_test `name` quoted `function` yes
                INSERT INTO `sql_upgrade_guard_test` (`name`, `function`) VALUES ('quoted', 'yes');
                #EndIf
                SQL,
                [['name' => 'quoted', 'function' => 'yes']],
            ],
            'row guards on three and four columns' => [
                <<<'SQL'
                #IfNotRow3D sql_upgrade_guard_test name three function f label l
                INSERT INTO `sql_upgrade_guard_test` (`name`, `function`, `label`) VALUES ('three', 'f', 'l');
                #EndIf
                #IfNotRow4D sql_upgrade_guard_test name four function f label l kind k
                INSERT INTO `sql_upgrade_guard_test` (`name`, `function`, `label`, `kind`) VALUES ('four', 'f', 'l', 'k');
                #EndIf
                SQL,
                [
                    ['name' => 'three', 'function' => 'f'],
                    ['name' => 'four', 'function' => 'f'],
                ],
            ],
            'column type and default guards that match' => [
                <<<'SQL'
                #IfNotColumnTypeDefault sql_upgrade_guard_test function varchar(64) NULL
                INSERT INTO `sql_upgrade_guard_test` (`name`) VALUES ('null default differs');
                #EndIf
                #IfNotColumnTypeDefault sql_upgrade_guard_test label varchar(64)
                INSERT INTO `sql_upgrade_guard_test` (`name`) VALUES ('blank default differs');
                #EndIf
                #IfNotColumnTypeDefault sql_upgrade_guard_test kind varchar(8) row
                INSERT INTO `sql_upgrade_guard_test` (`name`) VALUES ('value default differs');
                #EndIf
                #IfNotColumnTypeDefault sql_upgrade_guard_test missing varchar(8) row
                INSERT INTO `sql_upgrade_guard_test` (`name`) VALUES ('missing column default differs');
                #EndIf
                #IfNotColumnType sql_upgrade_guard_test missing varchar(8)
                INSERT INTO `sql_upgrade_guard_test` (`name`) VALUES ('missing column type differs');
                #EndIf
                SQL,
                [],
            ],
            'column type and default guards that differ' => [
                <<<'SQL'
                #IfNotColumnTypeDefault sql_upgrade_guard_test function varchar(64)
                INSERT INTO `sql_upgrade_guard_test` (`name`) VALUES ('default is not blank');
                #EndIf
                #IfNotColumnTypeDefault sql_upgrade_guard_test label varchar(64) NULL
                INSERT INTO `sql_upgrade_guard_test` (`name`) VALUES ('default is not null');
                #EndIf
                #IfNotColumnTypeDefault sql_upgrade_guard_test kind varchar(8) other
                INSERT INTO `sql_upgrade_guard_test` (`name`) VALUES ('default is not other');
                #EndIf
                #IfNotColumnTypeDefault sql_upgrade_guard_test kind varchar(9) row
                INSERT INTO `sql_upgrade_guard_test` (`name`) VALUES ('type is not varchar(9)');
                #EndIf
                SQL,
                [
                    ['name' => 'default is not blank', 'function' => null],
                    ['name' => 'default is not null', 'function' => null],
                    ['name' => 'default is not other', 'function' => null],
                    ['name' => 'type is not varchar(9)', 'function' => null],
                    ['name' => 'default is not blank', 'function' => null],
                    ['name' => 'default is not null', 'function' => null],
                    ['name' => 'default is not other', 'function' => null],
                    ['name' => 'type is not varchar(9)', 'function' => null],
                ],
            ],
        ];
    }
}
