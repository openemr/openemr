<?php

/**
 * Search class for layout field data types.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Layouts;

/**
 * Categorize layout field data types by their search UI treatment.
 *
 * Each layout field has a numeric data_type that determines how it renders.
 * For search purposes, those data types fall into three classes: not searchable,
 * text input, or select list.
 */
enum SearchClass
{
    case NotSearchable;
    case TextField;
    case SelectList;

    /**
     * Determine the search class for a given layout field data type.
     */
    public static function fromDataType(int $dataType): self
    {
        return match ($dataType) {
            1, 10, 11, 12, 13, 14, 26, 35 => self::SelectList,
            2, 3, 4 => self::TextField,
            default => self::NotSearchable,
        };
    }

    /**
     * Determine the search class from a layout_options row.
     *
     * Accepts the raw sqlFetchArray result and narrows data_type internally,
     * so callers don't need to cast.
     *
     * @param non-empty-array<mixed> $row
     */
    public static function fromLayoutRow(array $row): self
    {
        $dataType = $row['data_type'] ?? 0;
        return self::fromDataType(is_numeric($dataType) ? (int) $dataType : 0);
    }
}
