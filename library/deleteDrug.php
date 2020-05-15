<?php
/**
 *  @package   OpenEMR
 *  @link      http://www.open-emr.org
 *  @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @copyright Copyright (c )2020. Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 *
 */

require_once "../interface/globals.php";

use OpenEMR\Common\Csrf\CsrfUtils;

if (isset($_POST["drugId"])) {

    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $id = filter_input(INPUT_POST, 'drugId', FILTER_VALIDATE_INT);

    $sql = "delete from prescriptions where id = ?";

    sqlQuery($sql, [$id]);

    echo "Done";
}
