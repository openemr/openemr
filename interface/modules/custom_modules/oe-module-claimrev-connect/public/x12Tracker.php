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
    use OpenEMR\Modules\ClaimRevConnector\X12TrackerPage;

    $tab = "x12";

    //ensure user has proper access
if (!AclMain::aclCheckCore('acct', 'bill')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("ClaimRev Connect - X12 Tracker")]);
    exit;
}

    $datas = [];
    //check if form was submitted
if (isset($_POST['SubmitButton'])) {
    $datas = X12TrackerPage::searchX12Tracker($_POST);
}
?>

<html>
    <head>
        <link rel="stylesheet" href="../../../../../public/assets/bootstrap/dist/css/bootstrap.min.css">
    </head>
    <title><?php echo xlt("ClaimRev Connect - X12 Tracker"); ?></title>
    <body>
        <div class="row">
            <div class="col">
                <?php require '../templates/navbar.php'; ?>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <p>
                    <?php echo xlt("This tab helps give visibility to files that are in the x12 Tracker table."); ?>    
                </p>
                            
            </div>       
        </div>
        <div class="row">
            <div class="col">
                <form method="post" action="x12Tracker.php">
                    <div class="card">  
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label for="startDate"><?php echo xlt("Created Date Start");?></label>
                                    <input type="date" class="form-control"  id="startDate" name="startDate"  value="<?php echo isset($_POST['startDate']) ? attr($_POST['startDate']) : '' ?>" placeholder="yyyy-mm-dd"/>
                                </div>
                            </div>                    
                            <div class="col">
                                <div class="form-group">
                                    <label for="endDate"><?php echo xlt("Created Date End");?></label>
                                    <input type="date" class="form-control"  id="endDate" name="endDate"  value="<?php echo isset($_POST['endDate']) ? attr($_POST['endDate']) : '' ?>" placeholder="yyyy-mm-dd"/>
                                </div>
                            </div>
                            <div class="col">
                            
                            </div>                    
                            <div class="col">
                                
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <button type="submit" name="SubmitButton" class="btn btn-primary"><?php echo xlt("Submit"); ?></button>
                            </div>
                            <div class="col-10">
                            
                            </div>
                        </div>        
                    </div> 
                </form>

            </div>
        </div>

        <div class="row">
            <div class="col">                
                <?php
                if ($datas != null) { ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col"><?php echo xlt("Filename"); ?></th>
                                <th scope="col"><?php echo xlt("Messages"); ?></th>
                                <th scope="col"><?php echo xlt("Status"); ?></th>                              
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($datas as $data) {
                            ?>  
                            <tr>
                                <td>
                                <?php echo text($data["x12_filename"]); ?>
                                </td>
                                <td>
                                <?php echo text($data["status"]); ?>
                                </td>
                                <td>
                                <?php echo text($data["messages"]); ?>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <a href="index.php"><?php echo xlt("Back to index"); ?></a>
            </div>
        </div>

    </body>



</html>
