<?php
 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.
?>

<?php
require_once( src_dir() . "/clinical_rules.php");

class Controller_browse extends BaseController {

    function _action_list() {
        $this->set_view( "list.php" );
    }

    function _action_getrows() {
        $rows = array();

        $rules = resolve_rules_sql();
        foreach( $rules as $rowRule ) {
            $titleResult = sqlFetchArray( sqlStatement(
                    "SELECT title FROM list_options WHERE option_id = ?", array( $rowRule['id'] ) ) );
            $title = $titleResult['title'];
            $type = $rowRule['patient_reminder_flag'] == 1 ? "Reminder" : "CQM/AMC";

            if ( $rowRule['cqm_flag'] == 1  || $rowRule['amc_flag'] == 1 ) {
                // for now, only
                continue;
            }

            $row = array(
                "title" => $title,
                "type"  => $type,
                "id"    => $rowRule['id']
            );
            $rows[] = $row;
        }

        $this->emit_json( $rows );
    }

}
?>
