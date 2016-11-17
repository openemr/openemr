<?php
/** group.inc includes methods for groups */

require_once("{$GLOBALS['srcdir']}/sql.inc");

function getGroupData($search_params, $result_columns, $column){
    $sql = 'SELECT ' . $result_columns . ' FROM therapy_groups WHERE ' . $column . ' LIKE ? ORDER BY group_start_date DESC;';
    $search_params = '%' . $search_params . '%';
    $result = sqlStatement($sql, array($search_params));
    $final_result = array();
    while($row = sqlFetchArray($result)){
        $final_result[] = $row;
    }
    return $final_result;

}

function getGroupStatuses(){
    $sql = 'SELECT  option_id, title FROM list_options WHERE list_id = ?;';
    $result = sqlStatement($sql, array('groupstat'));
    $final_result =array();
    while($row = sqlFetchArray($result)){
        $final_result[] = $row;
    }
    return $final_result;

}


?>