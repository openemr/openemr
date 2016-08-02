<?php

//Create page validation array -- all the pages that have to fire validatejs and their rules
function collectValidationPageRules($option_id,$title){



    $pages = sqlStatement("SELECT * " .
        "FROM `list_options` WHERE list_id='page_validation' AND option_id=?  AND title=?",array($option_id,$title));

    $row = sqlFetchArray($pages);
    if($row) {
        return array('page_name' => $row['option_id'] . ".php", 'rules' => $row['notes']);
    }
    else{
        return null;
    }



}

