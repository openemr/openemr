<?php

 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

/**
 * enum
 * @author aron
 */
class ReminderIntervalType
{
    var $code;
    var $lbl;

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
        return $map[$code];
    }

    public static function values()
    {
        $map = self::map();
        return array_values($map);
    }

    private static function map()
    {
        $map = array(
            'clinical'  =>  new ReminderIntervalType('clinical', xl('Clinical')),
            'patient'   =>  new ReminderIntervalType('patient', xl('Patient'))
        );
        return $map;
    }
}
