<?php

/**
 * CAMOS save.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Mark Leeds <drleeds@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2006-2009 Mark Leeds <drleeds@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once("../../../library/api.inc.php");
require_once("../../../library/forms.inc.php");
require_once("./content_parser.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;

if (filter_input(INPUT_GET, 'mode') === "delete") {
    $session = SessionWrapperFactory::getInstance()->getActiveSession();
    if (!CsrfUtils::verifyCsrfToken(filter_input(INPUT_POST, 'csrf_token_form') ?: '', session: $session)) {
        CsrfUtils::csrfNotVerified();
    }

    // Cache escaped table name to avoid repeated SHOW TABLES lookups.
    // escape_table_name() on a literal handles case-insensitive matching
    // on MySQL installs where the actual table case differs from the code.
    $tbl_camos = escape_table_name("form_CAMOS");
    $isDelete = (bool) filter_input(INPUT_POST, 'delete');
    $isUpdate = (bool) filter_input(INPUT_POST, 'update');
    $pid = $session->get('pid');
    $postData = filter_input_array(INPUT_POST) ?: [];
    foreach ($postData as $key => $val) {
        if (!(str_starts_with((string) $key, 'ch_') and $val === 'on')) {
            continue;
        }
        $id = filter_var(substr((string) $key, 3), FILTER_VALIDATE_INT);
        if ($id === false) {
            continue;
        }
        if ($isDelete) {
            QueryUtils::sqlStatementThrowException("DELETE FROM " . $tbl_camos . " WHERE id = ? AND pid = ?", [$id, $pid]);
            QueryUtils::sqlStatementThrowException("DELETE FROM forms WHERE form_name LIKE 'CAMOS%' AND form_id = ? AND pid = ?", [$id, $pid]);
        }

        if ($isUpdate) {
            // Replace the placeholders before saving the form. This was changed in version 4.0. Previous to this, placeholders
            //   were submitted into the database and converted when viewing. All new notes will now have placeholders converted
            //   before being submitted to the database. Will also continue to support placeholder conversion on report
            //   views to support notes within database that still contain placeholders (ie. notes that were created previous to
            //   version 4.0).
            $content = filter_input(INPUT_POST, 'textarea_' . $id) ?: '';
            $rawPid = $session->get('pid');
            $rawEnc = $session->get('encounter');
            $content = replace(
                is_numeric($rawPid) ? (int) $rawPid : 0,
                is_numeric($rawEnc) ? (int) $rawEnc : 0,
                $content,
            );
            QueryUtils::sqlStatementThrowException("UPDATE " . $tbl_camos . " SET content = ? WHERE id = ? AND pid = ?", [$content, $id, $pid]);
        }
    }
}

formHeader("Redirecting....");
formJump();
formFooter();
