<?php
 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.
?>

<?php
require_once( library_src( 'RuleCriteriaBuilder.php') );
/**
 * Description of RuleCriteriaAgeBuilder
 *
 * @author aron
 */
class RuleCriteriaAgeBuilder extends RuleCriteriaBuilder {

    /**
     * @return RuleCriteriaType
     */
    function resolveRuleCriteriaType( $method, $methodDetail, $value ) {
        if (strpos($method, "age_max") ) {
            return RuleCriteriaType::from(RuleCriteriaType::ageMax);
        }
        if (strpos($method, "age_min") ) {
            return RuleCriteriaType::from(RuleCriteriaType::ageMin);
        }
        return null;
    }

    /**
     * @param RuleCriteriaType $ruleCriteriaType
     * @return RuleCriteria
     */
    function build( $ruleCriteriaType, $value, $methodDetail ) {
        $method = $ruleCriteriaType->method;
        $criteria = new RuleCriteriaAge( 
                $method == 'age_max' ? 'max' : 'min', 
                $value, 
                TimeUnit::from($methodDetail) 
        );
        
        $criteria->value = $value;
        return $criteria;
    }

    /**
     *
     * @param RuleCriteriaType $criteriaType
     */
    function newInstance( $criteriaType ) {
        if ( $criteriaType->code == RuleCriteriaType::ageMin ) {
            return new RuleCriteriaAge( 'min' );
        }

        if ( $criteriaType->code == RuleCriteriaType::ageMax ) {
            return new RuleCriteriaAge( 'max' );
        }

        return null;
    }

}
?>
