<?php

/**
 * CAMOS save.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Mark Leeds <drleeds@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2006-2009 Mark Leeds <drleeds@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once("../../../library/api.inc");
require_once("../../../library/forms.inc");
require_once("./content_parser.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if ($_GET["mode"] == "delete") {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    foreach ($_POST as $key => $val) {
        if (substr($key, 0, 3) == 'ch_' and $val = 'on') {
            $id = substr($key, 3);
            if ($_POST['delete']) {
                sqlStatement("delete from " . mitigateSqlTableUpperCase("form_CAMOS") . " where id=?", array($id));
                sqlStatement("delete from forms where form_name like 'CAMOS%' and form_id=?", array($id));
            }

            if ($_POST['update']) {
                // Replace the placeholders before saving the form. This was changed in version 4.0. Previous to this, placeholders
                //   were submitted into the database and converted when viewing. All new notes will now have placeholders converted
                //   before being submitted to the database. Will also continue to support placeholder conversion on report
                //   views to support notes within database that still contain placeholders (ie. notes that were created previous to
                //   version 4.0).
                $content = $_POST['textarea_' . $id];
                $content = replace($pid, $encounter, $content);
                sqlStatement("update " . mitigateSqlTableUpperCase("form_CAMOS") . " set content=? where id=?", array($content, $id));
            }
        }
    }
}

formHeader("Redirecting....");
formJump();
formFooter();
