<?php
 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

/**
 * Description of RuleCriteria
 *
 * @author aron
 */
abstract class RuleCriteria {
    /**
     * if true, then criteria is optional; required otherwise
     * @var boolean
     */
    var $optional;

    /**
     * if true, then criteira is an inclusion; exclusion otherwise
     * @var boolean
     */
    var $inclusion = true;

    /**
     * @var string
     */
    var $interval;

    /**
     * @var TimeUnit
     */
    var $intervalType;

    /**
     * uniquely identifies this criteria
     * @var string
     */
    var $guid;

    /**
     *
     * @var RuleCriteriaType
     */
    var $criteriaType;

    var $groupId;

    function getCharacteristics() {
        $characteristics = $this->optional ? xl ( "Optional" ) : xl ( "Required" );
        $characteristics .= " ";
        $characteristics .= $this->inclusion ? xl( "Inclusion" ) : xl( "Exclusion" );

        return $characteristics;
    }

    abstract function getRequirements();
    
    abstract function getTitle();

    abstract function getView();

    function getInterval() {
        if ( is_null($this->interval) || is_null( $this->intervalType ) ) {
            return null;
        }
        return xl( $this->interval ) . " x " . " "
             . xl( $this->intervalType->lbl );
    }

    protected function getLabel( $value, $list_id ) {
        return getLabel($value, $list_id);
    }

    protected function getLayoutLabel( $value, $form_id ) {
        return getLayoutLabel($value, $form_id);
    }
    
    protected function decodeComparator( $comparator ) {
        switch ( $comparator ) {
            case "eq": return "";
                break;
            case "ne": return "!=";
                break;
            case "gt": return ">";
                break;
            case "lt": return "<";
                break;
            case "ge": return ">=";
                break;
            case "le": return "<=";
                break;
        }
        return "";
    }

    /**
     * @return RuleCriteriaDbView
     */
    function getDbView() {
        $dbView = new RuleCriteriaDbView();
        $dbView->inclusion = $this->inclusion;
        $dbView->optional = $this->optional;
        $dbView->interval = $this->interval;
        $dbView->intervalType = $this->intervalType->code;
    
        return $dbView;
    }

    function updateFromRequest() {
        $inclusion = "yes" ==  _post("fld_inclusion");
        $optional = "yes" == _post("fld_optional");
        $groupId = _post("group_id");
        $interval = _post("fld_target_interval");
        $intervalType = TimeUnit::from( _post("fld_target_interval_type") );

        $this->groupId = $groupId;
        $this->optional = $optional;
        $this->inclusion = $inclusion;
        $this->interval = $interval;
        $this->intervalType = $intervalType;
    }

}
?>
