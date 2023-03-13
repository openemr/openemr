<?php
    namespace OpenEMR\Modules\ClaimRevConnector;
    $tab="setup";
    require_once '../src/ClaimRevModuleSetup.php';
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
<title>ClaimRev Connect - Setup</title>
<body>
    <div class="row"> 
        <div class="col">
            <?php
                include '../templates/navbar.php';
            ?>
        </div>
    </div>
    <div class="row"> 
        <div class="col">
            <h1>Setup</h1>        
        </div>
    </div>
    <div class="row"> 
        <div class="col-6">
            <div class="card">
                <ul>
                    <li>
                        <h6>x12 Partner Record</h6>
                        <?php 
                            if(ClaimRevModuleSetup::DoesPartnerExists()) 
                            { 
                        ?>
                                It looks like your X12 partner record is setup.
                        <?php 
                            } 
                            else 
                            { 
                        ?>                    
                                Your x12 Partner has not been created, please contact us if you need assistance.
                        
                        <?php 
                            }
                        ?>
                    </li>
                    <li>
                        <h6>
                            Background Services
                        </h6>
                        There are required background services that are needed to send claims, pick up reports, and check eligibility.
                        They are listed below in a table, but if there is something strange going on use the button to re-create the records.
                        <form method="post" action="<?=$_SERVER['PHP_SELF'];?>">
                            <button type="submit" name="backgroundService" class="btn btn-primary">Set Defaults</button>
                        </form>
                    </li>
                    <li>
                        <h6>
                            SFTP Background Service
                        </h6>
                        <?php 
                            if(ClaimRevModuleSetup::CouldSftpServiceCauseIssues())  
                            {
                        ?>                                
                                The SFTP service is still activated to send claims. 
                                We have noticed that this service can cause our service not to work correctly. 
                                If you would like to deactivate it, click the following button. 
                                Note: if you're sending claims elsewhere through SFTP, this would stop that.
                                <form method="post" action="<?=$_SERVER['PHP_SELF'];?>">
                                    <button type="submit" name="deactivateSftp" class="btn btn-primary">Deactivate</button>
                                </form>
                        <?php
                            }
                            else
                            {
                        ?>
                                The SFTP Service has been disabled, this is good and will prevent the service from working against sending your claims.
                                However if you would like to reactivate it then click this button.
                                <form method="post" action="<?=$_SERVER['PHP_SELF'];?>">
                                    <button type="submit" name="reactivateSftp" class="btn btn-primary">Reactivate</button>
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
            <h1>Background Services</h1>        
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="card">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Active</th>
                            <th scope="col">Running</th>
                            <th scope="col">Next Run</th>
                            <th scope="col">Execute Interval</th>   
                            <th scope="col">Function</th>       
                            <th scope="col">Require Once</th>          
                        </tr>
                    </thead>
                    <tbody>
                            <?php 
                                foreach($services as $service)
                                {
                            ?>
                                <tr>
                                    <td>
                                        <?php echo($service["name"])  ?> - <?php echo($service["title"]) ?>
                                    </td>
                                    <td>
                                        <?php echo($service["active"]) ?>
                                    </td>
                                    <td>
                                        <?php echo($service["running"]) ?>
                                    </td>
                                    <td>
                                        <?php echo($service["next_run"]) ?>
                                    </td>
                                    <td>
                                        <?php echo($service["execute_interval"]) ?>
                                    </td>
                                    <td>
                                        <?php echo($service["function"]) ?>
                                    </td>
                                    <td>
                                        <?php echo($service["require_once"]) ?>
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
