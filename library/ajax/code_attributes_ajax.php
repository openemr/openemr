<?php

/**
 * Given a code type, code, selector and price level for a service or product, this creates
 * JavaScript that will call the user's handler passing the following arguments:
 * code type, code, description, price, warehouse options.
 * Upload designated service codes as "services=" attributes for designated layouts.
 * This supports specifying related codes to determine the service codes to be used.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2015-2017 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../interface/globals.php");
require_once("$fileroot/custom/code_types.inc.php");
require_once("$fileroot/interface/drugs/drugs.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;

//verify csrf
if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

function write_code_info($codetype, $code, $selector, $pricelevel)
{
    global $code_types;

    $wh = ''; // options for warehouse selection

    if ($codetype == 'PROD') {
        $wrow = sqlQuery(
            "SELECT default_warehouse FROM users WHERE username = ?",
            array($_SESSION['authUser'])
        );
        $defaultwh = empty($wrow['default_warehouse']) ? '' : $wrow['default_warehouse'];
      //
        $crow = sqlQuery(
            "SELECT d.name, p.pr_price " .
            "FROM drugs AS d " .
            "LEFT JOIN prices AS p ON p.pr_id = d.drug_id AND p.pr_selector = ? AND p.pr_level = ? " .
            "WHERE d.drug_id = ?",
            array($selector, $pricelevel, $code)
        );
        $desc = $crow['name'];
        $price = empty($crow['pr_price']) ? 0 : (0 + $crow['pr_price']);
      //
        $lres = sqlStatement("SELECT * FROM list_options " .
        "WHERE list_id = 'warehouse' AND activity = 1 ORDER BY seq, title");
        $wh .= "<option value=''></option>";
        while ($lrow = sqlFetchArray($lres)) {
            $wh .= "<option value='" . attr($lrow['option_id']) . "'";
            $has_inventory = sellDrug($code, 1, 0, 0, 0, 0, '', '', $lrow['option_id'], true);
            if (
                $has_inventory && (
                (strlen($defaultwh) == 0 && $lrow['is_default']           ) ||
                (strlen($defaultwh)  > 0 && $lrow['option_id'] == $default))
            ) {
                $wh .= " selected";
            } else {
              // Disable this warehouse option if not selected and has no inventory.
                if (!$has_inventory) {
                    $wh .= " disabled";
                }
            }
            $wh .= ">" . text(xl_list_label($lrow['title'])) . "</option>";
        }
    } else {
      // not PROD
        $cres = return_code_information($codetype, $code, false);
        $desc = '';
        $price = 0;
        if ($crow = sqlFetchArray($cres)) {
            $desc = trim($crow['code_text']);
            if ($code_types[$codetype]['fee']) {
                if ($code_types[$codetype]['external'] == 0) {
                    $prow = sqlQuery(
                        "SELECT pr_price " .
                        "FROM prices WHERE pr_id = ? AND pr_selector = '' AND pr_level = ? " .
                        "LIMIT 1",
                        array($crow['id'], $pricelevel)
                    );
                    if (!empty($prow['pr_price'])) {
                        $price = 0 + $prow['pr_price'];
                    }
                } else {
                  // external code set with fees, prices table not supported
                    $price = 0 + $crow['fee'];
                }
            }
        }
    }

  // error_log("Warehouse string is: " . $wh); // debugging

    echo "code_attributes_handler(" .
    js_escape($codetype) . "," .
    js_escape($code) . "," .
    js_escape($desc) . "," .
    js_escape($price) . "," .
    js_escape($wh) . ");";
}

$pricelevel = isset($_GET['pricelevel']) ? $_GET['pricelevel'] : '';

if (!empty($_GET['list'])) {
  // This case supports packages of codes.
    $arrcodes = explode('~', $_GET['list']);
    foreach ($arrcodes as $codestring) {
        if ($codestring === '') {
            continue;
        }
        $arrcode = explode('|', $codestring);
        $codetype = $arrcode[0];
        list($code, $modifier) = explode(":", $arrcode[1]);
        $selector = isset($arrcode[2]) ? $arrcode[2] : '';
        write_code_info($codetype, $code, $selector, $pricelevel);
    }
} else {
  // This is the normal case of adding a single code.
    $codetype   = isset($_GET['codetype'  ]) ? $_GET['codetype'  ] : '';
    $code       = isset($_GET['code'      ]) ? $_GET['code'      ] : '';
    $selector   = isset($_GET['selector'  ]) ? $_GET['selector'  ] : '';
    write_code_info($codetype, $code, $selector, $pricelevel);
}
