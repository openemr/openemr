<?php

/**
 * DuplicatePatientRow is one line of the Duplicate Patient Management report.
 *
 * It carries three things: the control fields that drive behaviour (which chart the merge links
 * point at, how the row is highlighted), the raw patient_data row that column renderers read, and
 * the cells those renderers produced. Cells are rendered once, here, so the HTML table and the CSV
 * export cannot disagree about what a column says.
 *
 * Rows come in two kinds. The first row of a group is the "primary" -- the patient whose stored
 * dupscore put the group on the report -- and offers the Mark Unique / Recompute actions. The rest
 * are "matches", scored against that primary, and offer the merge actions.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2017-2021 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2026 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Patient;

final class DuplicatePatientRow
{
    /** Scope label for a chart that should be merged away. */
    public const SCOPE_MERGE_FROM = 'Merge From';

    /** Scope label for the chart the group should be merged into. */
    public const SCOPE_MERGE_TO = 'Merge To';

    /**
     * Rendered cells keyed by column key. Populated by {@see self::renderCells()} once the report's
     * column list is known, because a module can change that list.
     *
     * @var array<string, string>
     */
    private array $values = [];

    /**
     * @param array<mixed> $data           The raw patient_data row; what column renderers read.
     * @param string       $scopeLabel     Untranslated label ('', 'Merge From', 'Merge To').
     * @param string       $highlightClass CSS class driving the row background ('', 'highlight',
     *                                     'highlight-master').
     * @param int          $topPid         The pid of the group's primary row. The merge actions need
     *                                     both it and this row's pid to know which chart survives.
     * @param bool         $isMatch        True for a scored match, false for the group's primary row.
     */
    private function __construct(
        public readonly int $pid,
        public readonly int $score,
        public readonly int $topPid,
        public readonly bool $isMatch,
        public readonly string $scopeLabel,
        public readonly string $highlightClass,
        public readonly array $data,
    ) {
    }

    /**
     * The row a group is built around, scored by its stored dupscore.
     *
     * @param array<mixed> $row A patient_data row.
     */
    public static function forPrimary(array $row, int $highlightThreshold): self
    {
        return self::fromPatientRow($row, self::intField($row, 'pid'), false, $highlightThreshold);
    }

    /**
     * A candidate duplicate, scored against the group's primary row.
     *
     * @param array<mixed> $row A patient_data row plus a `myscore` column.
     */
    public static function forMatch(array $row, int $topPid, int $highlightThreshold): self
    {
        return self::fromPatientRow($row, $topPid, true, $highlightThreshold);
    }

    /**
     * @param array<mixed> $row
     */
    private static function fromPatientRow(
        array $row,
        int $topPid,
        bool $isMatch,
        int $highlightThreshold
    ): self {
        // The stored dupscore decides the highlight for every row; a match that scores high against
        // this particular primary then overrides it to mark the chart to merge into.
        $storedScore = self::intField($row, 'dupscore');
        $matchScore = self::intField($row, 'myscore');

        $scopeLabel = '';
        $highlightClass = '';
        if ($storedScore > $highlightThreshold) {
            $scopeLabel = self::SCOPE_MERGE_FROM;
            $highlightClass = 'highlight';
        }
        if ($isMatch && $matchScore > $highlightThreshold) {
            $scopeLabel = self::SCOPE_MERGE_TO;
            $highlightClass = 'highlight-master';
        }

        return new self(
            pid: self::intField($row, 'pid'),
            score: $isMatch ? $matchScore : $storedScore,
            topPid: $topPid,
            isMatch: $isMatch,
            scopeLabel: $scopeLabel,
            highlightClass: $highlightClass,
            data: $row,
        );
    }

    /**
     * Render this row's cells for the given columns.
     *
     * @param list<DuplicatePatientColumn> $columns
     */
    public function renderCells(array $columns): void
    {
        $values = [];
        foreach ($columns as $column) {
            $values[$column->key] = $column->render($this);
        }
        $this->values = $values;
    }

    /**
     * @return array<string, string>
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * A single patient_data field as text, for column renderers.
     */
    public function field(string $name): string
    {
        return self::stringField($this->data, $name);
    }

    /**
     * @param array<mixed> $row
     */
    private static function stringField(array $row, string $field): string
    {
        $value = $row[$field] ?? '';
        return is_scalar($value) ? (string) $value : '';
    }

    /**
     * @param array<mixed> $row
     */
    private static function intField(array $row, string $field): int
    {
        $value = $row[$field] ?? 0;
        return is_numeric($value) ? (int) $value : 0;
    }
}
