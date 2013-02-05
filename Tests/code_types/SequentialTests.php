<?php
$GLOBALS['OE_SITE_DIR']="/var/www/openemr/sites/default";
set_include_path(get_include_path() . PATH_SEPARATOR . "/var/www/openemr");
require_once("library/sql.inc");
require_once("library/htmlspecialchars.inc.php");
require_once("library/translation.inc.php");
require_once("custom/code_types.inc.php");

function seq_search_test($type,$string)
{
    echo "<ol>";
    $res=sequential_code_set_search($type,$string,20);
    foreach($res->GetArray() as $code)
    {
        echo "<li>". $code['code_type_name'].":".$code['code'].":".$code['code_text']."</li>";
    }
    echo "</ol>";
}


seq_search_test("ICD9","hyperchol");
seq_search_test("ICD9","401");


seq_search_test("ICD10","hypert");
seq_search_test("ICD10","I1");

seq_search_test("CPT4","99");

seq_search_test("SNOMED","hypert");
seq_search_test("SNOMED","1201005");


seq_search_test("SNOMED-CT","hypert");
seq_search_test("SNOMED-CT","1201005");


seq_search_test("SNOMED-PR","Incision Drai");



?>
