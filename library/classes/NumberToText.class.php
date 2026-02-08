<?php

//pulled from www.djgeespot.com/php, reworked as a class

/** Serialized Array of big names, thousand, million, etc
* @package NumberToText */

define("N2T_BIG", serialize(['thousand', 'million', 'billion', 'trillion', 'quadrillion', 'quintillion', 'sextillion', 'septillion', 'octillion', 'nonillion', 'decillion', 'undecillion', 'duodecillion', 'tredecillion', 'quattuordecillion', 'quindecillion', 'sexdecillion', 'septendecillion', 'octodecillion', 'novemdecillion', 'vigintillion']));
/** Serialized Array of medium names, twenty, thirty, etc
* @package NumberToText */
define("N2T_MEDIUM", serialize([2 => 'twenty', 3 => 'thirty', 4 => 'forty', 5 => 'fifty', 6 => 'sixty', 7 => 'seventy', 8 => 'eighty', 9 => 'ninety']));
/** Serialized Array of small names, zero, one, etc.. up to eighteen, nineteen
* @package NumberToText */
define("N2T_SMALL", serialize(['zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen']));
/** Word for "dollars"
* @package NumberToText */
define("N2T_DOLLARS", "dollars");
/** Word for one "dollar"
* @package NumberToText */
define("N2T_DOLLARS_ONE", "dollar");
/** Word for "cents"
* @package NumberToText */
define("N2T_CENTS", "cents");
/** Word for one "cent"
* @package NumberToText */
define("N2T_CENTS_ONE", "cent");
/** Word for "and"
* @package NumberToText */
define("N2T_AND", "and");
/** Word for "negative"
* @package NumberToText */
define("N2T_NEGATIVE", "negative");

class NumberToText
{
    function __construct(public $number, public $currency = false, public $capitalize = false, public $and = false)
    {
    }

    /** Number to text converter. Converts a number into a textual description, such as
    * "one hundred thousand and twenty-five".
    *
    * Now supports _any_ size number, and negative numbers. To pass numbers > 2 ^32, you must
    * pass them as a string, as PHP only has 32-bit integers.
    *
    * @author Greg MacLelan
    * @version 1.1
    * @param int  $number      The number to convert
    * @param bool $currency    True to convert as a dollar amount
    * @param bool $capitalize  True to capitalize every word (except "and")
    * @param bool $and         True to use "and"  (ie. "one hundred AND six")
    * @return The textual description of the number, as a string.
    * @package NumberToText
    */

    function convert()
    {
        $number = $this->number;
        $currency = $this->currency;
        $capitalize = $this->capitalize;
        $and = $this->and;

        $big = unserialize(N2T_BIG, ['allowed_classes' => false]);
        $small = unserialize(N2T_SMALL, ['allowed_classes' => false]);

        // get rid of leading 0's
        /*
        while ($number{0} == 0) {
            $number = substr($number,1);
        }
        */

        if ($number === 0) {
            return "zero";
        }

        $text = "";

        //$negative = ($number < 0); // check for negative
        //$number = abs($number); // make sure we have a +ve number
        if (str_starts_with((string) $number, "-")) {
            $negative = true;
            $number = substr((string) $number, 1); // abs()
        } else {
            $negative = false;
        }

        // get the integer and decimal parts
        //$int_o = $int = floor($number); // store into two vars
        if ($pos = strpos((string) $number, ".")) {
            $int_o = $int = substr((string) $number, 0, $pos);
            $decimal_o = $decimal = substr((string) $number, $pos + 1);
        } else {
            $int_o = $int = $number;
            $decimal_o = $decimal = 0;
        }

        // $int_o and $decimal_o are for "original value"
        // conversion for integer part:

        $section = 0; // $section controls "thousand" "million" etc
        $text = '';
        do {
            // keep breaking down into 3 digits ($convert) and the rest
            //$convert = $int % 1000;
            //$int = floor($int / 1000);

            if ($section > count($big) - 1) {
                // ran out of names for numbers this big, call recursively
                $text = NumberToText($int, false, false, $and) . " " . $big[$section - 1] . " " . $text;
                $int = 0;
            } else {
                // we can handle it

                if (strlen((string) $int) < 3) {
                      $convert = $int;
                      $int = 0;
                } else {
                      $convert = substr((string) $int, -3); // grab the last 3 digits
                      $int = substr((string) $int, 0, -1 * strlen($convert));
                }

                if ($convert > 0) {
                    // we have something here, put it in
                    if ($section > 0) {
                        $text = $this->n2t_convertthree($convert, $and, ($int > 0)) . " " . $big[$section - 1] . " " . $text;
                    } else {
                        $text = $this->n2t_convertthree($convert, $and, ($int > 0));
                    }
                }
            }

            $section++;
        } while ($int > 0);

        // conversion for decimal part:

        if ($currency && floor($number)) {
            // add " dollars"
            $text .= " " . ($int_o == 1 ? N2T_DOLLARS_ONE : N2T_DOLLARS) . " ";
        }

        if ($decimal && $currency) {
            // if we have any cents, add those
            if ($int_o > 0) {
                $text .= " " . N2T_AND . " ";
            }

            $cents = substr($decimal, 0, 2); // (0.)2342 -> 23
            $decimal = substr($decimal, 2); // (0.)2345.. -> 45..

            $text .= $this->n2t_convertthree($cents, false, true); // explicitly show "and" if there was an $int
        }

        if ($decimal) {
            // any remaining decimals (whether or not $currency is set)
            $text .= " point";
            for ($i = 0; $i < strlen($decimal); $i++) {
                // go through one number at a time
                $text .= " " . $small[$decimal[$i]];
            }
        }


        if ($decimal_o && $currency) {
            // add " cents" (if we're doing currency and had decimals)
            $text .= " " . ($decimal_o == 1 ? N2T_CENTS_ONE : N2T_CENTS);
        }

        // check for negative
        if ($negative) {
            $text = N2T_NEGATIVE . " " . $text;
        }

        // capitalize words
        if ($capitalize) {
            // easier to capitalize all words then un-capitalize "and"
            $text = str_replace(ucwords(N2T_AND), N2T_AND, ucwords((string) $text));
        }

        return trim((string) $text);
    }

    /** This is a utility function of n2t. It converts a 3-digit number
    * into a textual description. Normally this is not called by itself.
    *
    * @param  int  $number     The 3-digit number to convert (0 - 999)
    * @param  bool $and        True to put the "and" in the string
    * @param  bool $preceding  True if there are preceding members, puts an
    *                          explicit and in (ie 1001 => one thousand AND one)
    * @return The textual description of the number, as a string
    * @package NumberToText
    */
    function n2t_convertthree($number, $and, $preceding)
    {
        $small = unserialize(N2T_SMALL, ['allowed_classes' => false]);
        $medium = unserialize(N2T_MEDIUM, ['allowed_classes' => false]);

        $text = "";

        if ($hundreds = floor($number / 100)) {
            // we have 100's place
            $text .= $small[$hundreds] . " hundred ";
        }

        $tens = $number % 100;
        if ($tens) {
            // we still have values
            if ($and && ($hundreds || $preceding)) {
                $text .= " " . N2T_AND . " ";
            }

            if ($tens < 20) {
                $text .= $small[$tens];
            } else {
                $text .= $medium[floor($tens / 10)];
                if ($ones = $tens % 10) {
                    $text .= "-" . $small[$ones];
                }
            }
        }

        return $text;
    }

    function getmicrotime()
    {
        [$usec, $sec] = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }
}
