<?php

/**
 * Given a list ID, name of a target form field and a default value, this creates
 * JavaScript that will write Option values into the target selection list.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2014-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;

//verify csrf
if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$listid  = $_GET['listid'];
$target  = $_GET['target'];
$current = $_GET['current'];

$res = sqlStatement("SELECT option_id FROM list_options WHERE list_id = ? AND activity = 1 " .
  "ORDER BY seq, option_id", array($listid));

echo "var itemsel = document.forms[0][" . js_escape($target) . "];\n";
echo "var j = 0;\n";
echo "itemsel.options[j++] = new Option(" . js_escape("-- " . xl('Please Select') . " --") . ",'',false,false);\n";
while ($row = sqlFetchArray($res)) {
    $tmp = js_escape($row['option_id']);
    $def = $row['option_id'] == $current ? 'true' : 'false';
    echo "itemsel.options[j++] = new Option($tmp,$tmp,$def,$def);\n";
}
