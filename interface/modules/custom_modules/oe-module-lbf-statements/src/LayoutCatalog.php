<?php

/**
 * LBF layout metadata.
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

class LayoutCatalog
{
    /**
     * @return list<array{form_id:string,title:string}>
     */
    public function listLbfForms(): array
    {
        $rows = QueryUtils::fetchRecords(
            "SELECT grp_form_id, grp_title FROM layout_group_properties " .
            "WHERE grp_form_id LIKE 'LBF%' AND grp_group_id = '' AND grp_activity = 1 " .
            "ORDER BY grp_title, grp_form_id"
        );
        $out = [];
        foreach ($rows as $raw) {
            $row = Values::assocRow($raw);
            if ($row === null) {
                continue;
            }
            $formId = Values::rowString($row, 'grp_form_id');
            if ($formId === '') {
                continue;
            }
            $title = Values::rowString($row, 'grp_title');
            $out[] = [
                'form_id' => $formId,
                'title' => $title !== '' ? $title : $formId,
            ];
        }
        return $out;
    }

    /**
     * @return array<string, array{data_type:int,title:string,list_id:string,seq:int,group_id:string}>
     */
    public function fieldMeta(string $formId): array
    {
        Identifiers::assertFieldId($formId);
        $rows = QueryUtils::fetchRecords(
            "SELECT field_id, data_type, title, list_id, seq, group_id FROM layout_options " .
            "WHERE form_id = ? AND field_id != '' AND uor > 0 ORDER BY group_id, seq",
            [$formId]
        );
        $out = [];
        foreach ($rows as $raw) {
            $row = Values::assocRow($raw);
            if ($row === null) {
                continue;
            }
            $fieldId = Values::rowString($row, 'field_id');
            if ($fieldId === '') {
                continue;
            }
            $out[$fieldId] = [
                'data_type' => Values::rowInt($row, 'data_type'),
                'title' => Values::rowString($row, 'title'),
                'list_id' => Values::rowString($row, 'list_id'),
                'seq' => Values::rowInt($row, 'seq'),
                'group_id' => Values::rowString($row, 'group_id'),
            ];
        }
        return $out;
    }

    public function paragraphField(string $formId): string
    {
        Identifiers::assertFieldId($formId);
        $row = Values::assocRow(QueryUtils::querySingleRow(
            "SELECT paragraph_field_id FROM module_lbf_statement_forms WHERE form_id = ?",
            [$formId]
        ));
        if ($row !== null) {
            $configured = Values::rowString($row, 'paragraph_field_id');
            if ($configured !== '') {
                return $configured;
            }
        }
        $meta = $this->fieldMeta($formId);
        if (isset($meta['summary_comments'])) {
            return 'summary_comments';
        }
        foreach ($meta as $fieldId => $info) {
            if ($info['data_type'] === 3) {
                return $fieldId;
            }
        }
        return $this->ensureParagraphField($formId);
    }

    public function saveParagraphField(string $formId, string $fieldId): void
    {
        Identifiers::assertFieldId($formId);
        Identifiers::assertFieldId($fieldId);
        QueryUtils::sqlStatementThrowException(
            "REPLACE INTO module_lbf_statement_forms (form_id, paragraph_field_id) VALUES (?, ?)",
            [$formId, $fieldId]
        );
    }

    public function ensureParagraphField(string $formId): string
    {
        Identifiers::assertFieldId($formId);
        $exists = Values::assocRow(QueryUtils::querySingleRow(
            "SELECT field_id FROM layout_options WHERE form_id = ? AND field_id = 'stmt_paragraph'",
            [$formId]
        ));
        $existingId = $exists !== null ? Values::rowString($exists, 'field_id') : '';
        if ($existingId === '') {
            $group = Values::assocRow(QueryUtils::querySingleRow(
                "SELECT grp_group_id FROM layout_group_properties " .
                "WHERE grp_form_id = ? AND grp_group_id != '' ORDER BY grp_seq LIMIT 1",
                [$formId]
            ));
            $gid = '1';
            if ($group !== null) {
                $fromGroup = Values::rowString($group, 'grp_group_id');
                if ($fromGroup !== '') {
                    $gid = $fromGroup;
                }
            }
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO layout_options (form_id, field_id, group_id, title, seq, data_type, uor, " .
                "fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, " .
                "description, fld_rows, list_backup_id, source, conditions, validation, codes) VALUES (" .
                "?, 'stmt_paragraph', ?, 'Generated statements', 1, 3, 1, 40, 0, '', 1, 6, '', '', " .
                "'Narrative written by Form statements. Printed on the form PDF.', 4, '', 'F', NULL, NULL, '')",
                [$formId, $gid]
            );
        }
        $this->saveParagraphField($formId, 'stmt_paragraph');
        return 'stmt_paragraph';
    }
}
