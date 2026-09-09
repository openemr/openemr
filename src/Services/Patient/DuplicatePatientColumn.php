<?php

/**
 * DuplicatePatientColumn is one column of the Duplicate Patient Management report.
 *
 * The same column list drives the HTML table and the CSV export, so the two views cannot drift.
 * Modules add, remove and reorder columns by listening for
 * {@see \OpenEMR\Events\Patient\DuplicatePatientReportColumnsEvent}.
 *
 * Two rules bind anyone defining a column:
 *
 * 1. **Labels must come from a literal xl() call at the call site.** PHPStan requires a
 *    literal-string argument to xl(), and the translation extractor only finds literals -- a label
 *    assembled at runtime would never be translated.
 * 2. **Renderers return plain text, never markup.** The view escapes with text() and the CSV writer
 *    with csvEscape(), so returned HTML would be shown escaped rather than rendered.
 *
 * A column that needs a lookup should use $prepare rather than querying per row. It is handed every
 * row on the report at once, before any rendering, which is the difference between one query and
 * one query per patient.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Patient;

use Closure;

final readonly class DuplicatePatientColumn
{
    /**
     * @param string                                   $key     Stable identifier; also the key this
     *                                                          column's cell is stored under.
     * @param string                                   $label   Translated heading, from a literal
     *                                                          xl() call.
     * @param Closure(DuplicatePatientRow): string     $render  Plain text, never markup.
     * @param ?Closure(list<DuplicatePatientRow>): void $prepare Optional batch step, run once over
     *                                                          the whole report before rendering.
     */
    public function __construct(
        public string $key,
        public string $label,
        private Closure $render,
        private ?Closure $prepare = null,
    ) {
    }

    /**
     * Convenience for the common case: show one patient_data field verbatim.
     */
    public static function forField(string $key, string $label, ?string $field = null): self
    {
        $field ??= $key;
        return new self($key, $label, static fn(DuplicatePatientRow $row): string => $row->field($field));
    }

    /**
     * @param list<DuplicatePatientRow> $rows every row on the report, in display order
     */
    public function prepare(array $rows): void
    {
        if ($this->prepare !== null) {
            ($this->prepare)($rows);
        }
    }

    public function render(DuplicatePatientRow $row): string
    {
        return ($this->render)($row);
    }

    /**
     * The columns core's own report shows, in order.
     *
     * The Actions column is deliberately absent: it renders a <select> and its JavaScript, so it is
     * interaction rather than data and stays owned by the template.
     *
     * @return list<self>
     */
    public static function defaults(): array
    {
        return [
            new self('score', xl('Score'), static fn(DuplicatePatientRow $row): string => (string) $row->score),
            new self('pid', xl('Pid'), static fn(DuplicatePatientRow $row): string => (string) $row->pid),
            self::forField('pubpid', xl('Public')),
            new self('scope', xl('Scope'), static fn(DuplicatePatientRow $row): string => match ($row->scopeLabel) {
                DuplicatePatientRow::SCOPE_MERGE_FROM => xl('Merge From'),
                DuplicatePatientRow::SCOPE_MERGE_TO => xl('Merge To'),
                default => '',
            }),
            new self('name', xl('Name'), static fn(DuplicatePatientRow $row): string => $row->field('lname')
                . ', ' . $row->field('fname') . ' ' . $row->field('mname')),
            new self('DOB', xl('DOB'), static fn(DuplicatePatientRow $row): string =>
                // Times are never shown, and a DOB carrying one would break the short-date format.
                self::formatShortDate(substr($row->field('DOB'), 0, 10))),
            self::forField('sex', xl('Gender')),
            self::forField('email', xl('Email')),
            new self('phones', xl('Telephone'), self::renderPhones(...)),
            new self('regdate', xl('Registered'), static fn(DuplicatePatientRow $row): string =>
                self::formatShortDate($row->field('regdate'))),
            self::forField('street', xl('Address')),
        ];
    }

    private static function renderPhones(DuplicatePatientRow $row): string
    {
        $phones = [];
        foreach (['phone_home', 'phone_biz', 'phone_cell'] as $field) {
            $phone = trim($row->field($field));
            if ($phone !== '') {
                $phones[] = $phone;
            }
        }

        return implode(', ', $phones);
    }

    private static function formatShortDate(string $date): string
    {
        $formatted = oeFormatShortDate($date);
        return is_scalar($formatted) ? (string) $formatted : '';
    }
}
