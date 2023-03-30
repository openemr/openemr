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

use OpenEMR\Modules\ClaimRevConnector\EligibilityData;
use OpenEMR\Modules\ClaimRevConnector\EligibilityInquiryRequest;
use OpenEMR\Modules\ClaimRevConnector\SubscriberPatientEligibilityRequest;
use OpenEMR\Modules\ClaimRevConnector\EligibilityObjectCreator;
use OpenEMR\Modules\ClaimRevConnector\ValueMapping;

if ($pid == null) {
    echo xlt("Error retrieving patient.");
    exit;
}
$insurance = EligibilityData::getInsuranceData($pid);

//check if form was submitted
if (isset($_POST['checkElig'])) {
    $pr = $_POST['responsibility'];
    //$pid is found on the parent page that is including this php file
    $formattedPr = ValueMapping::mapPayerResponsibility($pr);
    EligibilityData::removeEligibilityCheck($pid, $formattedPr);
    $requestObjects = EligibilityObjectCreator::buildObject($pid, $pr, null, null, null);
    EligibilityObjectCreator::saveToDatabase($requestObjects, $pid);
    $request = $requestObjects[0];
}

?>





<div class="row">
    <div class="col">
    <ul class="nav nav-tabs mb-2">
<?php
        $classActive = "active";
        $first = "true";
foreach ($insurance as $row) {
    ?>
            <li class="nav-item" role="presentation">
                <a id="claimrev-ins-<?php echo attr(ucfirst($row['payer_responsibility']));?>-tab" aria-selected="<?php echo($first); ?>" class="nav-link <?php echo($classActive);?>"  data-toggle="tab" role="tab" href="#<?php echo attr(ucfirst($row['payer_responsibility']));?>"> <?php echo xlt(ucfirst($row['payer_responsibility']));?>  </a>
            </li>
    <?php
    $first = "false";
    $classActive = "";
}
?>
        
    </ul>
    <div class="tab-content">
<?php
        $classActive = "in active";
foreach ($insurance as $row) {
    ?>
            <div id="<?php echo attr(ucfirst($row['payer_responsibility']));?>" class="tab-pane <?php echo($classActive);?>">
                <div class="row">
                    <div class="col-2">
                    
                        <form method="post" action="../../patient_file/summary/demographics.php">
                            <input type="hidden" id="responsibility" name="responsibility" value="<?php echo attr(ucfirst($row['payer_responsibility']));?>">
                            <button type="submit" name="checkElig" class="btn btn-primary"><?php echo xlt("Check"); ?></button>
                        </form>
                    </div>
                    <div class="col">
    <?php
                $eligibilityCheck = EligibilityData::getEligibilityResult($pid, $row['payer_responsibility']);
    foreach ($eligibilityCheck as $check) {
        ?>
                            <div class="row">
                                <div class="col">
            <?php echo xlt("Status"); ?>: <?php echo text($check["status"]);?>
                                </div>
                                <div class="col">
                                    (<?php echo xlt("Last Update"); ?>: <?php echo text($check["last_update"]);?>)
                                </div>
                                <div class="col">
            <?php echo xlt("Message"); ?>: <?php echo text($check["response_message"]);?>
                                </div>                                     
                            </div>
        <?php
    }//end foreach
    ?>                        
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <hr/>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
    <?php
              //yeah.... this weird. why would someone do this? because I couldn't get the include
              //see my files one directory up. I might of done something wrong but I spent a good hour
              //trying to figure it out. I finally decided to go use something like this and make it
              //look like a hack.
              $path = __DIR__;
              $path = str_replace("src", "templates", $path);


    foreach ($eligibilityCheck as $check) {
        if ($check["eligibility_json"] == null) {
                        echo xlt("No Results");
        } else {
                    $result = $check["eligibility_json"];
                    $eligibilityData = json_decode($result);
                    $benefits = null;
                    $subscriberPatient = null;
                    $data = null;
            if (property_exists($eligibilityData, 'mapped271')) {
                $data = $eligibilityData->mapped271;
            }

            if (property_exists($data, 'dependent')) {
                $dependent = $data->dependent;
                if ($dependent != null) {
                    if (property_exists($dependent, 'benefits')) {
                        $benefits = $dependent->benefits;
                        $subscriberPatient = $dependent;
                    }
                }
            }

            if (property_exists($data, 'subscriber')) {
                $subscriber = $data->subscriber;
                if ($subscriber != null) {
                    if (property_exists($subscriber, 'benefits')) {
                        $benefits = $subscriber->benefits;
                        $subscriberPatient = $subscriber;
                    }
                }
            }

            ?>                            
                                <ul class="nav nav-tabs mb-2">
            <?php
                    $classActive = "active";
                    $first = "true";
            ?>
                                <li class="nav-item" role="presentation">
                                        <a id="claimrev-ins-quick-tab" aria-selected="<?php echo($first); ?>" class="nav-link active"  data-toggle="tab" role="tab" href="#eligibility-quick"> <?php echo xlt("Quick Info "); ?></a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a id="claimrev-ins-deductibles-tab" aria-selected="<?php echo($first); ?>" class="nav-link"  data-toggle="tab" role="tab" href="#eligibility-deductibles"> <?php echo xlt("Deductibles"); ?></a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a id="claimrev-ins-benefits-tab" aria-selected="<?php echo($first); ?>" class="nav-link"  data-toggle="tab" role="tab" href="#eligibility-benefits"><?php echo xlt("Benefits"); ?></a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a id="claimrev-ins-medicare-tab" aria-selected="<?php echo($first); ?>" class="nav-link"  data-toggle="tab" role="tab" href="#eligibility-medicare"><?php echo xlt("Medicare"); ?></a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a id="claimrev-ins-validations-tab" aria-selected="<?php echo($first); ?>" class="nav-link"  data-toggle="tab" role="tab" href="#eligibility-validations"> <?php echo xlt("Validations"); ?></a>
                                    </li>                                 
            <?php
                        $first = "false";
                        $classActive = "";
            ?>                                
                                </ul>
                            <div class="tab-content">
                                <div id="eligibility-quick" class="tab-pane active">
                                    <div class="row">
                                        <div class="col">
            <?php
                                include $path . '/quick_info.php';
            ?>
                                        </div>
                                    </div>
                                </div>
                                <div id="eligibility-deductibles" class="tab-pane">
                                    <div class="row">
                                        <div class="col">
            <?php
                                include $path . '/deductibles.php';
            ?>
                                        </div>
                                    </div>
                                </div>
                                <div id="eligibility-medicare" class="tab-pane">
                                    <div class="row">
                                        <div class="col">
            <?php
                                include $path . '/medicare_info.php';
            ?>
                                        </div>
                                    </div>
                                </div>
                                <div id="eligibility-benefits" class="tab-pane">
                                 
                                    <div class="row">
                                        <div class="col">
            <?php
                                $source = $data->informationSourceName;
                                include $path . '/source.php';
            ?>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col">
            <?php
                                $receiver = $data->receiver;
                                include $path . '/receiver.php';
            ?>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col">
            <?php
            if ($benefits != null) {
                        include $path . '/subscriber_patient.php';
                        include $path . '/benefit.php';
            }
            ?>
                                        </div>
                                    </div>
                                </div>
                                <div id="eligibility-validations" class="tab-pane">
                                    <div class="row">
                                        <div class="col">
            <?php
                                include $path . '/validation.php';
            ?>
                                        </div>
                                    </div>
                                </div>     
                            </div>
  
            <?php
        }//else results
    }
    ?>   
                    </div>
                </div>
            </div>            
        <?php
        $classActive = "";
}//end ($insurance as $row)
?>
    </div>
</div>
</div>



