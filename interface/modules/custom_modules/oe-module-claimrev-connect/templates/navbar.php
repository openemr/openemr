<?php

/**
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

?>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#"><?php echo xlt("ClaimRev Connect"); ?> </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item <?php if ($tab == "home") {
                echo "active";
                                } ?>">
                <a class="nav-link" href="index.php"><?php echo xlt("Home"); ?></a>
            </li>
            <li class="nav-item <?php if ($tab == "claims") {
                echo "active";
                                } ?>">
                <a class="nav-link" href="claims.php"><?php echo xlt("Claims"); ?></a>
            </li>
            <li class="nav-item <?php if ($tab == "eras") {
                echo "active";
                                } ?>">
                <a class="nav-link" href="era.php"><?php echo xlt("ERAs"); ?></a>
            </li>
            <li class="nav-item <?php if ($tab == "x12") {
                echo "active";
                                } ?>" >
                <a class="nav-link" href="x12Tracker.php"><?php echo xlt("X12 Tracker"); ?></a>                            
            </li>
            <li class="nav-item <?php if ($tab == "setup") {
                echo "active";
                                } ?>" >
                <a class="nav-link" href="setup.php"><?php echo xlt("Setup"); ?></a>                            
            </li>
            <li class="nav-item <?php if ($tab == "connectivity") {
                echo "active";
                                } ?>" >
                <a class="nav-link" href="debug-info.php"><?php echo xlt("Connectivity"); ?></a>                            
            </li>
        </ul>        
    </div>
</nav>       
