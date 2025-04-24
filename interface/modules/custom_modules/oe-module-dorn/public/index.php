<?php

/**
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
<<<<<<< HEAD
 * @copyright Copyright (c) 2022-2025 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once __DIR__ . "/../../../../globals.php";
=======
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once "../../../../globals.php";
>>>>>>> d11e3347b (modules setup and UI changes)

use OpenEMR\Core\Header;

$tab = "home";

<<<<<<< HEAD
// ACL is covered by the menu item
?>
<!DOCTYPE html>
=======

//ensure user has proper access
// if (!AclMain::aclCheckCore('acct', 'bill')) {
//     echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("DORN Lab Configuration - Home")]);
//     exit;
// }
?>
>>>>>>> d11e3347b (modules setup and UI changes)
<html lang="">
<head>
    <?php Header::setupHeader(['opener']); ?>
    <title> <?php echo xlt("DORN Configuration"); ?>  </title>
</head>
<<<<<<< HEAD
<body class="container-fluid">
=======
<body>
>>>>>>> d11e3347b (modules setup and UI changes)
    <div class="row">
        <div class="col">
            <?php
            require '../templates/navbar.php';
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col">
<<<<<<< HEAD
            <h3><?php echo xlt("DORN Configuration"); ?></h3>
=======
            <h1><?php echo xlt("DORN Configuration"); ?></h1>
>>>>>>> d11e3347b (modules setup and UI changes)
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
