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
use OpenEMR\Services\Utils\SQLUpgradeService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Runs upgrade scripts through the #If* guards, including guards that name
 * reserved words or quote-bearing values, which break the guard query unless
 * identifiers are quoted and values bound. The reserved-word column is
 * `order` because MySQL and MariaDB both reserve it.
 */
class SQLUpgradeServiceGuardTest extends TestCase
{
    private const TABLE = 'sql_upgrade_guard_test';

    private Filesystem $filesystem;
    private string $scriptDir;

    protected function setUp(): void
    {
        parent::setUp();

        // Drop first: a table left by an aborted run may predate these columns.
        QueryUtils::sqlStatementThrowException('DROP TABLE IF EXISTS `' . self::TABLE . '`');
        QueryUtils::sqlStatementThrowException(<<<'SQL'
            CREATE TABLE `sql_upgrade_guard_test` (
              `id` INT NOT NULL AUTO_INCREMENT,
              `name` VARCHAR(64) NOT NULL,
              `order` VARCHAR(64) NULL,
              `label` VARCHAR(64) NOT NULL DEFAULT '',
              `kind` VARCHAR(8) NOT NULL DEFAULT 'row',
              `tick``mark` VARCHAR(64) NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB
            SQL);

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

    private function runScript(string $script): void
    {
        $this->filesystem->dumpFile($this->scriptDir . '/guard.sql', $script);
        (new SQLUpgradeService())
            ->setRenderOutputToScreen(false)
            ->setThrowExceptionOnError(true)
            ->upgradeFromSqlFile('guard.sql', $this->scriptDir);
    }

    /**
     * Runs the script twice, the way a repeated upgrade does.
     *
     * @param list<array{name: string, order: ?string}> $expected
     */
    #[DataProvider('scriptProvider')]
    public function testGuardedScriptRunsTwice(string $script, array $expected): void
    {
        $this->runScript($script);
        $this->runScript($script);

        self::assertSame(
            $expected,
            QueryUtils::fetchRecords('SELECT `name`, `order` FROM `' . self::TABLE . '` ORDER BY `id`'),
        );
    }

    public function testTableGuardMatchesCaseTheWayTheServerDoes(): void
    {
        $namesAreCaseSensitive = QueryUtils::fetchRecords('SELECT 1 FROM DUAL WHERE @@lower_case_table_names = 0') !== [];

        $this->runScript(<<<'UPGRADE_SQL'
            #IfTable SQL_UPGRADE_GUARD_TEST
            INSERT INTO `sql_upgrade_guard_test` (`name`) VALUES ('upper-case table name matched');
            #EndIf
            UPGRADE_SQL);

        self::assertCount(
            $namesAreCaseSensitive ? 0 : 1,
            QueryUtils::fetchRecords('SELECT `name` FROM `' . self::TABLE . '`'),
        );
    }

    /**
     * @return array<string, array{string, list<array{name: string, order: ?string}>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function scriptProvider(): array
    {
        return [
            'reserved-word column in a row guard' => [
                <<<'SQL'
                #IfNotRow2D sql_upgrade_guard_test name WenoExchange order start_weno
                INSERT INTO `sql_upgrade_guard_test` (`name`, `order`) VALUES ('WenoExchange', 'start_weno');
                #EndIf
                SQL,
                [['name' => 'WenoExchange', 'order' => 'start_weno']],
            ],
            'reserved-word column in a null-row guard' => [
                <<<'SQL'
                #IfNotRow sql_upgrade_guard_test name unset
                INSERT INTO `sql_upgrade_guard_test` (`name`) VALUES ('unset');
                #EndIf
                #IfRowIsNull sql_upgrade_guard_test order
                UPDATE `sql_upgrade_guard_test` SET `order` = 'filled' WHERE `order` IS NULL;
                INSERT INTO `sql_upgrade_guard_test` (`name`, `order`) VALUES ('null row found', 'once');
                #EndIf
                SQL,
                [
                    ['name' => 'unset', 'order' => 'filled'],
                    ['name' => 'null row found', 'order' => 'once'],
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
                    'order' => null,
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
                    ['name' => 'column exists', 'order' => null],
                    ['name' => 'index exists', 'order' => null],
                    ['name' => 'column exists', 'order' => null],
                    ['name' => 'index exists', 'order' => null],
                ],
            ],
            'backtick inside a column name' => [
                <<<'SQL'
                #IfNotRow sql_upgrade_guard_test tick`mark ticked
                INSERT INTO `sql_upgrade_guard_test` (`name`, `tick``mark`) VALUES ('escaped', 'ticked');
                #EndIf
                SQL,
                [['name' => 'escaped', 'order' => null]],
            ],
            'column names the directive already quoted' => [
                <<<'SQL'
                #IfNotRow2D sql_upgrade_guard_test `name` quoted `order` yes
                INSERT INTO `sql_upgrade_guard_test` (`name`, `order`) VALUES ('quoted', 'yes');
                #EndIf
                #IfNotRow sql_upgrade_guard_test `sql_upgrade_guard_test`.`tick``mark` qualified
                INSERT INTO `sql_upgrade_guard_test` (`name`, `tick``mark`) VALUES ('qualified', 'qualified');
                #EndIf
                SQL,
                [
                    ['name' => 'quoted', 'order' => 'yes'],
                    ['name' => 'qualified', 'order' => null],
                ],
            ],
            'row guards compare every column' => [
                <<<'SQL'
                #IfNotRow sql_upgrade_guard_test name near
                INSERT INTO `sql_upgrade_guard_test` (`name`, `order`, `label`, `kind`) VALUES ('near', 'o', 'l', 'k');
                #EndIf
                #IfNotRow2D sql_upgrade_guard_test name near order other
                INSERT INTO `sql_upgrade_guard_test` (`name`, `order`) VALUES ('near', 'other');
                #EndIf
                #IfNotRow3D sql_upgrade_guard_test name near order o label other
                INSERT INTO `sql_upgrade_guard_test` (`name`, `order`, `label`) VALUES ('near', 'o', 'other');
                #EndIf
                #IfNotRow4D sql_upgrade_guard_test name near order o label l kind other
                INSERT INTO `sql_upgrade_guard_test` (`name`, `order`, `label`, `kind`) VALUES ('near', 'o', 'l', 'other');
                #EndIf
                SQL,
                [
                    ['name' => 'near', 'order' => 'o'],
                    ['name' => 'near', 'order' => 'other'],
                    ['name' => 'near', 'order' => 'o'],
                    ['name' => 'near', 'order' => 'o'],
                ],
            ],
            'column type and default guards that match' => [
                <<<'SQL'
                #IfNotColumnTypeDefault sql_upgrade_guard_test order varchar(64) NULL
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
                #IfNotColumnTypeDefault sql_upgrade_guard_test order varchar(64)
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
                    ['name' => 'default is not blank', 'order' => null],
                    ['name' => 'default is not null', 'order' => null],
                    ['name' => 'default is not other', 'order' => null],
                    ['name' => 'type is not varchar(9)', 'order' => null],
                    ['name' => 'default is not blank', 'order' => null],
                    ['name' => 'default is not null', 'order' => null],
                    ['name' => 'default is not other', 'order' => null],
                    ['name' => 'type is not varchar(9)', 'order' => null],
                ],
            ],
        ];
    }
}
