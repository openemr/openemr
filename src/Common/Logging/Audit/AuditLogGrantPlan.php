<?php

/**
 * Statements that keep audit rows from being updated or deleted.
 *
 * A database-wide grant cannot be narrowed by revoking one table, so the
 * enforcement that works for the application account is a trigger. Creating
 * a separate audit account is included when the installer is allowed to
 * create users. Names that are not simple identifiers are refused.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Logging\Audit;

final readonly class AuditLogGrantPlan
{
    public function __construct(
        public string $database,
        public string $applicationUser,
        public string $applicationHost,
        public string $auditUser,
        public string $auditHost,
    ) {
        foreach ([$database, $applicationUser, $applicationHost, $auditUser, $auditHost] as $name) {
            if (preg_match('/^[A-Za-z0-9_%]+$/', $name) !== 1) {
                throw new \InvalidArgumentException('Audit grant names must be simple identifiers.');
            }
        }
    }

    /**
     * @return array{user: string, host: string}
     */
    public static function parseAccount(string $currentUser): array
    {
        $account = trim($currentUser, " \t`'\"");
        $at = strrpos($account, '@');
        if ($at === false || $at === 0) {
            throw new \InvalidArgumentException('The database account is not user@host.');
        }

        return [
            'user' => trim(substr($account, 0, $at), " `'"),
            'host' => trim(substr($account, $at + 1), " `'"),
        ];
    }

    /**
     * @return list<string>
     */
    public function triggerStatements(): array
    {
        $blocks = [
            ['audit_log_block_update', 'log', 'UPDATE'],
            ['audit_log_block_delete', 'log', 'DELETE'],
            ['audit_log_comment_block_update', 'log_comment_encrypt', 'UPDATE'],
            ['audit_log_comment_block_delete', 'log_comment_encrypt', 'DELETE'],
            ['audit_log_anchor_block_delete', 'log_chain_anchor', 'DELETE'],
        ];
        $statements = [];
        foreach ($blocks as [$name, $table, $event]) {
            $statements[] = 'DROP TRIGGER IF EXISTS `' . $name . '`';
            $statements[] = 'CREATE TRIGGER `' . $name . '` BEFORE ' . $event
                . ' ON `' . $table . '` FOR EACH ROW'
                . " SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'The audit log cannot be changed.'";
        }

        return $statements;
    }

    /**
     * @return list<string>
     */
    public function accountStatements(string $password): array
    {
        if (preg_match('/^[0-9a-f]+$/', $password) !== 1) {
            throw new \InvalidArgumentException('The audit password must be hexadecimal.');
        }
        $audit = $this->account($this->auditUser, $this->auditHost);
        $database = '`' . $this->database . '`';

        return [
            'CREATE USER IF NOT EXISTS ' . $audit . " IDENTIFIED BY '" . $password . "'",
            'ALTER USER ' . $audit . " IDENTIFIED BY '" . $password . "'",
            'GRANT SELECT, INSERT ON ' . $database . '.`log` TO ' . $audit,
            'GRANT SELECT, INSERT ON ' . $database . '.`log_comment_encrypt` TO ' . $audit,
            'GRANT SELECT, INSERT, UPDATE ON ' . $database . '.`log_chain_anchor` TO ' . $audit,
        ];
    }

    private function account(string $user, string $host): string
    {
        return "'" . $user . "'@'" . $host . "'";
    }
}
