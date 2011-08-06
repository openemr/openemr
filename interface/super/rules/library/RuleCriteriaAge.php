<?php
 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

/**
 * Description of RuleCriteriaAge
 *
 * @author aron
 */
class RuleCriteriaAge extends RuleCriteria {
    var $type;
    var $value;
    var $timeUnit;

    /**
     *
     * @param TimeUnit $timeUnit
     */
    function __construct( $type, $value = null, $timeUnit = null) {
        $this->type = $type;
        $this->value = $value;
        $this->timeUnit = $timeUnit;
    }

    function getRequirements() {
        return $this->value;
    }

    function getTitle() {
        $title = xl( "Age" );
        if ( $this->type == "min" ) {
            $title .= " " . xl( "Min" );
        } else {
            $title .= " " . xl( "Max" );
        }

        $title .= " (" . $this->timeUnit->lbl . ")";
        return $title;
    }

    function getType() {
        if ( $this->type == "min" ) {
            return xl( "Min" );
        } else {
            return xl( "Max" );
        }
    }

    function getView() {
        return "age.php";
    }

    function getDbView() {
        $dbView = parent::getDbView();

        $dbView->method = "age_" . $this->type;
        $dbView->methodDetail = $this->timeUnit->code;
        $dbView->value = $this->value;
        return $dbView;
    }

    function updateFromRequest() {
        parent::updateFromRequest();
        $age = _post("fld_value");
        $timeUnit = TimeUnit::from( _post("fld_timeunit") );
        if ( $timeUnit == null ) {
            $timeUnit = TimeUnit::from( _post("fld_target_interval_type") );
        }

        $this->value = $age;
        $this->timeUnit = $timeUnit;
    }
}
?>
