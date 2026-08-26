<?php

/**
 * Persistence for statement rules and run log.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\LbfStatements;

use OpenEMR\Common\Database\QueryUtils;

class StatementRepository
{
    /**
     * @return list<string>
     */
    public function formIdsWithRules(): array
    {
        $rows = QueryUtils::fetchRecords(
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
        $rows = QueryUtils::fetchRecords($sql, $bind);
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
     * @return array<string, mixed>|null
     */
    public function getRule(int $id): ?array
    {
        return Values::assocRow(QueryUtils::querySingleRow(
            "SELECT id, form_id, source_field_id, source_field_id_2, op, min_value, max_value, " .
            "min_inclusive, max_inclusive, match_token, statement_text, seq, enabled " .
            "FROM module_lbf_statement_rules WHERE id = ?",
            [$id]
        ));
    }

    /**
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
        $this->assertBandDoesNotOverlap($data, $id);
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
            QueryUtils::sqlStatementThrowException(
                "UPDATE module_lbf_statement_rules SET form_id=?, source_field_id=?, source_field_id_2=?, " .
                "op=?, min_value=?, max_value=?, min_inclusive=?, max_inclusive=?, match_token=?, " .
                "statement_text=?, seq=?, enabled=? WHERE id=?",
                array_merge($fields, [$id])
            );
            return $id;
        }
        return Values::asInt(QueryUtils::sqlInsert(
            "INSERT INTO module_lbf_statement_rules (" .
            "form_id, source_field_id, source_field_id_2, op, min_value, max_value, min_inclusive, " .
            "max_inclusive, match_token, statement_text, seq, enabled) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)",
            $fields
        ));
    }

    public function setEnabled(int $id, bool $enabled): void
    {
        if ($enabled) {
            $rule = $this->getRule($id);
            if ($rule !== null) {
                $rule['enabled'] = 1;
                $this->assertBandDoesNotOverlap($rule, $id);
            }
        }
        QueryUtils::sqlStatementThrowException(
            "UPDATE module_lbf_statement_rules SET enabled = ? WHERE id = ?",
            [$enabled ? 1 : 0, $id]
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public function assertBandDoesNotOverlap(array $data, ?int $id = null): void
    {
        if (Values::asString($data['op'] ?? '') !== 'band') {
            return;
        }
        if (BandOverlap::invertedBounds($data)) {
            throw new \InvalidArgumentException('Minimum must be less than or equal to maximum.');
        }
        if (!Values::asBool($data['enabled'] ?? 0)) {
            return;
        }
        $formId = Values::asString($data['form_id'] ?? '');
        $source = Values::asString($data['source_field_id'] ?? '');
        $source2 = Values::asString($data['source_field_id_2'] ?? '');
        foreach ($this->rulesForForm($formId, true) as $other) {
            if ($id !== null && Values::rowInt($other, 'id') === $id) {
                continue;
            }
            if (Values::rowString($other, 'op') !== 'band') {
                continue;
            }
            if (Values::rowString($other, 'source_field_id') !== $source) {
                continue;
            }
            if (Values::rowString($other, 'source_field_id_2') !== $source2) {
                continue;
            }
            if (BandOverlap::rangesOverlap($data, $other)) {
                throw new \InvalidArgumentException(
                    'This numeric range overlaps another band on the same field.'
                );
            }
        }
    }

    public function logRun(string $formId, int $pid, int $instanceFormId, string $user, string $mode): void
    {
        Identifiers::assertFieldId($formId);
        if (!in_array($mode, ['append', 'overwrite'], true)) {
            throw new \InvalidArgumentException('Invalid mode');
        }
        QueryUtils::sqlInsert(
            "INSERT INTO module_lbf_statement_runs (form_id, pid, instance_form_id, user, mode) " .
            "VALUES (?,?,?,?,?)",
            [$formId, $pid, $instanceFormId, $user, $mode]
        );
    }
}
