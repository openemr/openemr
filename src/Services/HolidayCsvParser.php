<?php

/**
 * Holiday CSV parser built on league/csv.
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

use DateTimeImmutable;
use League\Csv\Exception as CsvException;
use League\Csv\Reader;

final class HolidayCsvParser implements HolidayCsvParserInterface
{
    /**
     * Date formats accepted in column 0 of the CSV.
     */
    private const ACCEPTED_DATE_FORMATS = ['Y-m-d', 'Y/m/d'];

    public function parse(string $path): iterable
    {
        try {
            $reader = Reader::from($path);
        } catch (CsvException $e) {
            throw new InvalidHolidayCsvException(
                xl('Unable to read uploaded file'),
                previous: $e,
            );
        }

        $rowNumber = 0;
        $emitted = 0;
        $sawNonEmptyRow = false;
        foreach ($reader->getRecords() as $record) {
            $rowNumber++;
            $rawFirst = $record[0] ?? null;
            $first = is_string($rawFirst) ? trim($rawFirst) : '';
            if ($first === '') {
                continue;
            }
            $rawSecond = $record[1] ?? null;
            $second = is_string($rawSecond) ? $rawSecond : '';
            if (!$sawNonEmptyRow && self::isHeaderRow($first, $second)) {
                $sawNonEmptyRow = true;
                continue;
            }
            $sawNonEmptyRow = true;

            if ($rawSecond === null) {
                throw new InvalidHolidayCsvException(
                    sprintf(xl('Row %1$d: CSV row must have date and description'), $rowNumber),
                    rowNumber: $rowNumber,
                );
            }

            $date = self::parseDate($first);
            if ($date === null) {
                throw new InvalidHolidayCsvException(
                    sprintf(xl('Row %1$d: Invalid date format in CSV'), $rowNumber),
                    rowNumber: $rowNumber,
                );
            }

            $emitted++;
            yield new HolidayRow($date, trim($second));
        }

        if ($emitted === 0) {
            throw new InvalidHolidayCsvException(xl('CSV file is empty'));
        }
    }

    private static function isHeaderRow(string $first, string $second): bool
    {
        return strtolower($first) === 'date' && strtolower(trim($second)) === 'description';
    }

    private static function parseDate(string $value): ?DateTimeImmutable
    {
        foreach (self::ACCEPTED_DATE_FORMATS as $format) {
            $dt = DateTimeImmutable::createFromFormat('!' . $format, $value);
            if ($dt instanceof DateTimeImmutable && $dt->format($format) === $value) {
                return $dt;
            }
        }
        return null;
    }
}
