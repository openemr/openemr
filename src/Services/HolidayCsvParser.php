<?php

/**
 * Holiday CSV parser.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    sharonco <sharonco@matrix.co.il>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2016 Sharon Cohen <sharonco@matrix.co.il>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

final class HolidayCsvParser implements HolidayCsvParserInterface
{
    private const CSV_LENGTH = 1000;
    private const CSV_DELIMITER = ',';
    private const CSV_ENCLOSURE = '"';
    private const CSV_ESCAPE = '\\';

    private string $lastError = '';

    public function readNextDataRow($handle): ?array
    {
        while (
            ($data = fgetcsv(
                $handle,
                self::CSV_LENGTH,
                self::CSV_DELIMITER,
                self::CSV_ENCLOSURE,
                self::CSV_ESCAPE,
            )) !== false
        ) {
            if (!isset($data[0]) || trim($data[0]) === '') {
                continue;
            }

            if ($this->isHeaderRow($data)) {
                continue;
            }

            return array_map(static fn($v): string => (string) $v, $data);
        }

        return null;
    }

    public function isValidCsvContent(string $path): bool
    {
        $this->lastError = '';
        $handle = fopen($path, 'r');
        if ($handle === false) {
            $this->lastError = xl('Unable to read uploaded file');
            return false;
        }

        try {
            $rowNumber = 0;
            while (($row = $this->readNextDataRow($handle)) !== null) {
                $rowNumber++;

                if (count($row) < 2) {
                    $this->lastError = sprintf(
                        xl('Row %1$d: CSV row must have date and description'),
                        $rowNumber
                    );
                    return false;
                }

                $date = trim($row[0]);
                if (!self::isValidHolidayDate($date)) {
                    $this->lastError = sprintf(
                        xl('Row %1$d: Invalid date format in CSV'),
                        $rowNumber
                    );
                    return false;
                }
            }

            if ($rowNumber === 0) {
                $this->lastError = xl('CSV file is empty');
                return false;
            }

            return true;
        } finally {
            fclose($handle);
        }
    }

    public function getLastError(): string
    {
        return $this->lastError;
    }

    /**
     * @param array<int, mixed> $row
     */
    private function isHeaderRow(array $row): bool
    {
        $first = strtolower(trim((string) ($row[0] ?? '')));
        $second = strtolower(trim((string) ($row[1] ?? '')));
        return $first === 'date' && $second === 'description';
    }

    public static function isValidHolidayDate(string $date): bool
    {
        if (preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $date) === 1) {
            $dt = \DateTimeImmutable::createFromFormat('Y/m/d', $date);
            return $dt !== false && $dt->format('Y/m/d') === $date;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1) {
            $dt = \DateTimeImmutable::createFromFormat('Y-m-d', $date);
            return $dt !== false && $dt->format('Y-m-d') === $date;
        }

        return false;
    }
}
