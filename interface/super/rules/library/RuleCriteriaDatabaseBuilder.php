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
 * Description of RuleCriteriaDatabaseBuilder
 *
 * @author aron
 */
class RuleCriteriaDatabaseBuilder extends RuleCriteriaBuilder {

    function __construct() {
    }

    /**
     * @return RuleCriteriaType
     */
    function resolveRuleCriteriaType( $method, $methodDetail, $value ) {
        if (strpos($method, "database") ) {
            $exploded = explode("::", $value);
            if ( $exploded[0] == "LIFESTYLE" ) {
                return RuleCriteriaType::from(RuleCriteriaType::lifestyle);
            }

            if ( $exploded[0] == 'CUSTOM' ) {
                return RuleCriteriaType::from(RuleCriteriaType::custom_bucket);
            } else {
                return RuleCriteriaType::from(RuleCriteriaType::custom);
            }
        }

        return null;
    }

    /**
     * @param RuleCriteriaType $ruleCriteriaType
     * @return RuleCriteria
     */
    function build( $ruleCriteriaType, $value, $methodDetail ) {
        $exploded = explode("::", $value);

        if ( $ruleCriteriaType->code == RuleCriteriaType::lifestyle ) {
            $type = $exploded[1];
            return new RuleCriteriaLifestyle( $type, sizeof( $exploded ) > 2 ? $exploded[2] : null );
        }

        if ( $ruleCriteriaType->code == RuleCriteriaType::custom_bucket ) {
            $category = $exploded[1];
            $item = $exploded[2];
            $completed = $exploded[3] == "YES";
            $frequencyComparator = $exploded[4];
            $frequency = $exploded[5];
            return new RuleCriteriaDatabaseBucket( $category, $item, $completed,
                    $frequencyComparator, $frequency );
        }

        if ( $ruleCriteriaType->code == RuleCriteriaType::custom ) {
            $table = $exploded[1];
            $column = $exploded[2];
            $valueComparator = $exploded[3];
            $value = $exploded[4];
            $frequencyComparator = $exploded[5];
            $frequency = $exploded[6];
            return new RuleCriteriaDatabaseCustom( $table, $column, 
                    $valueComparator, $value,
                    $frequencyComparator, $frequency );
        }

        return null;
    }

    /**
     *
     * @param RuleCriteriaType $ruleCriteriaType
     */
    function newInstance( $ruleCriteriaType ) {
        if ( $ruleCriteriaType->code == RuleCriteriaType::lifestyle ) {
            return new RuleCriteriaLifestyle( null, null );
        }

        if ( $ruleCriteriaType->code == RuleCriteriaType::custom_bucket ) {
            return new RuleCriteriaDatabaseBucket( "", "", true, "", "" );
        }

        if ( $ruleCriteriaType->code == RuleCriteriaType::custom ) {
            $table = "";
            $column = "";
            $valueComparator = "";
            $value = "";
            $frequencyComparator = "";
            $frequency = "";
            return new RuleCriteriaDatabaseCustom( "", "", "", "", "", "");
        }

        return null;
    }

}
?>
