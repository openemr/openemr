<?php
$GLOBALS['OE_SITE_DIR']="/var/www/openemr/sites/default";
set_include_path(get_include_path() . PATH_SEPARATOR . "/var/www/openemr");
require_once("library/sql.inc");
require_once("library/htmlspecialchars.inc.php");
require_once("library/translation.inc.php");
require_once("custom/code_types.inc.php");

function search_test($type,$string,$mode='default')
{
    echo "<ol>";
    $res=code_set_search($type,$string,false,true,false,0,10,array(),null,$mode);
    foreach($res->GetArray() as $code)
    {
        echo "<li>". $code['code_type_name'].":".$code['code'].":".$code['code_text']."</li>";
    }
    echo "</ol>";
}

?>
<?php
search_test("--ALL--","100");
search_test("--ALL--","100","code");
search_test("--ALL--","colon benign","description");


search_test("CVX","1","code");
search_test("CVX","Hep ped","description");


search_test("ICD9","401");
search_test("ICD9","001","code");
search_test("ICD9","401","code");
search_test("ICD9","essential hyper","description");


search_test("CPT4","99");


search_test("ICD10","I10","code");
search_test("ICD10","Hypertension");
search_test("ICD10","Hypertension","description");

search_test("SNOMED","Hypertension","description");
search_test("SNOMED-CT","Hypertension","description");
search_test("SNOMED-PR","Incision Drai","description");


?>
