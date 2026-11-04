<?php

/**
 * Checks a procedure_result row before it is written.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Validators;

use OpenEMR\Services\ProcedureResultStatus;

/**
 * OpenEMR connects with sql_mode '', so a value longer than its column is cut to fit without
 * an error and reads back changed. Each column's limit is checked here instead, in characters,
 * which is how MySQL counts a varchar. (Particle's lengthBetween, which BaseValidator builds on,
 * counts bytes, so it would refuse accented text that fits.)
 */
final class ProcedureResultValidator
{
    /**
     * varchar columns of procedure_result and their lengths, from sql/database.sql.
     *
     * @var array<string, int>
     */
    private const MAX_CHARACTERS = [
        'result_code' => 31,
        'result_text' => 255,
        'units' => 31,
        'result' => 255,
        'range' => 255,
        'abnormal' => 31,
    ];

    /** procedure_result.comments is a TEXT column, which holds 65,535 bytes. */
    private const COMMENTS_MAX_BYTES = 65535;

    /** N numeric, S string: the two data types a single result value is stored as. */
    private const DATA_TYPES = ['N', 'S'];

    /**
     * @param array<string, mixed> $row The columns about to be written; absent columns are not checked.
     * @return array<string, string> Column => what is wrong with it; empty when the row is valid.
     */
    public function validate(array $row): array
    {
        $errors = [];
        foreach (self::MAX_CHARACTERS as $column => $max) {
            if (!array_key_exists($column, $row)) {
                continue;
            }
            $value = $row[$column];
            if (!is_string($value)) {
                $errors[$column] = $column . ' must be a string';
            } elseif (mb_strlen($value) > $max) {
                $errors[$column] = $column . ' is longer than ' . $max . ' characters';
            }
        }

        if (array_key_exists('result_code', $row) && $row['result_code'] === '') {
            $errors['result_code'] = 'result_code is required';
        }
        if (array_key_exists('result', $row) && $row['result'] === '') {
            $errors['result'] = 'result is required';
        }

        if (array_key_exists('comments', $row)) {
            $comments = $row['comments'];
            if (!is_string($comments)) {
                $errors['comments'] = 'comments must be a string';
            } elseif (strlen($comments) > self::COMMENTS_MAX_BYTES) {
                $errors['comments'] = 'comments is longer than ' . self::COMMENTS_MAX_BYTES . ' bytes';
            }
        }

        if (array_key_exists('result_data_type', $row) && !in_array($row['result_data_type'], self::DATA_TYPES, true)) {
            $errors['result_data_type'] = 'result_data_type must be N or S';
        }

        if (array_key_exists('result_status', $row)) {
            $status = $row['result_status'];
            if (!is_string($status) || ProcedureResultStatus::tryFrom($status) === null) {
                $errors['result_status'] = 'result_status must be one of the proc_res_status ids this service writes';
            }
        }

        return $errors;
    }
}
