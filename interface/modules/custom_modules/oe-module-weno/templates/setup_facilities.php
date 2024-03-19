<?php

/**
 * @package   OpenEMR Modules setup_facilities
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\WenoModule\Services;

require_once(dirname(__DIR__, 4) . "/globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

//ensure user has proper access
if (!AclMain::aclCheckCore('admin', 'super')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Weno Admin")]);
    exit;
}

if ($_POST) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token"])) {
        CsrfUtils::csrfNotVerified();
    }
    foreach ($_POST as $location) {
        sqlQuery("update facility set weno_id = ? where id = ?", [$location[1], $location[0]]);
    }
}

$list = sqlStatement("SELECT id, name, street, city, weno_id FROM facility");
$facilities = [];
while ($row = sqlFetchArray($list)) {
    $facilities[] = $row;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('Facility IDs'); ?></title>
    <?php Header::setupHeader(); ?>
</head>
<body class="body_top">
    <div>
        <div class="container-fluid" id="facility">
            <form name="wenofacilityinfo" method="post" action="setup_facilities.php" onsubmit="return top.restoreSession()">
                <input type="hidden" name="csrf_token" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>">
                <table class="table">
                    <thead>
                    <tr>
                        <th></th>
                        <th><?php print xlt('Facility Name'); ?></th>
                        <th><?php print xlt('Address'); ?></th>
                        <th><?php print xlt('City'); ?></th>
                        <th><?php print xlt('Weno ID'); ?></th>
                    </tr>
                    </thead>
                    <?php
                    $i = 0;
                    foreach ($facilities as $facility) {
                        print "<tr>";
                        print "<td><input type='hidden' name='location" . $i . "[]' value='" . attr($facility['id']) . "'></td>";
                        print "<td>" . text($facility["name"]) . "</td><td>" . text($facility['street'])
                            . "</td><td>" . text($facility['city']) . "</td><td><input type='text' id='weno_id' name='location" . $i
                            . "[]' value='" . text($facility['weno_id']) . "'></td>";
                        print "</tr>";
                        ++$i;
                    }
                    ?>
                </table>
                <button type="submit" value="update" id="save_weno_id" class="btn btn-primary float-right"><?php echo xla('Update'); ?></button>
            </form>
        </div>
    </div>
</body>
</html>
