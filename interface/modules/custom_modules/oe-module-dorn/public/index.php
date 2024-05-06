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

use OpenEMR\Core\Header;

$tab = "home";


//ensure user has proper access
// if (!AclMain::aclCheckCore('acct', 'bill')) {
//     echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("DORN Lab Configuration - Home")]);
//     exit;
// }
?>
<html lang="">
<head>
    <?php Header::setupHeader(['opener']); ?>
    <title> <?php echo xlt("DORN Configuration"); ?>  </title>
</head>
<body>
    <div class="row">
        <div class="col">
            <?php
            require '../templates/navbar.php';
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <h1><?php echo xlt("DORN Configuration"); ?></h1>
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
