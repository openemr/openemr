<?php

/**
 * Tests for the audit-table write guard.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Logging\Audit;

use OpenEMR\Common\Logging\Audit\AuditLogGrantPlan;
use OpenEMR\Common\Logging\Audit\AuditTableWriteGuard;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AuditTableWriteGuardTest extends TestCase
{
    /**
     * @return array<string, array{string, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function statementProvider(): array
    {
        return [
            'delete log' => ['DELETE FROM log WHERE id = 1', true],
            'delete quoted log' => ['DELETE FROM `log` WHERE id = 1', true],
            'update comment' => ['UPDATE log_comment_encrypt SET checksum = 1', true],
            'truncate anchor' => ['TRUNCATE TABLE log_chain_anchor', true],
            'drop log' => ['DROP TABLE log', true],
            'drop if exists' => ['DROP TABLE IF EXISTS log', true],
            'drop listed with another table' => ['DROP TABLE other_table, log', true],
            'alter anchor' => ['ALTER TABLE log_chain_anchor ADD COLUMN extra int', true],
            'rename log away' => ['RENAME TABLE log TO log_backup', true],
            'rename onto log' => ['RENAME TABLE other_table TO log', true],
            'select log' => ['SELECT id FROM log WHERE id = 1', false],
            'insert log' => ['INSERT INTO log (event) VALUES (1)', false],
            'delete other' => ['DELETE FROM login_mfa_registrations WHERE user_id = 1', false],
            'update patient' => ['UPDATE patient_data SET fname = 1 WHERE pid = 1', false],
            'literal mentions log' => ["UPDATE list_options SET notes = 'see log' WHERE list_id = 'x'", false],
            'comment then delete' => ['/* note */ DELETE FROM log WHERE id = 1', true],
            'update ignore log' => ['UPDATE IGNORE log SET comments = 1 WHERE id = 1', true],
            'update low priority log' => ['UPDATE LOW_PRIORITY IGNORE log_comment_encrypt SET checksum = 1', true],
            'insert duplicate updates log' => ["INSERT INTO log (id, comments) VALUES (1, 'x') ON DUPLICATE KEY UPDATE comments = 'x'", true],
            'insert duplicate updates another table' => ["INSERT INTO patient_data (pid) VALUES (1) ON DUPLICATE KEY UPDATE fname = 'a'", false],
            'insert ignore still appends' => ['INSERT IGNORE INTO log (event) VALUES (1)', false],
            'update ignore another table' => ['UPDATE IGNORE patient_data SET fname = 1 WHERE pid = 1', false],
        ];
    }

    #[DataProvider('statementProvider')]
    public function testRejectsChangesToAuditTables(string $sql, bool $rejected): void
    {
        self::assertSame($rejected, AuditTableWriteGuard::rejects($sql));
    }

    public function testUpgradeMayAlterAnAuditTable(): void
    {
        $alter = "ALTER TABLE `log_comment_encrypt` ADD COLUMN `chain` char(128) DEFAULT NULL";

        self::assertTrue(AuditTableWriteGuard::rejects($alter));
        self::assertTrue(AuditTableWriteGuard::isUpgradeSchemaChange($alter));
        self::assertFalse(AuditTableWriteGuard::isUpgradeSchemaChange('UPDATE log SET comments = 1'));
        self::assertFalse(AuditTableWriteGuard::isUpgradeSchemaChange('DELETE FROM log WHERE id = 1'));
        self::assertFalse(AuditTableWriteGuard::isUpgradeSchemaChange('ALTER TABLE patient_data ADD COLUMN extra int'));
    }

    public function testParsesTheCurrentDatabaseAccount(): void
    {
        self::assertSame(
            ['user' => 'openemr', 'host' => 'localhost'],
            AuditLogGrantPlan::parseAccount("'openemr'@'localhost'"),
        );
    }

    public function testTriggerStatementsCoverTheThreeTables(): void
    {
        $plan = new AuditLogGrantPlan('openemr', 'openemr', 'localhost', 'openemr_audit', 'localhost');
        $sql = implode("\n", $plan->triggerStatements());

        self::assertStringContainsString('BEFORE UPDATE ON `log`', $sql);
        self::assertStringContainsString('BEFORE DELETE ON `log_comment_encrypt`', $sql);
        self::assertStringContainsString('BEFORE DELETE ON `log_chain_anchor`', $sql);
        self::assertStringNotContainsString('BEFORE UPDATE ON `log_chain_anchor`', $sql);
        self::assertStringNotContainsString('DROP TRIGGER', $sql);
    }

    public function testAccountStatementsKeepTheAnchorUpdate(): void
    {
        $plan = new AuditLogGrantPlan('openemr', 'openemr', 'localhost', 'openemr_audit', 'localhost');
        $sql = implode("\n", $plan->accountStatements('abc123'));

        self::assertStringContainsString("IDENTIFIED BY 'abc123'", $sql);
        self::assertStringContainsString('GRANT SELECT, INSERT, UPDATE ON `openemr`.`log_chain_anchor`', $sql);
        self::assertStringContainsString('GRANT SELECT, INSERT ON `openemr`.`log`', $sql);
        self::assertStringContainsString('GRANT INSERT ON `openemr`.`api_log`', $sql);
        self::assertStringContainsString('GRANT SELECT ON `openemr`.`gacl_aro`', $sql);
        self::assertStringContainsString('GRANT SELECT ON `openemr`.`gacl_groups_aro_map`', $sql);
        self::assertStringContainsString('GRANT SELECT ON `openemr`.`gacl_aro_groups`', $sql);
    }

    /**
     * A dotted, colon, or percent host is still an account name.
     */
    public function testAddressHostsStayValid(): void
    {
        $plan = new AuditLogGrantPlan('openemr', 'openemr', '127.0.0.1', 'oe_audit_abcdef123456', '::1');

        self::assertSame('127.0.0.1', $plan->applicationHost);
        self::assertSame('::1', $plan->auditHost);
    }
}
