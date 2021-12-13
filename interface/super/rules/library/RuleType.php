<?php

 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

/**
 * Enumerates rule types
 * todo turn this into a real enum
 * @author aron
 */
class RuleType
{
    var $code;
    var $lbl;

    const ActiveAlert = "activealert";
    const PassiveAlert = "passivealert";
    const CQM = "cqm";
    const AMC = "amc";
    const PatientReminder = "patientreminder";

    function __construct($code, $lbl)
    {
        $this->lbl = $lbl;
        $this->code = $code;
    }

    /**
     *
     * @param string $value
     * @return RuleType
     */
    public static function from($code)
    {
        $map = self::map();
        return $map[$code];
    }

    public static function values()
    {
        $map = self::map();
        return array_keys($map);
    }

    private static function map()
    {
        $map = array(
            self::ActiveAlert  =>  new RuleType(self::ActiveAlert, xl('Active Alert')),
            self::PassiveAlert   =>  new RuleType(self::PassiveAlert, xl('Passive Alert')),
            // not yet supported
//            self::CQM   =>  new RuleType( self::CQM, xl( 'CQM' ) ),
//            self::AMC   =>  new RuleType( self::AMC, xl( 'AMC' ) ),
            self::PatientReminder   =>  new RuleType(self::PatientReminder, xl('Patient Reminder'))
        );
        return $map;
    }
}
