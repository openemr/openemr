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

    require_once "../../../../globals.php";

    use OpenEMR\Common\Acl\AclMain;
    use OpenEMR\Common\Twig\TwigContainer;

    $tab = "home";

//ensure user has proper access
if (!AclMain::aclCheckCore('acct', 'bill')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("ClaimRev Connect - Home")]);
    exit;
}
?>
<html>
    <head>
        <link rel="stylesheet" href="../../../../../public/assets/bootstrap/dist/css/bootstrap.min.css">
    </head>
<title> <?php echo xlt("ClaimRev Connect - Home"); ?>  </title>
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
            <h1><?php echo xlt("ClaimRev - Client Access"); ?></h1>        
        </div>
    </div>
    <div class="row"> 
        <div class="col">
            <div class="card">
                <p>
                    <?php echo xlt("Welcome to the ClaimRev Connector. This is your link to the portal and claim processing."); ?>             
                </p>
               
                <h6> <?php echo xlt("Tab Descriptions"); ?>   </h6>
                <ul>
                    <li>
                        <?php echo xlt("Claims -> Lets you search claims sent to ClaimRev and view the status."); ?>                          
                    </li>
                    <li>
                        <?php echo xlt("X12 Tracker -> Display's files that have been submitted or in the process of being submitted."); ?>                          
                    </li>
                    <li>
                        <?php echo xlt("Setup -> Helps identify any setup issues along with checking background services."); ?>                          
                    </li>
                    <li>
                        <?php echo xlt("Connectivity -> Display's information that may help support fix connection issues you maybe having."); ?>                         
                    </li>
                </ul>

                <h6><?php echo xlt("Support/Sales"); ?></h6>
                <ul>
                    <li> <?php echo xlt("Call"); ?>: <a href="tel:9189430020">1-918-943-0020<a>   </li>
                    <li> <?php echo xlt("Email Support"); ?>: <a href = "support@claimrev.com">support@claimrev.com</a> </li>
                    <li> <?php echo xlt("Email Sales"); ?>: <a href = "sales@claimrev.com">sales@claimrev.com</a> </li>
                </ul>
            </div>
        
        </div>
    </div>
</body>
</html>
