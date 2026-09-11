<?php

/**
 * Statement rules and run log.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\LbfStatements;

class StatementRepository
{
    /**
     * @param Queries $sql Database access, or a test fake.
     */
    public function __construct(
        private readonly Queries $sql = new Queries()
    ) {
    }

    /**
     * Layout form_id values that currently have at least one enabled rule.
     *
     * @return list<string>
     */
    public function formIdsWithRules(): array
    {
        $rows = $this->sql->fetchRecords(
            "SELECT DISTINCT form_id FROM module_lbf_statement_rules WHERE enabled = 1 ORDER BY form_id"
        );
        $out = [];
        foreach ($rows as $raw) {
            $row = Values::assocRow($raw);
            if ($row === null) {
                continue;
            }
            $formId = Values::rowString($row, 'form_id');
            if ($formId !== '') {
                $out[] = $formId;
            }
        }
        return $out;
    }

    /**
     * Rules for one layout, optionally only the enabled ones.
     *
     * @return list<array<string, mixed>>
     */
    public function rulesForForm(string $formId, bool $enabledOnly = true): array
    {
        Identifiers::assertFieldId($formId);
        $sql = "SELECT id, form_id, source_field_id, source_field_id_2, op, min_value, max_value, " .
            "min_inclusive, max_inclusive, match_token, statement_text, seq, enabled " .
            "FROM module_lbf_statement_rules WHERE form_id = ?";
        $bind = [$formId];
        if ($enabledOnly) {
            $sql .= " AND enabled = 1";
        }
        $sql .= " ORDER BY seq, id";
        $rows = $this->sql->fetchRecords($sql, $bind);
        $out = [];
        foreach ($rows as $raw) {
            $row = Values::assocRow($raw);
            if ($row !== null) {
                $out[] = $row;
            }
        }
        return $out;
    }

    /**
     * One rule by id, or null when it is gone.
     *
     * @return array<string, mixed>|null
     */
    public function getRule(int $id): ?array
    {
        return Values::assocRow($this->sql->querySingleRow(
            "SELECT id, form_id, source_field_id, source_field_id_2, op, min_value, max_value, " .
            "min_inclusive, max_inclusive, match_token, statement_text, seq, enabled " .
            "FROM module_lbf_statement_rules WHERE id = ?",
            [$id]
        ));
    }

    /**
     * Insert or update a rule after locking the form and source-field pair.
     *
     * @param array<string, mixed> $data
     */
    public function saveRule(array $data, ?int $id = null): int
    {
        Identifiers::assertFieldId(Values::asString($data['form_id'] ?? ''));
        Identifiers::assertFieldId(Values::asString($data['source_field_id'] ?? ''));
        $source2 = Values::asString($data['source_field_id_2'] ?? '');
        if ($source2 !== '') {
            Identifiers::assertFieldId($source2);
        }
        $op = Values::asString($data['op'] ?? '');
        if (!in_array($op, ['band', 'ratio_lt', 'ratio_gt', 'parse_severity'], true)) {
            throw new \InvalidArgumentException('Invalid op');
        }
        $lockName = null;
        $lockAcquired = false;
        try {
            return $this->sql->inTransaction(function () use ($data, $id, &$lockName, &$lockAcquired): int {
                if ($id !== null && $id > 0) {
                    $this->requireRuleRow($id);
                }
                $lockName = static::bandLockName(
                    Values::asString($data['form_id'] ?? ''),
                    Values::asString($data['source_field_id'] ?? ''),
                    Values::asString($data['source_field_id_2'] ?? '')
                );
                if (!$this->sql->acquireLock($lockName, 10)) {
                    throw new BandLockException('Could not lock this field to save the rule.');
                }
                $lockAcquired = true;
                $this->assertBandDoesNotOverlap($data, $id);
                return $this->writeRule($data, $id);
            });
        } finally {
            if ($lockAcquired && $lockName !== null) {
                $this->sql->releaseLock($lockName);
            }
        }
    }

    /**
     * INSERT or UPDATE the rule row. Caller must hold the band lock.
     *
     * @param array<string, mixed> $data
     */
    private function writeRule(array $data, ?int $id): int
    {
        $source2 = Values::asString($data['source_field_id_2'] ?? '');
        $op = Values::asString($data['op'] ?? '');
        $min = Values::asFloatOrNull($data['min_value'] ?? null);
        $max = Values::asFloatOrNull($data['max_value'] ?? null);
        $matchToken = Values::asString($data['match_token'] ?? '');
        $fields = [
            Values::asString($data['form_id'] ?? ''),
            Values::asString($data['source_field_id'] ?? ''),
            $source2 !== '' ? $source2 : null,
            $op,
            $min,
            $max,
            Values::asBool($data['min_inclusive'] ?? 0) ? 1 : 0,
            Values::asBool($data['max_inclusive'] ?? 0) ? 1 : 0,
            $matchToken !== '' ? $matchToken : null,
            Values::asString($data['statement_text'] ?? '') !== ''
                ? Values::asString($data['statement_text'] ?? '')
                : null,
            Values::asInt($data['seq'] ?? 0),
            Values::asBool($data['enabled'] ?? 0) ? 1 : 0,
        ];
        if ($id !== null && $id > 0) {
            $this->sql->sqlStatementThrowException(
                "UPDATE module_lbf_statement_rules SET form_id=?, source_field_id=?, source_field_id_2=?, " .
                "op=?, min_value=?, max_value=?, min_inclusive=?, max_inclusive=?, match_token=?, " .
                "statement_text=?, seq=?, enabled=? WHERE id=?",
                array_merge($fields, [$id])
            );
            $this->assertRuleExists($id);
            return $id;
        }
        return $this->sql->sqlInsert(
            "INSERT INTO module_lbf_statement_rules (" .
            "form_id, source_field_id, source_field_id_2, op, min_value, max_value, min_inclusive, " .
            "max_inclusive, match_token, statement_text, seq, enabled) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)",
            $fields
        );
    }

    /**
     * Enable or disable one rule. A missing row is an error, not a silent success.
     */
    public function setEnabled(int $id, bool $enabled): void
    {
        $lockName = null;
        $lockAcquired = false;
        try {
            $this->sql->inTransaction(function () use ($id, $enabled, &$lockName, &$lockAcquired): void {
                $rule = $this->requireRuleRow($id);
                if (!$enabled) {
                    $this->writeEnabled($id, false);
                    return;
                }
                $rule['enabled'] = 1;
                $lockName = static::bandLockName(
                    Values::asString($rule['form_id'] ?? ''),
                    Values::asString($rule['source_field_id'] ?? ''),
                    Values::asString($rule['source_field_id_2'] ?? '')
                );
                if (!$this->sql->acquireLock($lockName, 10)) {
                    throw new BandLockException('Could not lock this field to save the rule.');
                }
                $lockAcquired = true;
                $this->assertBandDoesNotOverlap($rule, $id);
                $this->writeEnabled($id, true);
            });
        } finally {
            if ($lockAcquired && $lockName !== null) {
                $this->sql->releaseLock($lockName);
            }
        }
    }

    /**
     * Persist the enabled flag for one rule id.
     */
    private function writeEnabled(int $id, bool $enabled): void
    {
        $this->sql->sqlStatementThrowException(
            "UPDATE module_lbf_statement_rules SET enabled = ? WHERE id = ?",
            [$enabled ? 1 : 0, $id]
        );
        $this->assertRuleExists($id);
    }

    /**
     * Lock one rule row for the rest of the transaction, or throw if it is gone.
     *
     * @return array<string, mixed>
     */
    private function requireRuleRow(int $id): array
    {
        $rule = $this->lockRuleRow($id);
        if ($rule === null || Values::rowInt($rule, 'id') !== $id) {
            throw new RuleNotFoundException('Rule not found.');
        }
        return $rule;
    }

    /**
     * SELECT ... FOR UPDATE one rule row.
     *
     * @return array<string, mixed>|null
     */
    private function lockRuleRow(int $id): ?array
    {
        return Values::assocRow($this->sql->querySingleRow(
            "SELECT id, form_id, source_field_id, source_field_id_2, op, min_value, max_value, " .
            "min_inclusive, max_inclusive, match_token, statement_text, seq, enabled " .
            "FROM module_lbf_statement_rules WHERE id = ? FOR UPDATE",
            [$id]
        ));
    }

    /**
     * Advisory lock name for one form and source-field pair.
     */
    public static function bandLockName(string $formId, string $source, string $source2): string
    {
        return 'lbfstmt_' . md5($formId . "\0" . $source . "\0" . $source2);
    }

    /**
     * Throw when this enabled band overlaps another on the same source fields.
     *
     * @param array<string, mixed> $data
     */
    public function assertBandDoesNotOverlap(array $data, ?int $id = null): void
    {
        if (Values::asString($data['op'] ?? '') !== 'band') {
            return;
        }
        if (BandOverlap::invertedBounds($data)) {
            throw new InvertedBoundsException('Minimum must be less than or equal to maximum.');
        }
        if (!Values::asBool($data['enabled'] ?? 0)) {
            return;
        }
        $formId = Values::asString($data['form_id'] ?? '');
        $source = Values::asString($data['source_field_id'] ?? '');
        $source2 = Values::asString($data['source_field_id_2'] ?? '');
        foreach ($this->lockedEnabledBands($formId, $source, $source2) as $other) {
            if ($id !== null && Values::rowInt($other, 'id') === $id) {
                continue;
            }
            if (BandOverlap::rangesOverlap($data, $other)) {
                throw new BandOverlapException(
                    'This numeric range overlaps another band on the same field.'
                );
            }
        }
    }

    /**
     * Enabled bands for one source-field pair, locked for the rest of the
     * transaction so a concurrent insert is visible after GET_LOCK.
     *
     * @return list<array<string, mixed>>
     */
    private function lockedEnabledBands(string $formId, string $source, string $source2): array
    {
        $rows = $this->sql->fetchRecords(
            "SELECT id, form_id, source_field_id, source_field_id_2, op, min_value, max_value, " .
            "min_inclusive, max_inclusive, match_token, statement_text, seq, enabled " .
            "FROM module_lbf_statement_rules WHERE form_id = ? AND source_field_id = ? " .
            "AND IFNULL(source_field_id_2, '') = ? AND enabled = 1 AND op = 'band' FOR UPDATE",
            [$formId, $source, $source2]
        );
        $out = [];
        foreach ($rows as $raw) {
            $row = Values::assocRow($raw);
            if ($row !== null) {
                $out[] = $row;
            }
        }
        return $out;
    }

    /**
     * Throw when an UPDATE matched no remaining row.
     */
    private function assertRuleExists(int $id): void
    {
        $row = Values::assocRow($this->sql->querySingleRow(
            "SELECT id FROM module_lbf_statement_rules WHERE id = ?",
            [$id]
        ));
        if ($row === null || Values::rowInt($row, 'id') !== $id) {
            throw new RuleNotFoundException('Rule not found.');
        }
    }

    /**
     * Record that a paragraph was written for this form instance.
     */
    public function logRun(string $formId, int $pid, int $instanceFormId, string $user, string $mode): void
    {
        Identifiers::assertFieldId($formId);
        if (!in_array($mode, ['append', 'overwrite'], true)) {
            throw new \InvalidArgumentException('Invalid mode');
        }
        $this->sql->sqlInsert(
            "INSERT INTO module_lbf_statement_runs (form_id, pid, instance_form_id, user, mode) " .
            "VALUES (?,?,?,?,?)",
            [$formId, $pid, $instanceFormId, $user, $mode]
        );
    }
}
