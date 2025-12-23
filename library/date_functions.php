<?php

/**
 * Format a date string according to the user's language preference.
 *
 * Returns a formatted date string based on the user's language choice stored in
 * $_SESSION['language_choice']. The format varies by language and can optionally
 * include the day of the week.
 *
 * @param string|int $strtime Unix timestamp or date string. If empty, uses current time.
 * @param bool $with_dow Whether to include the day of the week in the output.
 * @return string The formatted date string.
 *
 * @author Cristian Navalici lemonsoftware at gmail dot com
 * @note For Hebrew, displays English calendar, NOT Jewish calendar
 * @note Last modified 10.07.2007 - dateformat accepts now an argument
 */
function dateformat(string|int $strtime = '', bool $with_dow = false): string
{
    // without an argument, display current date
    if (!$strtime) {
        $strtime = strtotime('now');
    }

    // name the day of the week for different languages
    $day = (int) date("w", $strtime); // 0 sunday -> 6 saturday

    $days = [
        xl('Sunday'),
        xl('Monday'),
        xl('Tuesday'),
        xl('Wednesday'),
        xl('Thursday'),
        xl('Friday'),
        xl('Saturday'),
    ];
    $dow = $days[$day];

    // name of the month in different languages
    $month = (int) date('m', $strtime);

    $months = [
        1 => xl('January'),
        2 => xl('February'),
        3 => xl('March'),
        4 => xl('April'),
        5 => xl('May'),
        6 => xl('June'),
        7 => xl('July'),
        8 => xl('August'),
        9 => xl('September'),
        10 => xl('October'),
        11 => xl('November'),
        12 => xl('December'),
    ];
    $nom = $months[$month];

    // Date string format
    // First, get current language title
    $languageTitle = getLanguageTitle($_SESSION['language_choice']);
    $dt = match ($languageTitle) {
        // standard english first
        getLanguageTitle(1) => date("F j, Y", $strtime),
        "Swedish" => date("Y", $strtime) . " $nom " . date("d", $strtime),
        "Spanish", "Spanish (Spain)", "Spanish (Latin American)" => date("d", $strtime) . " $nom " . date("Y", $strtime),
        "German" => date("d", $strtime) . " $nom " . date("Y", $strtime),
        "Dutch" => date("d", $strtime) . " $nom " . date("Y", $strtime),
        "Hebrew" => date("d", $strtime) . " $nom " . date("Y", $strtime), // display english NOT jewish calendar
        default => "$nom " . date("d", $strtime) . ", " . date("Y", $strtime),
    };

    if ($with_dow) {
        $separator = match ($languageTitle) {
            getLanguageTitle(1), "Hebrew" => ", ",
            default => " ",
        };
        $dt = "$dow$separator$dt";
    }

    return $dt;
}
