<?php

/**
 * This file contains functions to manage some AMC items.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2011-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . "/../../interface/globals.php");
require_once(dirname(__FILE__) . "/../amc.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

//  If all items are valid(ie. not empty) (note object_category and object_id and date_created can be empty), then proceed.
if (
    !(empty($_POST['amc_id'])) &&
     !(empty($_POST['complete'])) &&
     !(empty($_POST['mode'])) &&
     !(empty($_POST['patient_id']))
) {
    processAmcCall($_POST['amc_id'], $_POST['complete'], $_POST['mode'], $_POST['patient_id'], ($_POST['object_category'] ?? null), ($_POST['object_id'] ?? null), ($_POST['date_created'] ?? null));
}
