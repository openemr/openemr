<?php

/**
 * The filters of an invoice search, and the SQL condition they make.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\Invoice;

use OpenEMR\Billing\InvoiceServiceDates;

/**
 * The condition is on form_encounter aliased `f` and patient_data aliased `p`.
 */
final readonly class InvoiceSearchCriteria
{
    /**
     * @param string $name any part of the last name, "last, first" (prefixes), or "X-Y" (last names starting X through Y)
     * @param string $pid chart number (patient_data.pid)
     * @param string $encounter encounter number
     * @param string $lastStatementBefore Y-m-d: only invoices last sent a statement before it, or never; '' for any
     */
    public function __construct(
        public InvoiceCategory $category,
        public string $name = '',
        public string $pid = '',
        public string $encounter = '',
        public InvoiceServiceDates $serviceDates = new InvoiceServiceDates('', ''),
        public string $lastStatementBefore = '',
    ) {
    }

    public function hasFilter(): bool
    {
        return $this->name !== '' || $this->pid !== '' || $this->encounter !== ''
            || $this->serviceDates->isSet() || $this->lastStatementBefore !== '';
    }

    /**
     * The WHERE condition and its binds, or null for an All search with no filter
     * (it would list every invoice ever).
     *
     * @return ?array{string, list<string>}
     */
    public function condition(): ?array
    {
        if (!$this->hasFilter()) {
            return $this->category === InvoiceCategory::All ? null : ['1 = 1', []];
        }
        $conditions = [];
        $binds = [];
        if ($this->name !== '') {
            [$nameCondition, $nameBinds] = $this->nameCondition();
            $conditions[] = $nameCondition;
            array_push($binds, ...$nameBinds);
        }
        if ($this->pid !== '') {
            $conditions[] = 'f.pid = ?';
            $binds[] = $this->pid;
        }
        if ($this->encounter !== '') {
            $conditions[] = 'f.encounter = ?';
            $binds[] = $this->encounter;
        }
        if ($this->serviceDates->isSet()) {
            [$dateCondition, $dateBinds] = $this->serviceDates->condition('f.date');
            $conditions[] = $dateCondition;
            array_push($binds, ...$dateBinds);
        }
        if ($this->lastStatementBefore !== '') {
            $conditions[] = '(f.last_stmt_date IS NULL OR f.last_stmt_date < ?)';
            $binds[] = $this->lastStatementBefore;
        }
        return [implode(' AND ', $conditions), $binds];
    }

    /**
     * The invoices an ERA's claims point at, or none when it points at none.
     *
     * @param list<array{string, string}> $encounters pid, encounter, as the ERA gave them
     * @return array{string, list<string>}
     */
    public static function forEncounters(array $encounters): array
    {
        if ($encounters === []) {
            return ['1 = 2', []];
        }
        $binds = [];
        foreach ($encounters as [$pid, $encounter]) {
            $binds[] = $pid;
            $binds[] = $encounter;
        }
        return [
            '(' . implode(' OR ', array_fill(0, count($encounters), '(f.pid = ? AND f.encounter = ?)')) . ')',
            $binds,
        ];
    }

    /**
     * @return array{string, list<string>}
     */
    private function nameCondition(): array
    {
        // The last name followed by a comma and the start of a first name.
        if (preg_match('/^(.*\S)\s*,\s*(.*)/', $this->name, $matches) === 1) {
            return ['p.lname LIKE ? AND p.fname LIKE ?', [$matches[1] . '%', $matches[2] . '%']];
        }
        // "A-C": last names starting with A through C.
        if (preg_match('/^(\S)\s*-\s*(\S)$/', $this->name, $matches) === 1) {
            $conditions = [];
            $binds = [];
            for ($letter = ord($matches[1]); $letter <= ord($matches[2]); $letter++) {
                $conditions[] = 'p.lname LIKE ?';
                $binds[] = chr($letter) . '%';
            }
            return $conditions === [] ? ['1 = 2', []] : ['(' . implode(' OR ', $conditions) . ')', $binds];
        }
        return ['p.lname LIKE ?', ['%' . $this->name . '%']];
    }
}
