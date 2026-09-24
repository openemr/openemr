<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * Keeps every custom module's install and upgrade SQL safe to re-run.
 *
 * Installing a module's SQL again (Module Manager, or `openemr:zfc-module
 * --modaction=install_sql`) re-runs the whole script against an existing
 * install, so a statement SQLUpgradeService would execute unconditionally runs
 * again each time. The walk below mirrors SQLUpgradeService::upgradeFromSqlFile():
 * directives are case-sensitive and must start in column 0, and anything else
 * starting with `#` is a comment.
 *
 * Zend module install scripts are out of scope: they go through a different
 * installer path. Composer-installed modules are untracked, so their own
 * repos carry this check.
 */
#[Group('isolated')]
class ModuleSqlIdempotencyTest extends TestCase
{
    /**
     * Directives SQLUpgradeService evaluates, each with the argument pattern
     * it must match. Any other `#` line, including a directive missing an
     * argument, is a comment, so the statements under it are unguarded.
     */
    private const GUARD_DIRECTIVES = [
        'IfCareTeamsV1MigrationNeeded' => '',
        'IfColumn' => '(?:\s+\S+){2}',
        'IfDocumentNamingNeeded' => '',
        'IfEyeFormLaserCategoriesNeeded' => '',
        'IfIndex' => '(?:\s+\S+){2}',
        'IfInnoDBMigrationNeeded' => '',
        'IfMBOEncounterNeeded' => '',
        'IfMissingColumn' => '(?:\s+\S+){2}',
        'IfNotColumnType' => '(?:\s+\S+){3}',
        'IfNotColumnTypeDefault' => '(?:\s+\S+){3}',
        'IfNotIndex' => '(?:\s+\S+){2}',
        'IfNotListImmunizationManufacturer' => '',
        'IfNotListOccupation' => '',
        'IfNotListReaction' => '',
        'IfNotMigrateClickOptions' => '',
        'IfNotRow' => '(?:\s+\S+){3}',
        'IfNotRow2D' => '(?:\s+\S+){5}',
        'IfNotRow2Dx2' => '(?:\s+\S+){7}',
        'IfNotRow3D' => '(?:\s+\S+){7}',
        'IfNotRow4D' => '(?:\s+\S+){9}',
        'IfNotTable' => '\s+\S+',
        'IfNotWenoRx' => '',
        'IfRow' => '(?:\s+\S+){3}',
        'IfRow2D' => '(?:\s+\S+){5}',
        'IfRow3D' => '(?:\s+\S+){7}',
        'IfRowIsNull' => '(?:\s+\S+){2}',
        'IfTable' => '\s+\S+',
        'IfTableEngine' => '\s+\S+\s+(?:MyISAM|InnoDB)',
        'IfTextNullFixNeeded' => '',
        'IfUpdateEditOptionsNeeded' => '(?:\s+\S+){4}',
        'IfVitalsDatesNeeded' => '',
    ];

    /**
     * Statements that are safe to repeat without a guard.
     */
    private const SAFE_UNGUARDED = [
        '/^CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s/i',
        '/^SET\s+@/i',
    ];

    /**
     * The files the sweep runs: the install script InstModuleTable::getInstallScript()
     * can pick, plus sql/upgrade.sql.
     */
    private const SWEPT_FILE = '#^interface/modules/custom_modules/[^/]+/(sql/)?(table|install)\.sql$|^interface/modules/custom_modules/[^/]+/sql/upgrade\.sql$#';

    public function testTrackedModuleSqlIsSafeToRerun(): void
    {
        $root = dirname(__DIR__, 4);
        $process = new Process(['git', 'ls-files', '-z', '--', 'interface/modules/custom_modules'], $root);
        $process->mustRun();
        $files = array_values(array_filter(
            explode(chr(0), $process->getOutput()),
            static fn(string $path): bool => preg_match(self::SWEPT_FILE, $path) === 1,
        ));
        self::assertNotSame([], $files, 'Expected tracked custom module SQL; is this a git checkout?');

        $filesystem = new Filesystem();
        $problems = [];
        foreach ($files as $file) {
            foreach (self::problems($filesystem->readFile($root . '/' . $file)) as $problem) {
                $problems[] = $file . ':' . $problem;
            }
        }

        self::assertSame([], $problems, 'Module SQL must be safe to run on every deploy. Guard each statement with an #If* directive.');
    }

    /**
     * @param list<string> $expected
     */
    #[DataProvider('scriptProvider')]
    public function testProblemsAreReported(string $sql, array $expected): void
    {
        self::assertSame($expected, self::problems($sql));
    }

    /**
     * @return array<string, array{string, list<string>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function scriptProvider(): array
    {
        return [
            'guarded statements pass' => [
                <<<'SQL'
                #IfNotTable foo
                CREATE TABLE foo (
                  id INT
                );
                #EndIf
                SQL,
                [],
            ],
            'safe statements pass unguarded' => [
                <<<'SQL'
                CREATE TABLE IF NOT EXISTS foo (id INT);
                SET @x = 1;
                SQL,
                [],
            ],
            'guarded special sql passes' => [
                <<<'SQL'
                #IfNotRow foo bar 1
                #SpecialSql
                INSERT INTO foo (bar) VALUES (1);
                #EndSpecialSql
                #EndIf
                SQL,
                [],
            ],
            'unguarded special sql is reported' => [
                <<<'SQL'
                #SpecialSql
                INSERT INTO foo (bar)
                VALUES (1);
                #EndSpecialSql
                SQL,
                ['2: runs on every deploy: INSERT INTO foo (bar) VALUES (1);'],
            ],
            'statement after EndIf is unguarded' => [
                <<<'SQL'
                #IfMissingColumn foo bar
                ALTER TABLE foo ADD bar INT;
                #EndIf
                INSERT INTO foo (bar) VALUES (1);
                SQL,
                ['4: runs on every deploy: INSERT INTO foo (bar) VALUES (1);'],
            ],
            'space after the hash makes a comment' => [
                <<<'SQL'
                # IfNotRow categories name FAX
                INSERT INTO categories (name) VALUES ('FAX');
                #EndIf
                SQL,
                [
                    '1: not a directive, so SQLUpgradeService ignores it: # IfNotRow categories name FAX',
                    <<<'MESSAGE'
                    2: runs on every deploy: INSERT INTO categories (name) VALUES ('FAX');
                    MESSAGE,
                ],
            ],
            'directive case matters' => [
                <<<'SQL'
                #IfNotTable foo
                CREATE TABLE foo (id INT);
                #Endif
                SQL,
                ['3: not a directive, so SQLUpgradeService ignores it: #Endif'],
            ],
            'directive missing an argument is a comment' => [
                <<<'SQL'
                #IfNotRow categories name
                INSERT INTO categories (name) VALUES ('FAX');
                #EndIf
                SQL,
                [
                    '1: not a directive, so SQLUpgradeService ignores it: #IfNotRow categories name',
                    <<<'MESSAGE'
                    2: runs on every deploy: INSERT INTO categories (name) VALUES ('FAX');
                    MESSAGE,
                ],
            ],
            'unknown directive is a comment' => [
                <<<'SQL'
                #IfNotView foo
                CREATE VIEW foo AS SELECT 1;
                #EndIf
                SQL,
                [
                    '1: not a directive, so SQLUpgradeService ignores it: #IfNotView foo',
                    '2: runs on every deploy: CREATE VIEW foo AS SELECT 1;',
                ],
            ],
            'row guards that toggle' => [
                <<<'SQL'
                #IfRow background_services name Sync
                DELETE FROM background_services WHERE name = 'Sync';
                #EndIf
                #IfNotRow background_services name Sync
                INSERT INTO background_services (name) VALUES ('Sync');
                #EndIf
                SQL,
                ['1: #IfRow and #IfNotRow on the same row toggle on every deploy: background_services name Sync'],
            ],
            '2D row guards that toggle' => [
                <<<'SQL'
                #IfRow2D background_services name Sync active 1
                DELETE FROM background_services WHERE name = 'Sync' AND active = 1;
                #EndIf
                #IfNotRow2D background_services name Sync active 1
                INSERT INTO background_services (name, active) VALUES ('Sync', 1);
                #EndIf
                SQL,
                ['1: #IfRow2D and #IfNotRow2D on the same row toggle on every deploy: background_services name Sync active 1'],
            ],
            'row guards of different dimensions do not pair' => [
                <<<'SQL'
                #IfRow2D background_services name Sync active 0
                DELETE FROM background_services WHERE name = 'Sync' AND active = 0;
                #EndIf
                #IfNotRow background_services name Sync
                INSERT INTO background_services (name, active) VALUES ('Sync', 1);
                #EndIf
                SQL,
                [],
            ],
            'drop table is not safe' => [
                'DROP TABLE foo;',
                ['1: runs on every deploy: DROP TABLE foo;'],
            ],
            'drop table if exists is not safe' => [
                'DROP TABLE IF EXISTS foo;',
                ['1: runs on every deploy: DROP TABLE IF EXISTS foo;'],
            ],
        ];
    }

    /**
     * Walk a script the way SQLUpgradeService does and report each problem as
     * "<line>: <reason>".
     *
     * @return list<string>
     */
    private static function problems(string $sql): array
    {
        $directives = [];
        foreach (self::GUARD_DIRECTIVES as $name => $arguments) {
            $directives[] = $name . $arguments;
        }
        $directive = '/^#(?:' . implode('|', $directives) . ')/';
        $problems = [];
        $guarded = false;
        $special = false;
        $statement = '';
        $statementLine = 0;
        $rowGuards = [];
        foreach (explode("\n", $sql) as $index => $rawLine) {
            $lineNumber = $index + 1;
            $line = rtrim($rawLine);
            if ($line === '' || preg_match('/^\s*--/', $line) === 1) {
                continue;
            }
            if (preg_match($directive, $line) === 1) {
                $guarded = true;
                if (preg_match('/^#If(Not)?Row([234]D)?\s+(.+)$/', $line, $rowGuard) === 1) {
                    $rowGuards[$rowGuard[2]][$rowGuard[1] === 'Not' ? 'absent' : 'present'][$rowGuard[3]] = $lineNumber;
                }
                continue;
            }
            if (str_starts_with($line, '#EndIf')) {
                $guarded = false;
                continue;
            }
            if (str_starts_with($line, '#SpecialSql')) {
                $special = true;
                continue;
            }
            if (str_starts_with($line, '#EndSpecialSql')) {
                // SQLUpgradeService runs the accumulated block once it ends in a semicolon.
                $special = false;
                $line = '';
            } elseif (preg_match('/^\s*#/', $line) === 1) {
                if (preg_match('/^\s*#\s*(if|endif|specialsql|endspecialsql)/i', $line) === 1) {
                    $problems[] = $lineNumber . ': not a directive, so SQLUpgradeService ignores it: ' . trim($line);
                }
                continue;
            }
            if ($statement === '') {
                $statementLine = $lineNumber;
            }
            $statement = trim($statement . ' ' . trim($line));
            if ($special || !str_ends_with($statement, ';')) {
                continue;
            }
            if (!$guarded && !self::isSafeUnguarded($statement)) {
                $problems[] = $statementLine . ': runs on every deploy: ' . $statement;
            }
            $statement = '';
        }
        // A block that runs when a row exists and another that runs when it
        // does not cannot both settle: whichever ran last re-arms the other.
        foreach ($rowGuards as $dimension => $guards) {
            foreach (array_intersect_key($guards['present'] ?? [], $guards['absent'] ?? []) as $row => $lineNumber) {
                $problems[] = $lineNumber . ': #IfRow' . $dimension . ' and #IfNotRow' . $dimension . ' on the same row toggle on every deploy: ' . $row;
            }
        }
        return $problems;
    }

    private static function isSafeUnguarded(string $statement): bool
    {
        foreach (self::SAFE_UNGUARDED as $pattern) {
            if (preg_match($pattern, $statement) === 1) {
                return true;
            }
        }
        return false;
    }
}
