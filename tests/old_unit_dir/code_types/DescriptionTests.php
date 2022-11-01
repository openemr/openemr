<?php

$GLOBALS['OE_SITE_DIR'] = "/var/www/openemr/sites/default";
set_include_path(get_include_path() . PATH_SEPARATOR . "/var/www/openemr");
require_once("library/sql.inc.php");
require_once("library/htmlspecialchars.inc.php");
require_once("library/translation.inc.php");
require_once("custom/code_types.inc.php");

function description_test($codes, $mode)
{
    $descriptions = lookup_code_descriptions($codes, $mode);
    echo $descriptions . "    <br />" . PHP_EOL;
}
echo PHP_EOL;
description_test("ICD9:401.1");
description_test("ICD9:401.1", "code_text_short");

description_test("CVX:1");
description_test("CVX:1", "code_text_short");

description_test("ICD10:I10");
description_test("ICD10:I10", "code_text_short");

description_test("SNOMED:1201005");
description_test("SNOMED:1201005", "code_text_short");

description_test("SNOMED-CT:1201005");
description_test("SNOMED-CT:1201005", "code_text_short");

description_test("SNOMED-PR:285008");
description_test("SNOMED-PR:285008", "code_text_short");

description_test("ICD9:401.1;CVX:1;ICD10:I10;SNOMED:1201005;SNOMED-CT:1201005;SNOMED-PR:285008");
description_test("ICD9:401.1;CVX:1;ICD10:I10;SNOMED:1201005;SNOMED-CT:1201005;SNOMED-PR:285008", "code_text_short");
