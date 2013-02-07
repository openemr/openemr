<?php
$GLOBALS['OE_SITE_DIR']="/var/www/openemr/sites/default";
set_include_path(get_include_path() . PATH_SEPARATOR . "/var/www/openemr");
require_once("library/sql.inc");
require_once("library/htmlspecialchars.inc.php");
require_once("library/translation.inc.php");
require_once("custom/code_types.inc.php");

function seq_search_test($type,$string,$limit=20,$modes=NULL,$count=false)
{
    echo "<ol>";
    $res=sequential_code_set_search($type,$string,$limit,$modes,$count);
    if ($count) {
        echo "<li>" . $res . "</li>";
    }
    else {
        while ($code = sqlFetchArray($res))
        {
            echo "<li>". $code['code_type_name'].":".$code['code'].":".$code['code_text']."</li>";
        }
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


seq_search_test("ICD9","401",NULL,array('code','description'),true);
seq_search_test("ICD9","401",NULL,array('description','code'),true);
seq_search_test("ICD9","401",NULL,array('code'),true);
seq_search_test("ICD9","401",NULL,array('description'),true);
seq_search_test("ICD9","chol",NULL,array('code','description'),true);
seq_search_test("ICD9","chol",NULL,array('description','code'),true);
seq_search_test("ICD9","chol",NULL,array('code'),true);
seq_search_test("ICD9","chol",NULL,array('description'),true);

?>
