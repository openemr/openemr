<?php
 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.
 
	require_once( $GLOBALS['fileroot'] . "/library/options.inc.php" );

    function getLabel( $value, $list_id ) {
        // get from list_options
    	$result = generate_display_field(array('data_type'=>'1','list_id'=>$list_id), $value);
    	if ( $result != '') {
    		return $result;
    	}
    	            
        // if not found, default to the passed-in value
        return $value;
    }

    function getLayoutLabel( $value, $form_id ) {
        // get from layout_options
    	$sql = sqlStatement(
            "SELECT title from layout_options WHERE form_id = ? and field_id = ?", array($form_id, $value)
        );
        if (sqlNumRows($sql) > 0) {
            $result = sqlFetchArray( $sql );
            return xl( $result['title'] );
        }

        // if not found, default to the passed-in value
        return $value;

    }

    function getListOptions( $list_id ) {
        $options = array();
        $sql = sqlStatement(
            "SELECT option_id, title from list_options WHERE list_id = ?", array($list_id)
        );
        for($iter=0; $row=sqlFetchArray( $sql ); $iter++) {
            $options[] = new Option( 
                    out( $row['option_id'] ),            // id
                    out( xl_list_label($row['title']) )  // label
            );
        }
        return $options;
    }

    function getListOptionsArray( $list_id ) {
        $optionsArray = array();
        foreach ( getListOptions($list_id) as $option ) {
            $optionsArray[] = array( "id" => $option->id, "label" => $option->label );
        }
        return $optionsArray;
    }
    
?>
