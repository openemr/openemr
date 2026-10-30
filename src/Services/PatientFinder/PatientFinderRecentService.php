<?php

/**
 * PatientFinderRecentService
 *
 * Builds the "Recent Patients" tab of the Patient Finder: the configured
 * recent_patient_columns (validated against patient_data) and, for each patient
 * in the user's recent list, the selected column values with date/datetime
 * columns formatted per the site's display settings. Extracted verbatim from the
 * inline rp() helper in dynamic_finder.php.
 *
 * The data sources (recent list, date-column metadata, per-patient query),
 * identifier escaper and display-format settings are injected (defaulting to
 * core's PatientService / OEGlobalsBag), so the assembly is unit-testable.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Open Plan IT Ltd
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientFinder;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\PatientService;

final class PatientFinderRecentService
{
    /** @var callable(string, array<int, string|int>): list<array<mixed>> */
    private $fetchRecords;

    /** @var callable(): array<int, array<string, mixed>> */
    private $recentPatientList;

    /** @var callable(): array{date: array<int, string>, datetime: array<int, string>} */
    private $dateColumns;

    /** @var callable(string, array<int, string|int>): (array<mixed>|false) */
    private $singleRow;

    /** @var callable(string, array<int, string>): string */
    private $columnEscaper;

    private readonly int $dateDisplayFormat;

    private readonly int $timeDisplayFormat;

    public function __construct(
        ?callable $fetchRecords = null,
        ?callable $recentPatientList = null,
        ?callable $dateColumns = null,
        ?callable $singleRow = null,
        ?callable $columnEscaper = null,
        ?int $dateDisplayFormat = null,
        ?int $timeDisplayFormat = null
    ) {
        $this->fetchRecords = $fetchRecords ?? QueryUtils::fetchRecords(...);
        $patientServiceFactory = static fn(): PatientService => new PatientService();
        $this->recentPatientList = $recentPatientList
            ?? static fn(): array => $patientServiceFactory()->getRecentPatientList();
        $this->dateColumns = $dateColumns
            ?? static fn(): array => $patientServiceFactory()->getDateColumns();
        $this->singleRow = $singleRow ?? QueryUtils::querySingleRow(...);
        // Validate against patient_data and throw (rather than die) on a bad name.
        $this->columnEscaper = $columnEscaper
            ?? static fn(string $col, array $tables): string => escape_sql_column_name($col, $tables, false, true);
        $globals = OEGlobalsBag::getInstance();
        $this->dateDisplayFormat = $dateDisplayFormat ?? $globals->getInt('date_display_format');
        $this->timeDisplayFormat = $timeDisplayFormat ?? $globals->getInt('time_display_format');
    }

    /**
     * @return array{headers: array<int, array<mixed>>, rp: array<int, array<mixed>|false>}
     */
    public function build(): array
    {
        $headers = $this->resolveHeaders();
        $dateColumns = ($this->dateColumns)();
        $dateCols = $dateColumns['date'];
        $datetimeCols = $dateColumns['datetime'];

        $select = 'SELECT pid';
        foreach ($headers as $header) {
            $colName = PatientFinderValue::string($header['option_id'] ?? null);
            $escaped = ($this->columnEscaper)($colName, ['patient_data']);
            $select .= ', ';
            if (in_array($colName, $dateCols, true) || in_array($colName, $datetimeCols, true)) {
                $format = $this->dateFormat(in_array($colName, $datetimeCols, true));
                $select .= "DATE_FORMAT($escaped, $format) AS $escaped";
            } else {
                $select .= $escaped;
            }
        }
        $select .= ' FROM patient_data WHERE pid = ?';

        $rows = [];
        foreach (($this->recentPatientList)() as $patient) {
            $rows[] = ($this->singleRow)($select, [PatientFinderValue::int($patient['pid'] ?? null)]);
        }

        return ['headers' => $headers, 'rp' => $rows];
    }

    /**
     * The validated recent_patient_columns rows. A column is dropped when its
     * option_id is '*' or does not resolve to a real patient_data column (the
     * read-side guard matching addlistitem.php's write-side validation).
     *
     * @return array<int, array<mixed>>
     */
    private function resolveHeaders(): array
    {
        $rows = ($this->fetchRecords)(
            "SELECT option_id, title FROM list_options "
            . "WHERE list_id = 'recent_patient_columns' AND activity = '1' ORDER BY seq ASC",
            []
        );
        $headers = [];
        foreach ($rows as $row) {
            $optionId = is_string($row['option_id'] ?? null) ? $row['option_id'] : '';
            if ($optionId === '*') {
                continue;
            }
            try {
                ($this->columnEscaper)($optionId, ['patient_data']);
            } catch (SqlQueryException) {
                continue;
            }
            $headers[] = $row;
        }
        return $headers;
    }

    /**
     * Build the MySQL DATE_FORMAT pattern (a quoted SQL literal) for the site's
     * configured date (and, for datetime columns, time) display format.
     */
    private function dateFormat(bool $includeTime): string
    {
        $format = match ($this->dateDisplayFormat) {
            1 => "'%m/%d/%Y",   // MM/DD/YYYY
            2 => "'%d/%m/%Y",   // DD/MM/YYYY
            default => "'%Y-%m-%d", // MySQL YYYY-MM-DD
        };
        if ($includeTime) {
            $format .= $this->timeDisplayFormat === 1 ? ' %r' : ' %T';
        }
        return $format . "'";
    }
}
