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
 * Runs upgrade scripts whose guards name reserved words or quote-bearing
 * values, which break the guard query unless identifiers are quoted and
 * values bound. Each script runs twice, the way a repeated upgrade does.
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
                #EndIf
                SQL,
                [['name' => 'unset', 'function' => 'filled']],
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
                SQL,
                [
                    ['name' => 'column exists', 'function' => null],
                    ['name' => 'index exists', 'function' => null],
                    ['name' => 'column exists', 'function' => null],
                    ['name' => 'index exists', 'function' => null],
                ],
            ],
        ];
    }
}
