<?php

/**
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022-2025 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once __DIR__ . "/../../../../globals.php";

use OpenEMR\Core\Header;

$tab = "home";

// ACL is covered by the menu item
?>
<!DOCTYPE html>
<html lang="">
<head>
    <?php Header::setupHeader(['opener']); ?>
    <title> <?php echo xlt("DORN Configuration"); ?>  </title>
</head>
<body class="container-fluid">
    <div class="row">
        <div class="col">
            <?php
            require '../templates/navbar.php';
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <h3><?php echo xlt("DORN Configuration"); ?></h3>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <?php
            require '../templates/contact.php';
            ?>
        </div>
    </div>
</body>
</html>
