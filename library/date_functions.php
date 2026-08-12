<?php

use OpenEMR\Common\Calendar\DayOfWeek;
use OpenEMR\Common\Calendar\Month;
use OpenEMR\Common\Session\SessionWrapperFactory;

/**
 * Format a date string according to the user's language preference.
 *
 * Returns a formatted date string based on the user's language choice stored in
 * the session's 'language_choice' value. The format varies by language and can optionally
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

    // Callers pass the timestamp as either an int or a numeric string; normalise
    // once so the date() calls below receive the int they declare.
    $timestamp = (int) $strtime;

    // name the day of the week for different languages
    $day = (int) date("w", $timestamp); // 0 sunday -> 6 saturday
    $dow = DayOfWeek::from($day)->label();

    // name of the month in different languages
    $month = (int) date('m', $timestamp);
    $nom = Month::from($month)->label();

    $session = SessionWrapperFactory::getInstance()->getActiveSession();
    $languageChoice = $session->get('language_choice');
    // getLanguageTitle() treats an unset choice as language 1, so mirror that.
    $languageId = is_numeric($languageChoice) ? (int) $languageChoice : 1;

    // English is both the default and the common case, and reaching the English
    // arms below costs three lang_languages queries per formatted date -- two of
    // them just to discover that language 1 is called "English". Short-circuit it.
    if ($languageId === 1) {
        $dt = date("F j, Y", $timestamp);

        return $with_dow ? "$dow, $dt" : $dt;
    }

    $day_num = date("d", $timestamp);
    $year = date("Y", $timestamp);

    // Date string format
    // First, get current language title
    $languageTitle = getLanguageTitle($languageId);
    $dt = match ($languageTitle) {
        // standard english first
        getLanguageTitle(1) => date("F j, Y", $timestamp),
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
