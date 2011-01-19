<?php
 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.
?>

<?php
/**
 * Description of RuleCriteriaFactory
 *
 * @author aron
 */
abstract class RuleCriteriaFactory {

    var $strategyMap;
    function __construct() {
        $this->strategyMap[ RuleCriteriaType::ageMin ] = new RuleCriteriaAgeBuilder();
        $this->strategyMap[ RuleCriteriaType::ageMax ] = new RuleCriteriaAgeBuilder();
        $this->strategyMap[ RuleCriteriaType::sex ] = new RuleCriteriaSexBuilder();

        $this->strategyMap[ RuleCriteriaType::issue ] = new RuleCriteriaListsBuilder();
        $this->strategyMap[ RuleCriteriaType::medication ] = new RuleCriteriaListsBuilder();
        $this->strategyMap[ RuleCriteriaType::diagnosis ] = new RuleCriteriaListsBuilder();
        $this->strategyMap[ RuleCriteriaType::surgery ] = new RuleCriteriaListsBuilder();
        $this->strategyMap[ RuleCriteriaType::allergy ] = new RuleCriteriaListsBuilder();

        $this->strategyMap[ RuleCriteriaType::lifestyle ] = new RuleCriteriaDatabaseBuilder();
        $this->strategyMap[ RuleCriteriaType::custom ] = new RuleCriteriaDatabaseBuilder();
        $this->strategyMap[ RuleCriteriaType::custom_bucket ] = new RuleCriteriaDatabaseBuilder();
    }

    function resolveCriteriaType( $method, $methodDetail, $ruleValue) {
        $strategyMap = $this->getStrategyMap();
        $criteriaType = null;
        foreach( $strategyMap as $key=>$value ) {
            $criteriaType = $value->resolveRuleCriteriaType($method, $methodDetail, $ruleValue);
            if ( $criteriaType != null ) {
                return $criteriaType;
            }
        }

        return $criteriaType;
    }
    /**
     *
     * @param RuleCriteria $criteria
     */
    function build($ruleId, $guid, $inclusion, $optional,
            $method, $methodDetail, $value) {
        
        $criteriaType = $this->resolveCriteriaType($method, $methodDetail, $value);
        if ( $criteriaType == null ) {
            // could not resolve a criteria
            return null;
        }

        $builder = $this->getBuilderFor( $criteriaType );
        if ( is_null( $builder ) ) {
            // if no builder, then its an unrecognized critiera
            return null;
        }

        $criteria = $builder->build( $criteriaType, $value, $methodDetail );
        if ( is_null( $criteria ) ) {
            return null;
        }
        $criteria->inclusion = $inclusion;
        $criteria->optional = $optional;
        $criteria->guid = $guid;
        $criteria->criteriaType = $criteriaType;
        $this->modify($criteria, $ruleId);

        return $criteria;
    }

    /**
     *
     * @param string $ruleId
     * @param RuleCriteriaType $criteriaType
     */
    function buildNewInstance($ruleId, $criteriaType) {
        $strategyMap = $this->getStrategyMap();
        $builder = $this->getBuilderFor( $criteriaType );
        if ( is_null( $builder ) ) {
            // if no builder, then its an unrecognized critiera
            return null;
        }

        $criteria = $builder->newInstance( $criteriaType );
        if ( is_null( $criteria ) ) {
            return null;
        }
        $criteria->criteriaType = $criteriaType;

        return $criteria;
    }

    function getStrategyMap() {
        return $this->strategyMap;
    }

    /**
     *
     * @param RuleCriteriaType $criteriaType
     * @return RuleCriteria
     */
    function getBuilderFor( $criteriaType ) {
        $map = $this->getStrategyMap();
        return $map[ $criteriaType->code ];
    }

    abstract function modify($criteria, $ruleId);
}
?>
