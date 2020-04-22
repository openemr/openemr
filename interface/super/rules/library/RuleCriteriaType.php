<?php

 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

/**
 * Description of RuleCriteriaType
 *
 * @author aron
 */
class RuleCriteriaType
{

    // codes
    const ageMin = "age_min";
    const ageMax = "age_max";
    const sex = "sex";
    const diagnosis = "diagnosis";
    const issue = "issue";
    const medication = "medication";
    const allergy = "allergy";
    const surgery = "surgery";
    const lifestyle = "lifestyle";
    const custom = "custom";
    const custom_bucket = "custom_bucket";

    var $code;
    var $lbl;
    var $method;

    function __construct($code, $lbl, $method)
    {
        $this->lbl = $lbl;
        $this->code = $code;
        $this->method = $method;
    }

    /**
     *
     * @param string $value
     * @return RuleCriteriaType
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
            self::ageMin   =>  new RuleCriteriaType(self::ageMin, xl('Age min'), 'age_min'),
            self::ageMax   =>  new RuleCriteriaType(self::ageMax, xl('Age max'), 'age_max'),
            self::sex       =>  new RuleCriteriaType(self::sex, xl('Sex'), 'sex'),

            self::issue     =>  new RuleCriteriaType(self::issue, xl('Medical issue'), 'lists'),
            self::diagnosis =>  new RuleCriteriaType(self::diagnosis, xl('Diagnosis'), 'lists'),
            self::medication =>  new RuleCriteriaType(self::medication, xl('Medication'), 'lists'),
            self::allergy   =>  new RuleCriteriaType(self::allergy, xl('Allergy'), 'lists'),
            self::surgery   =>  new RuleCriteriaType(self::surgery, xl('Surgery'), 'lists'),

            self::lifestyle =>  new RuleCriteriaType(self::lifestyle, xl('Lifestyle'), 'database'),
            self::custom    =>  new RuleCriteriaType(self::custom, xl('Custom Table'), 'database'),
            self::custom_bucket  =>  new RuleCriteriaType(self::custom_bucket, xl('Custom'), 'database')
        );
        return $map;
    }
}
