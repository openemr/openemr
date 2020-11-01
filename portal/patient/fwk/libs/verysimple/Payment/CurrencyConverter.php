<?php

/** @package    verysimple::Payment */

/**
 * import supporting libraries
 */
require_once("verysimple/HTTP/HttpRequest.php");

/**
 * CurrencyConverter is a utility class for converting currencies.
 *
 * @package verysimple::Payment
 * @author VerySimple Inc.
 * @copyright 1997-2007 VerySimple, Inc.
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 * @version 2.0
 */
class CurrencyConverter
{

    /**
     * Converts currency using google.
     * throws an exception if something went wrong
     *
     * @param numeric $amount
     * @param
     *          string from currency (ex USD)
     * @param
     *          string to currency (ex EUR)
     * @return number
     * @author : http://www.it-base.ro/2007/07/09/currency-conversion-in-php
     * @author : Jason M Hinkle
     * @version : 1.0
     */
    public static function Convert($amount, $from, $to)
    {
        $converted_amount = 0;

        $qs = $amount . ' ' . $from . ' in ' . $to;
        $url = "http://www.google.com/search?q=" . urlEncode($qs);

        $g_response = strip_tags(HttpRequest::Get($url));

        if (preg_match("/Rates provided for information only - see disclaimer./i", $g_response)) {
            $matches = array ();
            preg_match('/= ([0-9\s\.,]+)/', $g_response, $matches);
            if ($matches [1]) {
                $converted_amount = $matches [1];
            } else {
                // this should never occur unless google changes the output formatting of the search results
                throw new Exception("Unable to parse response from google");
            }
        } else {
            throw new Exception("The google search result does not appear to contain currency information");
        }

        return $converted_amount;
    }
}
