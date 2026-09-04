<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\CodeTypes;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\Codes\ExternalCodesCreatedEvent;

/**
 * Lazy loader for the three code-type lookup tables that legacy code reads as
 * top-level globals ($code_types, $code_external_tables, $ct_external_options).
 * The `OEGlobalsBag` (which shadows $GLOBALS) is the source of truth: a getter
 * returns the bag entry when present, otherwise builds the array, writes it
 * into the bag, and returns it. Test code and `global $code_types;` readers
 * both see whichever value the bag currently holds.
 */
final class CodeTypeRegistry
{
    /**
     * @return array<int|string, mixed>
     */
    public static function codeTypes(): array
    {
        $bag = OEGlobalsBag::getInstance();
        $existing = $bag->get('code_types');
        if (is_array($existing)) {
            return $existing;
        }

        $codeTypes = [];
        $rows = QueryUtils::fetchRecords(
            "SELECT * FROM code_types WHERE ct_active=1 ORDER BY ct_seq, ct_key"
        );
        foreach ($rows as $row) {
            $key = is_string($row['ct_key'] ?? null) ? $row['ct_key'] : '';
            if ($key === '') {
                continue;
            }
            $label = $row['ct_label'] ?? '';
            $codeTypes[$key] = [
                'active' => $row['ct_active'],
                'id'   => $row['ct_id'],
                'fee'  => $row['ct_fee'],
                'mod'  => $row['ct_mod'],
                'just' => $row['ct_just'],
                'rel'  => $row['ct_rel'],
                'nofs' => $row['ct_nofs'],
                'diag' => $row['ct_diag'],
                'mask' => $row['ct_mask'],
                'label' => ($label === '' ? $key : $label),
                'external' => $row['ct_external'],
                'claim' => $row['ct_claim'],
                'proc' => $row['ct_proc'],
                'term' => $row['ct_term'],
                'problem' => $row['ct_problem'],
                'drug' => $row['ct_drug'],
            ];
            if (!array_key_exists($bag->getString('default_search_code_type'), $codeTypes)) {
                $bag->set('default_search_code_type', array_key_first($codeTypes));
            }
        }
        $bag->set('code_types', $codeTypes);

        return $codeTypes;
    }

    /**
     * @return array<int|string, mixed>
     */
    public static function codeExternalTables(): array
    {
        $bag = OEGlobalsBag::getInstance();
        $existing = $bag->get('code_external_tables');
        if (is_array($existing)) {
            return $existing;
        }

        $sctConceptJoin = fn(string $filter): array => [
            JOIN_TABLE => "sct_concepts",
            JOIN_FIELDS => ["sct_descriptions.ConceptId=sct_concepts.ConceptId", $filter],
        ];

        $tables = [
            // Original codes table (0) so lookup_code_descriptions can treat
            // it uniformly alongside the external tables below.
            0 => self::externalTableEntry('codes', 'code', 'code_text', 'code_text_short', [], 'id'),
            // ICD9
            4 => self::externalTableEntry('icd9_dx_code', 'formatted_dx_code', 'long_desc', 'short_desc', ["active='1'"], 'revision DESC'),
            5 => self::externalTableEntry('icd9_sg_code', 'formatted_sg_code', 'long_desc', 'short_desc', ["active='1'"], 'revision DESC'),
            // SNOMED (RF1)
            7 => self::externalTableEntry('sct_descriptions', 'ConceptId', 'Term', 'Term', ["DescriptionStatus=0", "DescriptionType=3"], ""),
            2 => self::externalTableEntry('sct_descriptions', 'ConceptId', 'Term', 'Term', ["DescriptionStatus=0", "DescriptionType=1"], "", [$sctConceptJoin("FullySpecifiedName like '%(disorder)'")]),
            9 => self::externalTableEntry('sct_descriptions', 'ConceptId', 'Term', 'Term', ["DescriptionStatus=0", "DescriptionType=1"], "", [$sctConceptJoin("FullySpecifiedName like '%(procedure)'")]),
            // SNOMED (RF2)
            11 => self::externalTableEntry('sct2_description', 'conceptId', 'term', 'term', ["active=1"], "", [], "", [CODE_COLUMN_TYPE => CODE_COLUMN_TYPE_NUMERIC, SKIP_TOTAL_TABLE_COUNT => true]),
            // ICD10
            1 => self::externalTableEntry('icd10_dx_order_code', 'formatted_dx_code', 'long_desc', 'short_desc', ["active='1'", "valid_for_coding = '1'"], 'revision DESC'),
            6 => self::externalTableEntry('icd10_pcs_order_code', 'pcs_code', 'long_desc', 'short_desc', ["active='1'", "valid_for_coding = '1'"], 'revision DESC'),
            // Value sets
            13 => self::externalTableEntry('valueset', 'code', 'description', 'description', [], ''),
            14 => self::externalTableEntry('valueset_oid', 'code', 'description', 'description', [], ''),
        ];

        // SNOMED (RF2) disorder + procedure — filter varies with language.
        [$disorderFilter, $procedureFilter] = isSnomedSpanish()
            ? ["term LIKE '%(trastorno)'", "term LIKE '%(procedimiento)'"]
            : ["term LIKE '%(disorder)'", "term LIKE '%(procedure)'"];
        $tables[10] = self::externalTableEntry('sct2_description', 'conceptId', 'term', 'term', ["active=1", $disorderFilter], "", [], "", [CODE_COLUMN_TYPE => CODE_COLUMN_TYPE_NUMERIC, SKIP_TOTAL_TABLE_COUNT => true]);
        $tables[12] = self::externalTableEntry('sct2_description', 'conceptId', 'term', 'term', ["active=1", $procedureFilter], "", [], "", [CODE_COLUMN_TYPE => CODE_COLUMN_TYPE_NUMERIC, SKIP_TOTAL_TABLE_COUNT => true]);

        $bag->set('code_external_tables', $tables);

        return $tables;
    }

    /**
     * @return array<int|string, mixed>
     */
    public static function ctExternalOptions(): array
    {
        $bag = OEGlobalsBag::getInstance();
        $existing = $bag->get('ct_external_options');
        if (is_array($existing)) {
            return $existing;
        }

        $options = [
            '0' => xl('No'),
            '4' => xl('ICD9 Diagnosis'),
            '5' => xl('ICD9 Procedure/Service'),
            '1' => xl('ICD10 Diagnosis'),
            '6' => xl('ICD10 Procedure/Service'),
            '2' => xl('SNOMED (RF1) Diagnosis'),
            '7' => xl('SNOMED (RF1) Clinical Term'),
            '9' => xl('SNOMED (RF1) Procedure'),
            '10' => xl('SNOMED (RF2) Diagnosis'),
            '11' => xl('SNOMED (RF2) Clinical Term'),
            '12' => xl('SNOMED (RF2) Procedure'),
            '13' => xl('CQM (Mixed Types) Value Set'),
            '14' => xl('CQM OID Value Set'),
        ];

        $event = new ExternalCodesCreatedEvent($options);
        $bag->getKernel()->getEventDispatcher()->dispatch(
            $event,
            ExternalCodesCreatedEvent::EVENT_HANDLE
        );
        $updated = $event->getExternalCodeData();
        $result = is_array($updated) ? $updated : $options;

        $bag->set('ct_external_options', $result);

        return $result;
    }

    /**
     * @param list<string> $filterClauses
     * @param list<array<string, mixed>> $joins
     * @param array<string, mixed> $extraColumns
     * @return array<string, mixed>
     */
    private static function externalTableEntry(
        string $tableName,
        string $colCode,
        string $colDescription,
        string $colDescriptionBrief,
        array $filterClauses = [],
        string $versionOrder = "",
        array $joins = [],
        string $displayDesc = "",
        array $extraColumns = [],
    ): array {
        return array_merge([
            EXT_TABLE_NAME => $tableName,
            EXT_COL_CODE => $colCode,
            EXT_COL_DESCRIPTION => $colDescription,
            EXT_COL_DESCRIPTION_BRIEF => $colDescriptionBrief,
            EXT_FILTER_CLAUSES => $filterClauses,
            EXT_JOINS => $joins,
            EXT_VERSION_ORDER => $versionOrder,
            DISPLAY_DESCRIPTION => $displayDesc,
            CODE_COLUMN_TYPE => CODE_COLUMN_TYPE_STRING,
            SKIP_TOTAL_TABLE_COUNT => false,
        ], $extraColumns);
    }
}
