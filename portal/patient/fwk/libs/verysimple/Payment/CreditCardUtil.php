<?php

/** @package    verysimple::Payment */

/**
 * CreditCardUtil is a standard class used to work in general
 * with credit card processing, including clean output.
 *
 * @package verysimple::Payment
 * @author VerySimple Inc.
 * @copyright 1997-2008 VerySimple, Inc.
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 * @version 1.0
 */
class CreditCardUtil
{
    /**
     * Formats the given credit card number with dashes.
     *
     * @param
     *          string credit card number
     * @return string
     */
    static function FormatWithDashes($cc_num)
    {
        $dashSeparatedNumber = "";

        if (strlen($cc_num) == 15) {
            $firstFour = substr($cc_num, 0, 4);
            $secondSix = substr($cc_num, 4, 6);
            $thirdFive = substr($cc_num, 10, 5);
            $dashSeparatedNumber = $firstFour . "-" . $secondSix . "-" . $thirdFive;
        } else {
            $firstFour = substr($cc_num, 0, 4);
            $secondFour = substr($cc_num, 4, 4);
            $thirdFour = substr($cc_num, 8, 4);
            $fourthFour = substr($cc_num, 12, 4);
            $dashSeparatedNumber = $firstFour . "-" . $secondFour . "-" . $thirdFour . "-" . $fourthFour;
        }

        return $dashSeparatedNumber;
    }

    /**
     * Returns a number with all non-numeric characters removed
     *
     * @param unknown_type $num
     */
    static function StripNonNumeric($num)
    {
        return preg_replace('{\D}', '', $num);
    }

    /**
     * Returns true if the card meets a valid mod10 (Luhn Algorithm) check
     *
     * @param
     *          bool
     */
    static function IsValidMod10($str)
    {
        if (strspn($str, "0123456789") != strlen($str)) {
            return false;
        }

        $map = array (
                0,
                1,
                2,
                3,
                4,
                5,
                6,
                7,
                8,
                9, // for even indices
                0,
                2,
                4,
                6,
                8,
                1,
                3,
                5,
                7,
                9
        ); // for odd indices
        $sum = 0;
        $last = strlen($str) - 1;

        for ($i = 0; $i <= $last; $i++) {
            $sum += $map [$str [$last - $i] + ($i & 1) * 10];
        }

        return $sum % 10 == 0;
    }

    /**
     * Returns the Credit Card type based on the card number using the info
     * at http://en.wikipedia.org/wiki/Credit_card_numbers as a reference
     *
     * @return string
     */
    static function GetType($num)
    {
        $firstOne = substr($num, 0, 1);

        if (strlen($num) < 4) {
            return "";
        }

        if ($firstOne == 4) {
            return "Visa";
        }

        $firstTwo = substr($num, 0, 2);
        if ($firstTwo == 34 || $firstTwo == 37) {
            return "AmEx";
        }

        if ($firstTwo >= 51 && $firstTwo <= 55) {
            return "MasterCard";
        }

        if ($firstTwo == 36 || $firstTwo == 38 || $firstTwo == 54 || $firstTwo == 55) {
            return "Diners Club";
        }

        $firstThree = substr($num, 0, 3);
        if ($firstThree >= 300 && $firstThree <= 305) {
            return "Carte Blanche";
        }

        if ($firstThree >= 644 && $firstThree <= 649) {
            return "Discover";
        }

        $firstFour = substr($num, 0, 4);
        if ($firstFour == 6011) {
            return "Discover";
        }

        if ($firstFour == 2014 || $firstFour == 2149) {
            return "enRoute";
        }

        if ($firstFour == 6011) {
            return "Discover";
        }

        $firstSix = substr($num, 0, 6);
        if ($firstSix >= 622126 && $firstSix <= 622925) {
            return "Discover";
        }

        if ($firstOne == 3) {
            return "JCB";
        }

        if ($firstFour == 2131 || $firstFour == 1800) {
            return "JCB";
        }

        return "Other";
    }
}
