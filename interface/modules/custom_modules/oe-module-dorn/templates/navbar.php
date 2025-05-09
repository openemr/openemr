<?php

/**
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
<<<<<<< HEAD
 * @copyright Copyright (c) 2022-2025 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Acl\AclMain;

$isAuth = AclMain::aclCheckCore('admin', 'users') ?? false;
=======
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

>>>>>>> d11e3347b (modules setup and UI changes)
?>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#"><?php echo xlt("DORN Lab Integration"); ?> </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item <?php
            if ($tab == "home") {
                echo "active";
            }
            ?>">
                <a class="nav-link" href="index.php"><?php echo xlt("Home"); ?></a>
            </li>
<<<<<<< HEAD
            <?php if ($isAuth) { ?>
=======
>>>>>>> d11e3347b (modules setup and UI changes)
            <li class="nav-item <?php
            if ($tab == "Configure Primary") {
                echo "active";
            } ?>">
                <a class="nav-link" href="primary_config.php"><?php echo xlt("Configure Primary"); ?></a>
            </li>
            <li class="nav-item <?php
            if ($tab == "lab setup") {
                echo "active";
            } ?>">
                <a class="nav-link" href="lab_setup.php"><?php echo xlt("Lab Setup"); ?> </a>
            </li>
<<<<<<< HEAD
            <?php } ?>
=======
>>>>>>> d11e3347b (modules setup and UI changes)
            <li class="nav-item <?php
            if ($tab == "orders") {
                echo "active";
            } ?>" >
                <a class="nav-link" href="orders.php"><?php echo xlt("Orders"); ?> </a>                            
            </li>
            <li class="nav-item <?php
            if ($tab == "results") {
                echo "active";
            } ?>" >
                <a class="nav-link" href="results.php"><?php echo xlt("Results"); ?></a>                            
<<<<<<< HEAD
            </li>  
            <li class="nav-item <?php
            if ($tab == "routes") {
                echo "active";
            } ?>" >
                <a class="nav-link" href="routes.php"><?php echo xlt("Route List"); ?></a>                            
            </li>               
=======
            </li>            
>>>>>>> d11e3347b (modules setup and UI changes)
        </ul>        
    </div>
</nav>       
