<?php

/**
 * A UDS Table 3A age band, identified by its form line number (1–38).
 *
 * Table 3A breaks ages into single years for 0–24 (Lines 1–25) and then
 * five-year bands up to "85 and over" (Lines 26–38). This wraps the line number
 * as a validated domain primitive so a raw, possibly out-of-range integer can
 * never be passed where a band is expected; build one with `fromAge()`.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

use DomainException;

final readonly class Table3aAgeBand
{
    public const FIRST_LINE = 1;
    public const LAST_LINE = 38;

    public function __construct(public int $line)
    {
        if ($line < self::FIRST_LINE || $line > self::LAST_LINE) {
            throw new DomainException('Table 3A age-band line must be between 1 and 38');
        }
    }

    /**
     * Resolve the band for a patient's whole-year age. Ages 0–24 map to their
     * own line (Line 1 is "under age 1"); 25–84 fall into five-year bands; 85
     * and over share the final line.
     */
    public static function fromAge(int $age): self
    {
        if ($age < 0) {
            throw new DomainException('Age cannot be negative');
        }

        if ($age <= 24) {
            return new self($age + 1);
        }

        if ($age >= 85) {
            return new self(self::LAST_LINE);
        }

        return new self(26 + intdiv($age - 25, 5));
    }

    public function label(): string
    {
        if ($this->line === 1) {
            return 'Under age 1';
        }
        if ($this->line <= 25) {
            return 'Age ' . ($this->line - 1);
        }
        if ($this->line === self::LAST_LINE) {
            return 'Age 85 and over';
        }

        $start = 25 + ($this->line - 26) * 5;

        return 'Ages ' . $start . '–' . ($start + 4);
    }
}
