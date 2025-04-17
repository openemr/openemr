<?php

// ============================================================
// dateformat
//
// return a formated string for date
// @args:   string (date string), boolean (include day of week)
//              (it uses $_SESSION['language_choice'] )
// @return: $date_string (string) - formated string
// Cristian Navalici lemonsoftware at gmail dot com
//
// For Hebrew must be implemented a special calendar functions
//
// 10.07.2007 - dateformat accepts now an argument
// ============================================================

function dateformat($strtime = '', $with_dow = false)
{

// without an argument, display current date
    if (!$strtime) {
        $strtime = strtotime('now');
    }

// string date is formed by
// $dow + date(day) + $nom + date(year) or similar

// name the day of the week for different languages
    $day = date("w", $strtime); // 0 sunday -> 6 saturday

    switch ($day) {
        case 0:
            $dow = xl('Sunday');
            break;
        case 1:
            $dow = xl('Monday');
            break;
        case 2:
            $dow = xl('Tuesday');
            break;
        case 3:
            $dow = xl('Wednesday');
            break;
        case 4:
            $dow = xl('Thursday');
            break;
        case 5:
            $dow = xl('Friday');
            break;
        case 6:
            $dow = xl('Saturday');
            break;
    }

// name of the month in different languages
    $month = (int) date('m', $strtime);

    switch ($month) {
        case 1:
            $nom = xl('January');
            break;
        case 2:
            $nom = xl('February');
            break;
        case 3:
            $nom = xl('March');
            break;
        case 4:
            $nom = xl('April');
            break;
        case 5:
            $nom = xl('May');
            break;
        case 6:
            $nom = xl('June');
            break;
        case 7:
            $nom = xl('July');
            break;
        case 8:
            $nom = xl('August');
            break;
        case 9:
            $nom = xl('September');
            break;
        case 10:
            $nom = xl('October');
            break;
        case 11:
            $nom = xl('November');
            break;
        case 12:
            $nom = xl('December');
            break;
    }

// Date string format
// First, get current language title
    $languageTitle = getLanguageTitle($_SESSION['language_choice']);
    switch ($languageTitle) {
        // standard english first
        case getLanguageTitle(1):
            $dt = date("F j, Y", $strtime);
            if ($with_dow) {
                $dt = "$dow, $dt";
            }
            break;
        case "Swedish":
            $dt = date("Y", $strtime) . " $nom " . date("d", $strtime);
            if ($with_dow) {
                $dt = "$dow $dt";
            }
            break;
        case "Spanish":
        case "Spanish (Spain)":
        case "Spanish (Latin American)":
            $dt = date("d", $strtime) . " $nom " . date("Y", $strtime);
            if ($with_dow) {
                $dt = "$dow $dt";
            }
            break;
        case "German":
            $dt = date("d", $strtime) . " $nom " . date("Y", $strtime);
            if ($with_dow) {
                $dt = "$dow $dt";
            }
            break;
        case "Dutch":
            $dt = date("d", $strtime) . " $nom " . date("Y", $strtime);
            if ($with_dow) {
                $dt = "$dow $dt";
            }
            break;
            // hebrew (israel) , display english NOT jewish calendar
        case "Hebrew":
            $dt = date("d", $strtime) . " $nom " . date("Y", $strtime);
            if ($with_dow) {
                $dt = "$dow, $dt";
            }
            break;
            // default case
        default:
            $dt = "$nom " . date("d", $strtime) . ", " . date("Y", $strtime);
            if ($with_dow) {
                $dt = "$dow, $dt";
            }
    }

    return $dt;
}
