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
    $tab="setup";
    use OpenEMR\Modules\ClaimRevConnector\ClaimRevModuleSetup;

    $services = ClaimRevModuleSetup::GetBackgroundServices();
if(isset($_POST['deactivateSftp'])) {
    ClaimRevModuleSetup::DeactivateSftpService();
    
}
if(isset($_POST['reactivateSftp'])) { 
    ClaimRevModuleSetup::ReactivateSftpService();
    
}
if(isset($_POST['backgroundService'])) { 
    ClaimRevModuleSetup::CreateBackGroundServices();
    
}

    $services = ClaimRevModuleSetup::GetBackgroundServices();
?>
<html>
    <head>
        <link rel="stylesheet" href="../../../../../public/assets/bootstrap/dist/css/bootstrap.min.css">
    </head>
    
    <title> <?php echo xlt("ClaimRev Connect - Setup"); ?></title>


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
            <h1><?php echo xlt("Setup"); ?></h1>        
        </div>
    </div>
    <div class="row"> 
        <div class="col-6">
            <div class="card">
                <ul>
                    <li>
                        <h6><?php echo xlt("x12 Partner Record"); ?></h6>
                        <?php 
                        if(ClaimRevModuleSetup::DoesPartnerExists()) { 
                            echo xlt("It looks like your X12 partner record is setup."); 
                   
                        } 
                        else 
                        { 
                            echo xlt("Your x12 Partner has not been created, please contact us if you need assistance.");                                               
                        }
                        ?>
                    </li>
                    <li>
                        <h6>
                            <?php echo xlt("Background Services"); ?>
                            
                        </h6>
                        <?php echo xlt("There are required background services that are needed to send claims, pick up reports, and check eligibility. They are listed below in a table, but if there is something strange going on use the button to re-create the records."); ?>
                        
                        <form method="post" action="setup.php">
                            <button type="submit" name="backgroundService" class="btn btn-primary"><?php echo xlt("Set Defaults"); ?></button>
                        </form>
                    </li>
                    <li>
                        <h6>
                            <?php echo xlt("SFTP Background Service"); ?>
                            
                        </h6>
                        <?php 
                        if(ClaimRevModuleSetup::CouldSftpServiceCauseIssues()) {
                            echo xlt("The SFTP service is still activated to send claims. We have noticed that this service can cause our service not to work correctly. If you would like to deactivate it, click the following button. Note: if you're sending claims elsewhere through SFTP, this would stop that.");
                            ?>                                
                                
                                <form method="post" action="setup.php">
                                    <button type="submit" name="deactivateSftp" class="btn btn-primary"><?php echo xlt("Deactivate"); ?></button>
                                </form>
                            <?php
                        }
                        else
                        {
                            echo xlt("The SFTP Service has been disabled, this is good and will prevent the service from working against sending your claims. However if you would like to reactivate it then click this button.");
                            ?>
                                
                                <form method="post" action="setup.php">
                                    <button type="submit" name="reactivateSftp" class="btn btn-primary"><?php echo xlt("Reactivate"); ?></button>
                                </form>
                            <?php

                        }
                        ?>
                    </li>
                </ul>
             
                </div>
        </div>
    </div>
    <div class="row"> 
        <div class="col">
            <h1><?php echo xlt("Background Services")?></h1>        
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="card">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col"><?php echo xlt("Name"); ?></th>
                            <th scope="col"><?php echo xlt("Active"); ?></th>
                            <th scope="col"><?php echo xlt("Running"); ?></th>
                            <th scope="col"><?php echo xlt("Next Run"); ?></th>
                            <th scope="col"><?php echo xlt("Execute Interval"); ?></th>   
                            <th scope="col"><?php echo xlt("Function"); ?></th>       
                            <th scope="col"><?php echo xlt("Require Once"); ?></th>          
                        </tr>
                    </thead>
                    <tbody>
                            <?php 
                            foreach($services as $service)
                            {
                                ?>
                                <tr>
                                    <td>
                                    <?php echo text($service["name"])  ?> - <?php echo text($service["title"]) ?>
                                    </td>
                                    <td>
                                    <?php echo text($service["active"]) ?>
                                    </td>
                                    <td>
                                    <?php echo text($service["running"]) ?>
                                    </td>
                                    <td>
                                    <?php echo text($service["next_run"]) ?>
                                    </td>
                                    <td>
                                    <?php echo text($service["execute_interval"]) ?>
                                    </td>
                                    <td>
                                    <?php echo text($service["function"]) ?>
                                    </td>
                                    <td>
                                    <?php echo text($service["require_once"]) ?>
                                    </td>                                  
                                </tr>
                                <?php
                            }
                            ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
