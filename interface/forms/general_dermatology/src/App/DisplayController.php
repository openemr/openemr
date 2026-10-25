<?php

namespace GeneralDermatology\App;

class DisplayController
{
    public static function getDays()
    {
        $days = [];
        for ($i = 1; $i <= 30; $i++) {
            $days[] = $i;
        }
        return $days;
    }

    public static function getWeeks()
    {
        $weeks = [];
        for ($i = 1; $i <= 10; $i++) {
            $weeks[] = $i;
        }
        return $weeks;
    }

    public static function getMonths()
    {
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[] = $i;
        }
        return $months;
    }

    public static function getYears()
    {
        $years = [];
        for ($i = 1; $i <= 12; $i++) {
            $years[] = $i;
        }
        return $years;
    }
}