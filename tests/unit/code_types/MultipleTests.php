<?php
$GLOBALS['OE_SITE_DIR']="/var/www/openemr/sites/default";
set_include_path(get_include_path() . PATH_SEPARATOR . "/var/www/openemr");
require_once("library/sql.inc");
require_once("library/htmlspecialchars.inc.php");
require_once("library/translation.inc.php");
require_once("custom/code_types.inc.php");

function mult_search_test($type,$string,$limit=20,$modes=NULL,$count=false)
{
    echo "<ol>";
    $res=multiple_code_set_search($type,$string,$limit,$modes,$count);
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


mult_search_test(array("ICD9"),"hyperchol");
mult_search_test(array("ICD9"),"401");

mult_search_test(array("ICD10"),"hypert");
mult_search_test(array("ICD10"),"I1");

mult_search_test(array("CPT4"),"99");

mult_search_test(array("SNOMED"),"hypert");
mult_search_test(array("SNOMED"),"1201005");

mult_search_test(array("SNOMED-CT"),"hypert");
mult_search_test(array("SNOMED-CT"),"1201005");

mult_search_test(array("SNOMED-PR"),"Incision Drai");

mult_search_test(array(),"100");
mult_search_test(array("ICD9","CVX"),"100");
mult_search_test(array("CVX","ICD10"),"100");

mult_search_test(array(),"colon benign");
mult_search_test(array("ICD9","SNOMED"),"colon benign");

mult_search_test(array("ICD9"),"401",NULL,array('code','description'),true);
mult_search_test(array("ICD9"),"401",NULL,array('description','code'),true);
mult_search_test(array("ICD9"),"401",NULL,array('code'),true);
mult_search_test(array("ICD9"),"401",NULL,array('description'),true);
mult_search_test(array("ICD9"),"chol",NULL,array('code','description'),true);
mult_search_test(array("ICD9"),"chol",NULL,array('description','code'),true);
mult_search_test(array("ICD9"),"chol",NULL,array('code'),true);
mult_search_test(array("ICD9"),"chol",NULL,array('description'),true);

?>
