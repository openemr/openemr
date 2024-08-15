<?php

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
     * @return string
     */
    public static function post($var, $default = ''): string
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

    public static function controller_base_dir(): string
    {
        return realpath(self::base_dir() . '/controllers/');
    }

    public static function controller_dir($controller): string
    {
        $dir = self::controller_base_dir() . '/' . $controller;
        if (realpath($dir . '/../') != self::controller_base_dir()) {
            throw new \Exception("Invalid controller '$controller'");
        }

        return $dir;
    }
}
