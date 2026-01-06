<?php

/**
 * interface/main/holidays/Holidays_Csv.php shared helpers for holiday CSV parsing.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    sharonco <sharonco@matrix.co.il>
 * @copyright Copyright (c) 2016 Sharon Cohen <sharonco@matrix.co.il>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

class Holidays_Csv
{
    private const CSV_LENGTH = 1000;
    private const CSV_DELIMITER = ",";
    private const CSV_ENCLOSURE = "\"";
    private const CSV_ESCAPE = "\\";

    /**
     * @param resource $handle
     */
    public static function read_next_data_row($handle): ?array
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
            if (!isset($data[0]) || trim($data[0]) === "") {
                continue;
            }

            if (self::is_header_row($data)) {
                continue;
            }

            return $data;
        }

        return null;
    }

    private static function is_header_row(array $row): bool
    {
        $first = strtolower(trim($row[0] ?? ""));
        $second = strtolower(trim($row[1] ?? ""));
        return $first === "date" && $second === "description";
    }
}
