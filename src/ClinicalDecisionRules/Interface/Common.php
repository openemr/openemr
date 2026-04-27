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

use OpenEMR\Core\OEGlobalsBag;
use Symfony\Component\HttpFoundation\Request;

class Common
{
    private static ?Request $request = null;

    /**
     * Cache the Symfony Request built from the current globals so that the
     * many `get()`/`post()` helper invocations in CDR controllers do not
     * each rebuild the parameter bags from scratch. The cache is bound to
     * a single PHP process; in tests that mutate `$_GET`/`$_POST` between
     * cases, call {@see self::resetRequestCache()} to drop the snapshot.
     */
    private static function request(): Request
    {
        if (self::$request === null) {
            self::$request = Request::createFromGlobals();
        }
        return self::$request;
    }

    /**
     * Drop the cached Symfony Request. Tests that mutate `$_GET`/`$_POST`
     * between cases should call this in their setUp/tearDown so subsequent
     * `get()`/`post()` calls re-read the freshly-mutated globals.
     */
    public static function resetRequestCache(): void
    {
        self::$request = null;
    }

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
        $val = self::request()->query->all()[$var] ?? null;
        if (is_string($val) && $val !== '') {
            return $val;
        }
        return $default;
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
        $val = self::request()->request->all()[$var] ?? null;
        if (is_array($val)) {
            /** @var string[] $val */
            return $val;
        }
        if (is_string($val) && $val !== '') {
            return $val;
        }
        return $default;
    }

    /**
     * Like {@see self::post()} but always returns a string. Array values are
     * discarded and replaced with the default. Use this when a caller needs a
     * guaranteed scalar string (e.g., assigning to a typed string property).
     */
    public static function postString(string $var, string $default = ''): string
    {
        $val = self::post($var, $default);
        return is_string($val) ? $val : $default;
    }

    public static function base_url(): string
    {
        return OEGlobalsBag::getInstance()->getKernel()->getWebRoot() . '/interface/super/rules';
    }

    public static function src_dir(): string
    {
        return OEGlobalsBag::getInstance()->getKernel()->getSrcDir();
    }

    public static function template_dir(): string
    {
        return OEGlobalsBag::getInstance()->getKernel()->getTemplateDir() . 'super' . DIRECTORY_SEPARATOR . 'rules' . DIRECTORY_SEPARATOR;
    }

    public static function base_dir(): string
    {
        return OEGlobalsBag::getInstance()->getKernel()->getIncludeRoot() . '/super/rules/';
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
