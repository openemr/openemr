<?php

 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

/**
 * todo docs
 * enum
 * @author aron
 */
class TimeUnit
{
    var $code;
    var $lbl;

    const Week = "week";
    const Month = "month";
    const Year = "year";

    function __construct($code, $lbl)
    {
        $this->lbl = $lbl;
        $this->code = $code;
    }

    /**
     *
     * @param string $value
     * @return ReminderIntervalType
     */
    public static function from($code)
    {
        $map = self::map();
        return $map[$code] ?? null;
    }

    public static function values()
    {
        $map = self::map();
        return array_values($map);
    }

    private static function map()
    {
        $map = array(
            'minute'        =>  new TimeUnit('minute', xl('Minutes')),
            'hour'          =>  new TimeUnit('hour', xl('Hours')),
            'day'           =>  new TimeUnit('day', xl('Days')),
            'week'          =>  new TimeUnit('week', xl('Weeks')),
            'month'         =>  new TimeUnit('month', xl('Months')),
            'year'          =>  new TimeUnit('year', xl('Years')),
            'flu_season'    =>  new TimeUnit('flu_season', xl('Flu season'))
        );
        return $map;
    }
}
