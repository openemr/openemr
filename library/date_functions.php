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

    static $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    $dow = xl($days[$day]);

    // name of the month in different languages
    $month = (int) date('m', $strtime);

    static $months = [
        1 => 'January',
        2 => 'February',
        3 => 'March',
        4 => 'April',
        5 => 'May',
        6 => 'June',
        7 => 'July',
        8 => 'August',
        9 => 'September',
        10 => 'October',
        11 => 'November',
        12 => 'December',
    ];
    $nom = xl($months[$month]);

    // Date string format
    // First, get current language title
    $languageTitle = getLanguageTitle($_SESSION['language_choice']);
    $day_num = date("d", $strtime);
    $year = date("Y", $strtime);
    $dt = match ($languageTitle) {
        // standard english first
        getLanguageTitle(1) => date("F j, Y", $strtime),
        "Swedish" => "$year $nom $day_num",
        "Dutch",
        "German",
        "Hebrew",
        "Spanish",
        "Spanish (Latin American)",
        "Spanish (Spain)" => "$day_num $nom $year",
        default => "$nom $day_num, $year",
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
