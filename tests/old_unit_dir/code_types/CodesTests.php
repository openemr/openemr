<?php

$GLOBALS['OE_SITE_DIR'] = "/var/www/openemr/sites/default";
set_include_path(get_include_path() . PATH_SEPARATOR . "/var/www/openemr");
require_once("library/sql.inc.php");
require_once("library/htmlspecialchars.inc.php");
require_once("library/translation.inc.php");
require_once("custom/code_types.inc.php");

function search_test($type, $string, $mode = 'default', $return_only_one = false)
{
    echo "<ol>";
    $res = code_set_search($type, $string, false, true, $return_only_one, 0, 10, array(), null, $mode);
    while ($code = sqlFetchArray($res)) {
        echo "<li>" . $code['code_type_name'] . ":" . $code['code'] . ":" . $code['code_text'] . ":" . $code['code_text_short'] . "</li>";
    }
    echo "</ol>";
}

?>
<?php
search_test("SNOMED-CT", "", "description");

search_test("CVX", "1", "code");
search_test("CVX", "Hep ped", "description");


search_test("ICD9", "401");
search_test("ICD9", "001", "code");
search_test("ICD9", "401", "code");
search_test("ICD9", "essential hyper", "description");


search_test("CPT4", "99");


search_test("ICD10", "I10", "code");
search_test("ICD10", "Hypertension");
search_test("ICD10", "Hypertension", "description");

search_test("SNOMED", "Hypertension", "description");
search_test("SNOMED-CT", "Hypertension", "description");
search_test("SNOMED-PR", "Incision Drai", "description");

search_test("SNOMED-PR", "Incision and", "default");

search_test("ICD10", "A01.01", "default", true);
search_test("ICD10", "A01.01", "code", true);
search_test("ICD10", "A01.01", "description", true);
