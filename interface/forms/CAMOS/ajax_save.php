<?php

/**
 * CAMOS ajax_save.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Mark Leeds <drleeds@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (C) 2006-2009 Mark Leeds <drleeds@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once("../../../library/api.inc.php");
require_once("content_parser.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Services\FormService;

$session = SessionWrapperFactory::getInstance()->getActiveSession();
if (!CsrfUtils::verifyCsrfToken(filter_input(INPUT_POST, 'csrf_token_form') ?? '', session: $session)) {
    CsrfUtils::csrfNotVerified();
}

/** @var array{category: string, subcategory: string, item: string, content: string} $field_names */
$field_names = ['category' => filter_input(INPUT_POST, 'category') ?? '', 'subcategory' => filter_input(INPUT_POST, 'subcategory') ?? '', 'item' => filter_input(INPUT_POST, 'item') ?? '', 'content' => filter_input(INPUT_POST, 'content') ?? ''];
$camos_array = [];
process_commands($field_names['content'], $camos_array);

$CAMOS_form_name = "CAMOS-" . $field_names['category'] . '-' . $field_names['subcategory'] . '-' . $field_names['item'];

$rawPid = $session->get('pid');
$pid = is_numeric($rawPid) ? (int) $rawPid : 0;
if ($pid <= 0) {
    die(xlt('Patient context required'));
}
$rawEncounter = $session->get('encounter');
$encounter = is_numeric($rawEncounter) ? (int) $rawEncounter : 0;
$userauthorized = $session->get('userauthorized');

if ($encounter === 0) {
    $encounter = (int) date("Ymd");
}

if (preg_match("/^[\s\\r\\n\\\\r\\\\n]*$/", $field_names['content']) == 0) { //make sure blanks do not get submitted
  // Replace the placeholders before saving the form. This was changed in version 4.0. Previous to this, placeholders
  //   were submitted into the database and converted when viewing. All new notes will now have placeholders converted
  //   before being submitted to the database. Will also continue to support placeholder conversion on report
  //   views to support notes within database that still contain placeholders (ie. notes that were created previous to
  //   version 4.0).
    $field_names['content'] = replace($pid, $encounter, $field_names['content']);
    $newid = formSubmit("form_CAMOS", $field_names, filter_input(INPUT_GET, 'id') ?? '', $userauthorized);
    (new FormService())->addForm($encounter, $CAMOS_form_name, $newid, "CAMOS", $pid, $userauthorized);
}

//deal with embedded camos submissions here
foreach ($camos_array as $val) {
    if (preg_match("/^[\s\\r\\n\\\\r\\\\n]*$/", $val['content']) == 0) { //make sure blanks not submitted
        foreach ($val as $k => $v) {
            // Replace the placeholders before saving the form. This was changed in version 4.0. Previous to this, placeholders
            //   were submitted into the database and converted when viewing. All new notes will now have placeholders converted
            //   before being submitted to the database. Will also continue to support placeholder conversion on report
            //   views to support notes within database that still contain placeholders (ie. notes that were created previous to
            //   version 4.0).
            $val[$k] = trim(replace($pid, $encounter, $v));
        }

        $CAMOS_form_name = "CAMOS-" . $val['category'] . '-' . $val['subcategory'] . '-' . $val['item'];
        $newid = formSubmit("form_CAMOS", $val, filter_input(INPUT_GET, 'id') ?? '', $userauthorized);
        (new FormService())->addForm($encounter, $CAMOS_form_name, $newid, "CAMOS", $pid, $userauthorized);
    }
}

echo "<font color=red><b>" . xlt('submitted') . ": " . text(strval(time())) . "</b></font>";
