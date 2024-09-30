<?php

/**
 * Common.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Aron Racho <aron@mi-squared.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Discover And Change, Inc. <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2010-2011 Aron Racho <aron@mi-squared.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\ClinicalDecisionRules\Interface;

class Common
{
    /**
     * This is a wrapper for implode function, which calls each function in the
     * array $funcs on each piece in the array $pieces
     *
     * @param string $glue
     * @param array $pieces
     * @param array $funcs
     * @return string
     */
    public static function implode_funcs($glue, array $pieces, array $funcs): string
    {
        $newPieces = [];
        foreach ($pieces as $piece) {
            $newPiece = $piece;
            foreach ($funcs as $func) {
                $newPiece = $func($newPiece);
            }
            $newPieces[] = $newPiece;
        }

        return implode($glue, $newPieces);
    }

    /**
     * * xxx todo: sanitize inputs
     *
     * @param string $var
     * @param string $default
     * @return string
     */
    public static function get($var, $default = ''): string
    {
        $val = $_GET[$var] ?? null;
        return isset($val) && $val !== '' ? $val : $default;
    }

    /**
     * xxx todo: sanitize inputs
     *
     * @param string $var
     * @param string $default
     * @return string|string[] returns a string value or an array of string values
     */
    public static function post($var, $default = ''): string|array
    {
        $val = $_POST[$var] ?? null;
        return isset($val) && $val !== '' ? $val : $default;
    }

    public static function base_url(): string
    {
        return $GLOBALS['webroot'] . '/interface/super/rules';
    }

    public static function src_dir(): string
    {
        return $GLOBALS['srcdir'];
    }

    public static function template_dir(): string
    {
        return $GLOBALS['template_dir'] . 'super' . DIRECTORY_SEPARATOR . 'rules' . DIRECTORY_SEPARATOR;
    }

    public static function base_dir(): string
    {
        return $GLOBALS['incdir'] . '/super/rules/';
    }

    public static function library_dir(): string
    {
        return self::base_dir() . 'library';
    }

    public static function library_src($file): string
    {
        return self::library_dir() . "/$file";
    }

    public static function js_src($file): void
    {
        echo self::base_url() . '/www/js/' . $file;
    }

    public static function css_src($file): void
    {
        echo self::base_url() . '/www/css/' . $file;
    }
}
