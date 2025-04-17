<?php

/**
 * interface/therapy_groups/therapy_groups_views/header.php contains header for all therapy group views.
 *
 * This is the header of all therapy group related views.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @author    Amiel Elboim <amielel@matrix.co.il>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016 Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @copyright Copyright (c) 2016 Amiel Elboim <amielel@matrix.co.il>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Core\Header;

?>
<!doctype html>

<html>
<head>
    <?php Header::setupHeader(['datetime-picker', 'topdialog', 'moment', 'datatables', 'datatables-dt', 'datatables-bs']); ?>

    <script>
        <?php require $GLOBALS['srcdir'] . "/formatting_DateToYYYYMMDD_js.js.php" ?>
    </script>
</head>

<body class="body_top therapy_group">
