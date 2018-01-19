<?php
$GLOBALS['OE_SITE_DIR']="/var/www/openemr/sites/default";
set_include_path(get_include_path() . PATH_SEPARATOR . "/var/www/openemr");
require_once("library/sql.inc");
require_once("library/htmlspecialchars.inc.php");
require_once("library/translation.inc.php");
require_once("custom/code_types.inc.php");

function main_search_test($type,$string,$limit=20,$modes=NULL,$count=false,$category=NULL)
{
    echo "<ol>";
    $res=main_code_set_search($type,$string,$limit,$category,true,$modes,$count);
    if ($count) {
        echo "<li>" . $res . "</li>";
    }
    else {
        while ($code = sqlFetchArray($res))
        {
            echo "<li>". $code['code_type_name'].":".$code['code'].":".$code['code_text'].":".$code['code_text_short']."</li>";
        }
    }
    echo "</ol>";
}

function return_code_info_test($type,$string,$limit=20,$modes=NULL,$count=false)
{
    echo "<ol>";
    $res=return_code_information($type,$string);
    while ($code = sqlFetchArray($res))
    {
        echo "<li>". $code['code_type_name'].":".$code['code'].":".$code['code_text'].":".$code['code_text_short']."</li>";
    }
    echo "</ol>";
}

main_search_test("ICD9","hyperchol");
main_search_test("ICD9","401");
main_search_test(array("ICD9"),"401");

main_search_test("ICD10","hypert");
main_search_test("ICD10","I1");
main_search_test(array("ICD10"),"I1");

main_search_test("CPT4","99");

main_search_test("SNOMED","hypert");
main_search_test("SNOMED","1201005");

main_search_test("SNOMED-CT","hypert");
main_search_test("SNOMED-CT","1201005");

main_search_test("SNOMED-PR","Incision Drai");

main_search_test(array("ICD9","CVX","ICD10"),"100");
main_search_test(array("ICD9","CVX"),"100");
main_search_test(array("CVX","ICD10"),"100");

main_search_test(array("ICD9","ICD10","SNOMED"),"colon benign");
main_search_test(array("ICD9","SNOMED"),"colon benign");

main_search_test("","polio",40,NULL,false,"active");
main_search_test("","polio",40,NULL,false,"diagnosis");
main_search_test("","polio",40,NULL,false,"procedure");
main_search_test("","polio",40,NULL,false,"clinical_term");

main_search_test("ICD9","401",NULL,array('code','description'),true);
main_search_test("ICD9","401",NULL,array('description','code'),true);
main_search_test("ICD9","401",NULL,array('code'),true);
main_search_test("ICD9","401",NULL,array('description'),true);
main_search_test("ICD9","chol",NULL,array('code','description'),true);
main_search_test("ICD9","chol",NULL,array('description','code'),true);
main_search_test("ICD9","chol",NULL,array('code'),true);
main_search_test("ICD9","chol",NULL,array('description'),true);

return_code_info_test("ICD9","045.10");
return_code_info_test("CVX","2");
return_code_info_test("ICD10","A80.2");
return_code_info_test("SNOMED","172672006");
return_code_info_test("SNOMED-CT","14535005");
return_code_info_test("SNOMED-PR","170420002");

?>
