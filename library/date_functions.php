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
 * @param ?int $timestamp Unix timestamp, defaulting to now
 * @param bool $with_dow Whether to include the day of the week in the output.
 * @return string The formatted date string.
 *
 * @author Cristian Navalici lemonsoftware at gmail dot com
 * @note For Hebrew, displays English calendar, NOT Jewish calendar
 * @note Last modified 10.07.2007 - dateformat accepts now an argument
 */
function dateformat(?int $timestamp = null, bool $with_dow = false): string
{
    $timestamp ??= time();

    // Perf optimization: short-circuit English, see #13497/#13507
    $session = SessionWrapperFactory::getInstance()->getActiveSession();
    $languageChoice = $session->get('language_choice');
    // getLanguageTitle() treats an unset choice as language 1, so mirror that.
    $languageId = is_numeric($languageChoice) ? (int) $languageChoice : 1;
    if ($languageId === 1) {
        $dt = date("F j, Y", $timestamp);
        if ($with_dow) {
            $dow = DayOfWeek::from((int) date('w', $timestamp))->label();
            return "$dow, $dt";
        }
        return $dt;
    }

    // name of the month in different languages
    $month = (int) date('m', $timestamp);
    $nom = Month::from($month)->label();

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
        // name the day of the week for different languages
        $day = (int) date("w", $timestamp); // 0 sunday -> 6 saturday
        $dow = DayOfWeek::from($day)->label();

        $separator = match ($languageTitle) {
            getLanguageTitle(1), "Hebrew" => ", ",
            default => " ",
        };
        $dt = "$dow$separator$dt";
    }

    return $dt;
}
