<?php



/*Create page validation array -- all the pages that have to fire validatejs and their rules
* @param $title
* @return array
*/
function collectValidationActivePageRules($title){

    $sql = sqlStatement("SELECT * " .
        "FROM `list_options` WHERE list_id=? AND activity=?  AND title=?",array('page_validation',1,$title));

    return fetchData($sql);


}

/**get all the validation on the page
 * @param $title
 * @return array
 */
function collectValidationPageRules($title){

    $sql = sqlStatement("SELECT * " .
        "FROM `list_options` WHERE list_id=? AND title=?",array('page_validation',$title));
    return fetchData($sql);


}

/**fetch the array out of the statement
 * @param $sql
 * @return array
 */
function fetchData($sql){

    $dataArray=array();
    while($row = sqlFetchArray($sql) ) {
        $formPageNameArray = explode('#', $row['option_id']);
        $dataArray[$formPageNameArray[1]]=array('page_name' => $formPageNameArray[0] . ".php",'rules' => $row['notes']);
    }
    return $dataArray;


}

