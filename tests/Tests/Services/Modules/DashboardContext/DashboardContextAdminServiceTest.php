<?php

/**
 * Database tests for creating and assigning custom dashboard contexts.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2026 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Modules\DashboardContext;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\ModulesClassLoader;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\DashboardContext\Services\DashboardContextAdminService;
use OpenEMR\Services\Utils\SQLUpgradeService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DashboardContextAdminServiceTest extends TestCase
{
    private const MODULE_DIR = 'interface/modules/custom_modules/oe-module-dashboard-context';

    /**
     * Stands in for both the admin and the assigned user; the service stores
     * user ids without joining the users table, so no user row is needed.
     */
    private const USER_ID = 911740;

    /**
     * Tables created by the module's install.sql.
     */
    private const MODULE_TABLES = [
        'user_dashboard_context',
        'user_dashboard_context_config',
        'dashboard_context_definitions',
        'dashboard_context_assignments',
        'dashboard_context_role_defaults',
        'dashboard_context_audit_log',
    ];

    /**
     * @var list<string>
     */
    private static array $tablesCreatedByTest = [];

    private DashboardContextAdminService $service;

    public static function setUpBeforeClass(): void
    {
        $projectDir = OEGlobalsBag::getInstance()->getProjectDir();
        (new ModulesClassLoader($projectDir))->registerNamespaceIfNotExists(
            'OpenEMR\\Modules\\DashboardContext\\',
            $projectDir . '/' . self::MODULE_DIR . '/src'
        );

        // The module is not installed in a fresh test database: install its
        // tables for this class and drop afterwards only the ones it created.
        $missingTables = array_values(array_filter(
            self::MODULE_TABLES,
            static fn(string $table): bool => !QueryUtils::existsTable($table)
        ));
        if ($missingTables !== []) {
            self::runModuleSql('install.sql');
            self::$tablesCreatedByTest = $missingTables;
        }
    }

    public static function tearDownAfterClass(): void
    {
        foreach (self::$tablesCreatedByTest as $table) {
            QueryUtils::sqlStatementThrowException("DROP TABLE IF EXISTS `{$table}`");
        }
        self::$tablesCreatedByTest = [];
        QueryUtils::clearSchemaCache();
    }

    protected function setUp(): void
    {
        $this->deleteTestRows();
        $this->service = new DashboardContextAdminService();
    }

    protected function tearDown(): void
    {
        $this->deleteTestRows();
    }

    #[DataProvider('blankContextKeyProvider')]
    public function testBlankContextKeyIsGeneratedFromTheName(?string $contextKey): void
    {
        $contextId = $this->service->createContext(
            ['context_name' => 'Issue 11740 Wound Care', 'context_key' => $contextKey],
            self::USER_ID
        );

        $this->assertIsInt($contextId);
        $this->assertSame('custom_issue_11740_wound_care', $this->contextKeyOf($contextId));
    }

    /**
     * @return array<string, array{?string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function blankContextKeyProvider(): array
    {
        return [
            'key left empty in the form' => [''],
            'key of spaces only' => ['   '],
            'key not sent' => [null],
        ];
    }

    public function testTwoContextsWithBlankKeysAreBothCreated(): void
    {
        $first = $this->service->createContext(
            ['context_name' => 'Issue 11740 Wound Care', 'context_key' => ''],
            self::USER_ID
        );
        $second = $this->service->createContext(
            ['context_name' => 'Issue 11740 Oncology', 'context_key' => ''],
            self::USER_ID
        );

        $this->assertIsInt($first);
        $this->assertIsInt($second);
        $this->assertSame('custom_issue_11740_oncology', $this->contextKeyOf($second));
    }

    public function testTypedContextKeyIsKeptWithoutSurroundingSpaces(): void
    {
        $contextId = $this->service->createContext(
            ['context_name' => 'Issue 11740 Typed', 'context_key' => ' issue_11740_typed '],
            self::USER_ID
        );

        $this->assertIsInt($contextId);
        $this->assertSame('issue_11740_typed', $this->contextKeyOf($contextId));
    }

    public function testAssigningACustomContextRecordsItsId(): void
    {
        $contextId = $this->service->createContext(
            ['context_name' => 'Issue 11740 Typed', 'context_key' => 'issue_11740_typed'],
            self::USER_ID
        );
        $this->assertIsInt($contextId);

        $this->assertTrue($this->service->assignContextToUser(self::USER_ID, 'issue_11740_typed', self::USER_ID));

        $this->assertSame([['context_id' => $contextId, 'context_key' => 'issue_11740_typed']], $this->activeAssignments());
    }

    public function testAssigningASystemContextRecordsNoId(): void
    {
        $this->assertTrue($this->service->assignContextToUser(self::USER_ID, 'primary_care', self::USER_ID));

        $this->assertSame([['context_id' => null, 'context_key' => 'primary_care']], $this->activeAssignments());
    }

    #[DataProvider('systemContextKeyProvider')]
    public function testSystemContextKeyCannotBeTakenByACustomContext(string $contextKey): void
    {
        $this->assertFalse($this->service->createContext(
            ['context_name' => 'Issue 11740 Primary Care', 'context_key' => $contextKey],
            self::USER_ID
        ));

        $this->assertTrue($this->service->assignContextToUser(self::USER_ID, 'primary_care', self::USER_ID));

        $this->assertSame([['context_id' => null, 'context_key' => 'primary_care']], $this->activeAssignments());
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function systemContextKeyProvider(): array
    {
        return [
            'same key' => ['primary_care'],
            // context_key compares case-insensitively, so this row would answer lookups for primary_care.
            'upper case' => ['PRIMARY_CARE'],
        ];
    }

    public function testSystemContextAssignmentIgnoresAnOlderDefinitionWithTheSameKey(): void
    {
        // Before createContext() refused system keys, an admin could save a definition keyed primary_care.
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO dashboard_context_definitions (user_id, context_key, context_name) VALUES (?, 'primary_care', 'Issue 11740 Legacy')",
            [self::USER_ID]
        );
        $legacyId = QueryUtils::getLastInsertId();

        $this->assertTrue($this->service->assignContextToUser(self::USER_ID, 'primary_care', self::USER_ID));
        $this->assertTrue($this->service->deleteContext($legacyId));

        $this->assertSame([['context_id' => null, 'context_key' => 'primary_care']], $this->activeAssignments());
    }

    public function testDeletingAnAssignedCustomContextRemovesTheAssignment(): void
    {
        $contextId = $this->service->createContext(
            ['context_name' => 'Issue 11740 Typed', 'context_key' => 'issue_11740_typed'],
            self::USER_ID
        );
        $this->assertIsInt($contextId);
        $this->assertTrue($this->service->assignContextToUser(self::USER_ID, 'issue_11740_typed', self::USER_ID));

        $this->assertTrue($this->service->deleteContext($contextId));

        $this->assertSame([], $this->activeAssignments());
    }

    private function contextKeyOf(int $contextId): string
    {
        $key = QueryUtils::fetchSingleValue(
            'SELECT context_key FROM dashboard_context_definitions WHERE id = ?',
            'context_key',
            [$contextId]
        );
        $this->assertIsString($key);
        return $key;
    }

    /**
     * @return list<array{context_id: ?int, context_key: string}>
     */
    private function activeAssignments(): array
    {
        $rows = QueryUtils::fetchRecords(
            'SELECT context_id, context_key FROM dashboard_context_assignments WHERE user_id = ? AND is_active = 1 ORDER BY id',
            [self::USER_ID]
        );
        $assignments = [];
        foreach ($rows as $row) {
            $this->assertIsString($row['context_key']);
            $assignments[] = [
                'context_id' => is_numeric($row['context_id']) ? (int) $row['context_id'] : null,
                'context_key' => $row['context_key'],
            ];
        }
        return $assignments;
    }

    private function deleteTestRows(): void
    {
        foreach (
            [
                'dashboard_context_definitions',
                'dashboard_context_assignments',
                'user_dashboard_context',
                'dashboard_context_audit_log',
            ] as $table
        ) {
            QueryUtils::sqlStatementThrowException("DELETE FROM {$table} WHERE user_id = ?", [self::USER_ID]);
        }
    }

    private static function runModuleSql(string $file): void
    {
        $sqlUpgradeService = new SQLUpgradeService();
        $sqlUpgradeService->setThrowExceptionOnError(true);
        $sqlUpgradeService->setRenderOutputToScreen(false);
        $sqlUpgradeService->upgradeFromSqlFile($file, OEGlobalsBag::getInstance()->getProjectDir() . '/' . self::MODULE_DIR . '/sql');
        QueryUtils::clearSchemaCache();
    }
}
