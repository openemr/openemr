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

class LayoutCatalog
{
    /**
     * @param Queries $sql Database access, or a test fake.
     */
    public function __construct(
        private readonly Queries $sql = new Queries()
    ) {
    }

    /**
     * Active LBF layouts for the rules editor dropdown.
     *
     * @return list<array{form_id:string,title:string}>
     */
    public function listLbfForms(): array
    {
        $rows = $this->sql->fetchRecords(
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
     * Visible fields on one layout, keyed by field_id.
     *
     * @return array<string, array{data_type:int,title:string,list_id:string,seq:int,group_id:string}>
     */
    public function fieldMeta(string $formId): array
    {
        Identifiers::assertFieldId($formId);
        $rows = $this->sql->fetchRecords(
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

    /**
     * Destination textarea for generated text. Does not create layout_options rows.
     */
    public function paragraphField(string $formId): string
    {
        Identifiers::assertFieldId($formId);
        $row = Values::assocRow($this->sql->querySingleRow(
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
        return '';
    }

    /**
     * Store which textarea on this layout receives generated statements.
     */
    public function saveParagraphField(string $formId, string $fieldId): void
    {
        Identifiers::assertFieldId($formId);
        Identifiers::assertFieldId($fieldId);
        $meta = $this->fieldMeta($formId);
        if (!isset($meta[$fieldId]) || $meta[$fieldId]['data_type'] !== 3) {
            throw new \InvalidArgumentException('Paragraph field must be a textarea on this layout.');
        }
        $this->sql->sqlStatementThrowException(
            "REPLACE INTO module_lbf_statement_forms (form_id, paragraph_field_id) VALUES (?, ?)",
            [$formId, $fieldId]
        );
    }

    /**
     * Create stmt_paragraph on this layout when an administrator asks for it.
     */
    public function ensureParagraphField(string $formId): string
    {
        Identifiers::assertFieldId($formId);
        $exists = Values::assocRow($this->sql->querySingleRow(
            "SELECT field_id FROM layout_options WHERE form_id = ? AND field_id = 'stmt_paragraph'",
            [$formId]
        ));
        $existingId = $exists !== null ? Values::rowString($exists, 'field_id') : '';
        if ($existingId === '') {
            $group = Values::assocRow($this->sql->querySingleRow(
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
            $this->sql->sqlStatementThrowException(
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
