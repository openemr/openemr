<?php

 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

/**
 * Description of RuleCriteriaSexBuilder
 *
 * @author aron
 */
class RuleCriteriaSexBuilder extends RuleCriteriaBuilder
{

    /**
     * @return RuleCriteriaType
     */
    function resolveRuleCriteriaType($method, $methodDetail, $value)
    {
        if (strpos($method, "sex")) {
            return RuleCriteriaType::from(RuleCriteriaType::sex);
        }

        return null;
    }

    /**
     * @param RuleCriteriaType $ruleCriteriaType
     * @return RuleCriteria
     */
    function build($ruleCriteriaType, $value, $methodDetail)
    {
        return new RuleCriteriaSex($value);
    }

    /**
     *
     * @param RuleCriteriaType $criteriaType
     */
    function newInstance($criteriaType)
    {
        return new RuleCriteriaSex('Male');
    }
}
