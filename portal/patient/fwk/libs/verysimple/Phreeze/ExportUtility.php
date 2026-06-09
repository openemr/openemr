<?php

/** @package    verysimple::Phreeze */

/**
 * ExportUtility Class
 *
 * This contains various utility functions for exporting Phreezable objects into other formats
 * such as Excel, CSV, tab-delimited, XML, etc
 *
 * @package verysimple::Phreeze
 * @author VerySimple Inc. <noreply@verysimple.com>
 * @copyright 1997-2005 VerySimple Inc.
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 * @version 1.0
 */
class ExportUtility
{
    /**
     * Given a zero-based column number, the appropriate Excel column letter is
     * returned, ie A, B, AB, CJ, etc.
     * max supported is ZZ, higher than that will
     * throw an exception.
     *
     * @param int $columnNumber
     */
    static function GetColumnLetter($columnNumber)
    {
        // work with 1-based number
        $colNum = $columnNumber + 1;
        $code = "";

        if ($colNum > 26) {
            // greater than 26 means the column will be AA, AB, AC, etc.
            $left = floor($columnNumber / 26);
            $right = 1 + ($columnNumber % 26);

            if ($left > 26) {
                throw new Exception("Columns exceed supported amount");
            }

            $code = chr($left + 64) . chr($right + 64);
        } else {
            $code = chr($colNum + 64);
        }

        return $code;
    }
}
