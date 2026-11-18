<?php

/**
 * Statement rules and run log.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\LBF;

use OpenEMR\BC\ServiceContainer;

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
        $formId = Values::asString($data['form_id'] ?? '');
        $catalog = new LayoutCatalog($this->sql);
        $catalog->assertActiveLbfForm($formId);
        $source = Values::asString($data['source_field_id'] ?? '');
        Identifiers::assertFieldId($source);
        $source2 = Values::asString($data['source_field_id_2'] ?? '');
        if ($source2 !== '') {
            Identifiers::assertFieldId($source2);
        }
        $this->assertSourcesOnLayout($catalog, $formId, $source, $source2);
        $op = Values::asString($data['op'] ?? '');
        if (!in_array($op, ['band', 'ratio_lt', 'ratio_gt', 'parse_severity'], true)) {
            throw new \InvalidArgumentException('Invalid op');
        }
        if (in_array($op, ['ratio_lt', 'ratio_gt'], true) && $source2 === '') {
            throw new \InvalidArgumentException('A ratio rule needs a second field.');
        }
        $data['min_value'] = self::storedBound($data['min_value'] ?? null);
        $data['max_value'] = self::storedBound($data['max_value'] ?? null);
        // The advisory lock has to come before any rule-row lock. Two saves
        // that lock different rows first, then wait on each other, fail a
        // valid save.
        $lockName = static::bandLockName($formId, $source);
        $lockAcquired = false;
        try {
            return $this->sql->inTransaction(function () use ($data, $id, $lockName, &$lockAcquired): int {
                if (!$this->sql->acquireLock($lockName, 10)) {
                    throw new BandLockException('Could not lock this field to save the rule.');
                }
                $lockAcquired = true;
                if ($id !== null && $id > 0) {
                    $this->requireRuleRow($id);
                }
                $this->assertBandDoesNotOverlap($data, $id);
                return $this->writeRule($data, $id);
            });
        } finally {
            $this->releaseBandLock($lockName, $lockAcquired);
        }
    }

    /**
     * Empty is an open end. Anything else must be a number.
     */
    private static function assertOpenOrNumeric(mixed $value): void
    {
        if ($value === null || is_int($value)) {
            return;
        }
        if (is_float($value)) {
            if (!is_finite($value)) {
                throw new \InvalidArgumentException('A bound must be a number.');
            }

            return;
        }
        if (!is_string($value) || (trim($value) !== '' && !is_numeric(trim($value)))) {
            throw new \InvalidArgumentException('A bound must be a number.');
        }
    }

    /**
     * Empty is an open end. A number is stored at DECIMAL(12,4).
     */
    private static function storedBound(mixed $value): ?float
    {
        self::assertOpenOrNumeric($value);
        $number = Values::asFloatOrNull($value);
        if ($number === null) {
            return null;
        }

        return round($number, 4);
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
        if (!$enabled) {
            $this->sql->inTransaction(function () use ($id): void {
                $this->requireRuleRow($id);
                $this->writeEnabled($id, false);
            });
            return;
        }
        $preview = $this->getRule($id);
        if ($preview === null || Values::rowInt($preview, 'id') !== $id) {
            throw new RuleNotFoundException('Rule not found.');
        }
        $lockName = static::bandLockName(
            Values::asString($preview['form_id'] ?? ''),
            Values::asString($preview['source_field_id'] ?? '')
        );
        $lockAcquired = false;
        try {
            $this->sql->inTransaction(function () use ($id, $lockName, &$lockAcquired): void {
                if (!$this->sql->acquireLock($lockName, 10)) {
                    throw new BandLockException('Could not lock this field to save the rule.');
                }
                $lockAcquired = true;
                $rule = $this->requireRuleRow($id);
                $lockedName = static::bandLockName(
                    Values::asString($rule['form_id'] ?? ''),
                    Values::asString($rule['source_field_id'] ?? '')
                );
                if ($lockedName !== $lockName) {
                    throw new BandLockException('Could not lock this field to save the rule.');
                }
                $rule['enabled'] = 1;
                $this->assertBandDoesNotOverlap($rule, $id);
                $this->writeEnabled($id, true);
            });
        } finally {
            $this->releaseBandLock($lockName, $lockAcquired);
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
     * Source fields must exist on the selected layout, not only look like ids.
     */
    private function assertSourcesOnLayout(
        LayoutCatalog $catalog,
        string $formId,
        string $source,
        string $source2
    ): void {
        $fields = $catalog->fieldMeta($formId);
        if (!isset($fields[$source])) {
            throw new \InvalidArgumentException('Source field is not on this layout.');
        }
        if ($source2 !== '' && !isset($fields[$source2])) {
            throw new \InvalidArgumentException('Second source field is not on this layout.');
        }
    }

    /**
     * Drop the band lock after the transaction. A failed RELEASE_LOCK must not
     * hide a committed write or the original exception.
     */
    private function releaseBandLock(?string $lockName, bool $acquired): void
    {
        if (!$acquired || $lockName === null) {
            return;
        }
        try {
            $this->sql->releaseLock($lockName);
        } catch (\Throwable) { // @phpstan-ignore openemr.forbiddenCatchType (lock cleanup must not hide a committed save)
            ServiceContainer::getLogger()->error('LBF statements: could not release the band lock.');
        }
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
     * Advisory lock for every band that can read this field.
     *
     * The second field is only a fallback, so it does not get its own lock.
     */
    public static function bandLockName(string $formId, string $source): string
    {
        return 'lbfstmt_' . md5($formId . "\0" . $source);
    }

    /**
     * True when both bands can apply to one patient.
     *
     * A blank sex applies to every patient. Two different sexes do not.
     *
     * @param array<string, mixed> $left
     * @param array<string, mixed> $right
     */
    public static function sexesCanMatchSamePatient(array $left, array $right): bool
    {
        $leftSex = trim(Values::asString($left['match_token'] ?? ''));
        $rightSex = trim(Values::asString($right['match_token'] ?? ''));
        if ($leftSex === '' || $rightSex === '') {
            return true;
        }

        return strcasecmp($leftSex, $rightSex) === 0;
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
        foreach ($this->lockedEnabledBands($formId, $source) as $other) {
            if ($id !== null && Values::rowInt($other, 'id') === $id) {
                continue;
            }
            if (!self::sexesCanMatchSamePatient($data, $other)) {
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
     * Enabled bands that can read this field, locked for the rest of the
     * transaction so a concurrent insert is visible after GET_LOCK.
     *
     * A second field does not split the set. It is a fallback for a blank
     * primary, and both bands can fire on the same measurement.
     *
     * @return list<array<string, mixed>>
     */
    private function lockedEnabledBands(string $formId, string $source): array
    {
        $rows = $this->sql->fetchRecords(
            "SELECT id, form_id, source_field_id, source_field_id_2, op, min_value, max_value, " .
            "min_inclusive, max_inclusive, match_token, statement_text, seq, enabled " .
            "FROM module_lbf_statement_rules WHERE form_id = ? AND source_field_id = ? " .
            "AND enabled = 1 AND op = 'band' FOR UPDATE",
            [$formId, $source]
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
