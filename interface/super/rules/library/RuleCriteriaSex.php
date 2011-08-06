<?php
 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

/**
 * Description of RuleCriteriaSex
 *
 * @author aron
 */
class RuleCriteriaSex extends RuleCriteria {
    
    var $value;

    function __construct( $value ) {
        $this->value = $value;
    }

    function getRequirements() {
        return xl_list_label( $this->value, ENT_NOQUOTES );
    }

    function getTitle() {
        return xl( "Sex" );
    }

    function getView() {
        return "sex.php";
    }

    function getOptions() {
        return getListOptionsArray( 'sex' );
    }

    function getDbView() {
        $dbView = parent::getDbView();

        $dbView->method = "sex";
        $dbView->methodDetail = "";
        $dbView->value = $this->value;
        return $dbView;
    }

    function updateFromRequest() {
        parent::updateFromRequest();

        $sex = _post("fld_sex");
        $this->value = $sex;
    }

}
?>
