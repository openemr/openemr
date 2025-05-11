<?php

/**
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once "../../../../globals.php";

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Modules\Dorn\LabCompendiumInstall;

if (!empty($_GET)) {
    if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
    $labGuid = $_REQUEST['labGuid'];
    echo "<div style='background-color: var(--light); color: var(--dark)'>" .
    "<div>" . xlt('Compendium Install') . "</div><ul>";
    LabCompendiumInstall::uninstall($labGuid);
    LabCompendiumInstall::install($labGuid);
    echo "</ul><div>" . xlt('Compendium Install Complete') . "</div></div>";
}
