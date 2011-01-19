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
 * Description of RuleCriteriaDatabaseCustom
 *
 * @author aron
 */
class RuleCriteriaDatabaseCustom extends RuleCriteria {

    var $table;
    var $column;
    var $valueComparator;
    var $value;
    var $frequencyComparator;
    var $frequency;

    function __construct( $table, $column, 
                    $valueComparator, $value,
                    $frequencyComparator, $frequency) {
        $this->table = $table;
        $this->column = $column;
        $this->valueComparator = $valueComparator;
        $this->value = $value;
        $this->frequencyComparator = $frequencyComparator;
        $this->frequency = $frequency;
    }

    function getRequirements() {
        $requirements = "";
        if ( $this->value ) {
            $requirements .= xl( "Value" ) . ": ";
            $requirements .= $this->decodeComparator($this->valueComparator) . " " . $this->value;
            $requirements .= " | ";
        }
        
        $requirements .= xl( "Frequency" ) . ": ";
        $requirements .= $this->decodeComparator($this->frequencyComparator) . " " . $this->frequency;

        return $requirements;
    }

    function getTitle() {
        return xl( $this->table ) . "." . xl( $this->column );
    }

    function getView() {
        return "custom.php";
    }

    function getTableNameOptions() {
        $options = array();
        $stmts = sqlStatement( "SHOW TABLES" );
        for($iter=0; $row=sqlFetchArray($stmts); $iter++) {
            foreach( $row as $key=>$value) {
                array_push( $options, array( "id" => out( $value ), "label" => out( xl ( $value ) ) ) );
            }
        }
        return $options;
    }
    
    function getDbView() {
        $dbView = parent::getDbView();

        $dbView->method = "database";
        $dbView->methodDetail = "";
        $dbView->value =
                "::"
                . $this->table . "::" . $this->column. "::"
                . $this->valueComparator . "::" . $this->value . "::"
                . $this->frequencyComparator . "::" . $this->frequency;
        return $dbView;
    }

    function updateFromRequest() {
        parent::updateFromRequest();

        $this->table = _post("fld_table");
        $this->column = _post("fld_column");
        $this->value = _post("fld_value");
        $this->valueComparator = _post("fld_value_comparator");
        $this->frequency = _post("fld_frequency");
        $this->frequencyComparator = _post("fld_frequency_comparator");
    }
   
}
?>
