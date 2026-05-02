<?php

/**
 *
 * @package OpenEMR
 * @link    https://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\ClaimRevConnector\Bootstrap;
use OpenEMR\Modules\ClaimRevConnector\CsrfHelper;
use OpenEMR\Modules\ClaimRevConnector\EligibilityData;
use OpenEMR\Modules\ClaimRevConnector\EligibilityObjectCreator;
use OpenEMR\Modules\ClaimRevConnector\ModuleInput;

/** @var int|null $pid */

if ($pid === null) {
    echo xlt("Error retrieving patient.");
    exit;
}
$insurance = EligibilityData::getInsuranceData($pid);
$eligibilityCsrfToken = CsrfHelper::collectCsrfToken('eligibility');
$eligTestMode = (new Bootstrap(OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher()))
    ->getGlobalConfig()
    ->isTestModeEnabled();

//check if form was submitted
if (ModuleInput::postExists('checkElig')) {
    $pr = ModuleInput::postString('responsibility');

    // Collect selected products
    $selectedProducts = [];
    if (ModuleInput::postString('product_1') !== '') {
        $selectedProducts[] = 1;
    }
    if (ModuleInput::postString('product_3') !== '') {
        $selectedProducts[] = 3;
    }
    if (ModuleInput::postString('product_2') !== '') {
        $selectedProducts[] = 2;
    }
    if (ModuleInput::postString('product_5') !== '') {
        $selectedProducts[] = 5;
    }
    if ($selectedProducts === []) {
        $selectedProducts = [1]; // default to eligibility
    }

    $requestObjects = EligibilityObjectCreator::buildObject($pid, $pr, null, null, null, $selectedProducts);
    EligibilityObjectCreator::saveToDatabase($requestObjects, $pid);
    if ($requestObjects !== []) {
        $request = $requestObjects[0];
    }
}

?>

<?php if ($eligTestMode) { ?>
    <div class="alert alert-warning mt-2 py-2">
        <i class="fa fa-flask"></i>
        <strong><?php echo xlt("Test Mode"); ?></strong> &mdash;
        <?php echo xlt("ClaimRev test mode is on. Eligibility checks return simulated data fabricated from the patient's local insurance row, not a real payer response. Do not rely on these results clinically."); ?>
    </div>
<?php } ?>

<div class="row">
    <div class="col">
    <ul class="nav nav-tabs mb-2">
<?php
        $classActive = "active";
        $first = "true";
foreach ($insurance as $row) {
    ?>
            <li class="nav-item" role="presentation">
                <a id="claimrev-ins-<?php echo attr(ucfirst((string) $row['payer_responsibility']));?>-tab" aria-selected="<?php echo($first); ?>" class="nav-link <?php echo($classActive);?>"  data-toggle="tab" role="tab" href="#<?php echo attr(ucfirst((string) $row['payer_responsibility']));?>"> <?php echo text(ucfirst((string) $row['payer_responsibility']));?>  </a>
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
            <div id="<?php echo attr(ucfirst((string) $row['payer_responsibility']));?>" class="tab-pane <?php echo($classActive);?>">
                <div class="row">
                    <div class="col">
                        <form method="post" action="../../patient_file/summary/demographics.php">
                            <input type="hidden" id="responsibility" name="responsibility" value="<?php echo attr(ucfirst((string) $row['payer_responsibility']));?>">
                            <div class="form-row align-items-center mb-2">
                                <div class="col-auto">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input cr-product-cb cr-exclusive" type="checkbox" name="product_1" id="product_1_<?php echo attr($row['payer_responsibility']); ?>" value="1" checked data-pr="<?php echo attr($row['payer_responsibility']); ?>" data-excludes="product_3">
                                        <label class="form-check-label" for="product_1_<?php echo attr($row['payer_responsibility']); ?>"><?php echo xlt("Eligibility"); ?></label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input cr-product-cb cr-exclusive" type="checkbox" name="product_3" id="product_3_<?php echo attr($row['payer_responsibility']); ?>" value="1" data-pr="<?php echo attr($row['payer_responsibility']); ?>" data-excludes="product_1">
                                        <label class="form-check-label" for="product_3_<?php echo attr($row['payer_responsibility']); ?>"><?php echo xlt("Coverage Discovery"); ?></label>
                                    </div>
                                    <span class="border-left ml-2 pl-2"></span>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input cr-product-cb" type="checkbox" name="product_2" id="product_2_<?php echo attr($row['payer_responsibility']); ?>" value="1">
                                        <label class="form-check-label" for="product_2_<?php echo attr($row['payer_responsibility']); ?>"><?php echo xlt("Demographics"); ?></label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input cr-product-cb" type="checkbox" name="product_5" id="product_5_<?php echo attr($row['payer_responsibility']); ?>" value="1">
                                        <label class="form-check-label" for="product_5_<?php echo attr($row['payer_responsibility']); ?>"><?php echo xlt("MBI Finder"); ?></label>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <button type="submit" name="checkElig" class="btn btn-outline-primary btn-sm"><?php echo xlt("Queue Check"); ?></button>
                                    <button type="button" class="btn btn-primary btn-sm" onclick="checkNow('<?php echo attr($row['payer_responsibility']); ?>')">
                                        <span class="spinner-border spinner-border-sm d-none" id="spinner-<?php echo attr($row['payer_responsibility']); ?>" role="status"></span>
                                        <?php echo xlt("Check Now"); ?>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
    <?php
                $eligibilityCheck = EligibilityData::getEligibilityResult($pid, $row['payer_responsibility']);
    foreach ($eligibilityCheck as $check) {
        ?>
                            <div class="row mb-2">
                                <div class="col">
            <?php echo xlt("Status"); ?>: <?php echo text($check["status"]);?>
                                </div>
                                <div class="col">
                                    (<?php echo xlt("Last Update"); ?>: <?php echo text($check["last_update"]);?>)
                                </div>
                                <div class="col">
            <?php echo xlt("Message"); ?>: <?php echo text((string) $check["response_message"]);?>
                                </div>
                                <div class="col-auto d-flex align-items-center">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" title="<?php echo attr(xl('Update the Insurance card Eligibility tab with this data')); ?>"
                                        onclick="syncNativeEligibility('<?php echo attr((string) $pid); ?>', '<?php echo attr($row['payer_responsibility']); ?>', this)">
                                        <i class="fa fa-sync"></i> <?php echo xlt("Sync to Insurance Card"); ?>
                                    </button>
                                    <span class="cr-sync-status ml-2 small"></span>
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
              $path = __DIR__;
              $path = str_replace("src", "templates", $path);

    foreach ($eligibilityCheck as $check) {
        if ($check["individual_json"] == null) {
                        echo xlt("No Results");
        } else {
                    $individualJson = $check["individual_json"];
                    $individual = json_decode((string) $individualJson);
                    $prKey = attr($row['payer_responsibility']);

                    // Extract per-product claimRevResultIds for AI chat
                    $responseData = json_decode((string) ($check['response_json'] ?? ''), true);
                    $productResultIdsRaw = is_array($responseData) ? ($responseData['_productResultIds'] ?? []) : [];
                    $productResultIds = is_array($productResultIdsRaw) ? $productResultIdsRaw : [];
                    // Fallback: use the top-level claimRevResultId if no per-product map
                    $claimRevResultIdRaw = is_array($responseData) ? ($responseData['claimRevResultId'] ?? '') : '';
                    $claimRevResultId = is_string($claimRevResultIdRaw) ? $claimRevResultIdRaw : '';
            if ($productResultIds === [] && $claimRevResultId !== '') {
                $productResultIds = [1 => $claimRevResultId]; // assume eligibility
            }
                    $hasAnyChatId = $productResultIds !== [];
                    // Extract payer code from first eligibility or coverage discovery result
                    $chatPayerCode = '';
            if (is_array($responseData)) {
                $mappedRaw = $responseData['mappedData'] ?? null;
                $mappedIndividuals = (is_array($mappedRaw) && isset($mappedRaw['individuals']) && is_array($mappedRaw['individuals'])) ? $mappedRaw['individuals'] : [];
                $firstInd = $mappedIndividuals !== [] ? $mappedIndividuals[array_key_first($mappedIndividuals)] : [];
                $eligArr = is_array($firstInd) && isset($firstInd['eligibility']) && is_array($firstInd['eligibility']) ? $firstInd['eligibility'] : [];
                $firstElig = $eligArr !== [] ? $eligArr[array_key_first($eligArr)] : [];
                if (is_array($firstElig) && isset($firstElig['payerInfo']) && is_array($firstElig['payerInfo']) && isset($firstElig['payerInfo']['payerCode']) && is_string($firstElig['payerInfo']['payerCode'])) {
                    $chatPayerCode = $firstElig['payerInfo']['payerCode'];
                }
            }

                    $hasEligibility = is_object($individual) && property_exists($individual, 'eligibility') && is_iterable($individual->eligibility);
                    $hasDemographics = is_object($individual) && property_exists($individual, 'demographicInfo') && $individual->demographicInfo !== null;
                    $hasCoverageDiscoveryResults = is_object($individual) && property_exists($individual, 'coverageDiscovery') && is_iterable($individual->coverageDiscovery);
                    $insuranceFinderStatusRaw = is_object($individual) && property_exists($individual, 'insuranceFinderStatus') ? $individual->insuranceFinderStatus : '';
                    $insuranceFinderStatus = is_string($insuranceFinderStatusRaw) ? $insuranceFinderStatusRaw : '';
                    $hasCoverageDiscovery = $hasCoverageDiscoveryResults || $insuranceFinderStatus !== '';
                    $hasMbi = is_object($individual) && property_exists($individual, 'mbiFinderResults') && $individual->mbiFinderResults !== null;

            if (!$hasEligibility && !$hasDemographics && !$hasCoverageDiscovery && !$hasMbi) {
                echo xlt("No results returned for selected products");
            } else {
                ?>
                        <ul class="nav nav-tabs mb-2" id="product-tabs-<?php echo $prKey; ?>">
                    <?php if ($hasEligibility) { ?>
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#product-elig-<?php echo $prKey; ?>"><?php echo xlt("Eligibility"); ?></a>
                                </li>
                            <?php } ?>
                    <?php if ($hasCoverageDiscovery) { ?>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo !$hasEligibility ? 'active' : ''; ?>" data-toggle="tab" href="#product-coverage-<?php echo $prKey; ?>"><?php echo xlt("Coverage Discovery"); ?></a>
                                </li>
                            <?php } ?>
                    <?php if ($hasDemographics) { ?>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo !$hasEligibility && !$hasCoverageDiscovery ? 'active' : ''; ?>" data-toggle="tab" href="#product-demo-<?php echo $prKey; ?>"><?php echo xlt("Demographics"); ?></a>
                                </li>
                            <?php } ?>
                    <?php if ($hasMbi) { ?>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo !$hasEligibility && !$hasCoverageDiscovery && !$hasDemographics ? 'active' : ''; ?>" data-toggle="tab" href="#product-mbi-<?php echo $prKey; ?>"><?php echo xlt("MBI Finder"); ?></a>
                                </li>
                            <?php } ?>
                    <?php if ($hasAnyChatId) { ?>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#product-chat-<?php echo $prKey; ?>">
                                        <i class="fa fa-robot"></i> <?php echo xlt("Conversation"); ?>
                                        <span class="badge badge-info ml-1"><?php echo xlt("Beta"); ?></span>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                        <div class="tab-content">

                <?php
                // === Eligibility Tab (Product 1) ===
                if ($hasEligibility && is_object($individual)) {
                    $results = $individual->eligibility;
                    $index = 0;
                    ?>
                            <div id="product-elig-<?php echo $prKey; ?>" class="tab-pane active">
                    <?php foreach ($results as $result) {
                                $index++;
                                $eligibilityData = $result;
                                $benefits = null;
                                $subscriberPatient = null;
                                $data = null;
                        if (is_object($eligibilityData) && property_exists($eligibilityData, 'mapped271')) {
                            $data = $eligibilityData->mapped271;
                        }

                        if (is_object($data) && property_exists($data, 'dependent')) {
                            $dependent = $data->dependent;
                            if (is_object($dependent) && property_exists($dependent, 'benefits')) {
                                $benefits = $dependent->benefits;
                                $subscriberPatient = $dependent;
                            }
                        }

                        if (is_object($data) && property_exists($data, 'subscriber')) {
                            $subscriber = $data->subscriber;
                            if (is_object($subscriber) && property_exists($subscriber, 'benefits')) {
                                $benefits = $subscriber->benefits;
                                $subscriberPatient = $subscriber;
                            }
                        }
                        ?>
                                <ul class="nav nav-tabs nav-tabs-sm mb-2 mt-2">
                                    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#elig-quick-<?php echo $prKey . '-' . attr((string) $index); ?>"><?php echo xlt("Quick Info"); ?></a></li>
                                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#elig-deductibles-<?php echo $prKey . '-' . attr((string) $index); ?>"><?php echo xlt("Deductibles"); ?></a></li>
                                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#elig-benefits-<?php echo $prKey . '-' . attr((string) $index); ?>"><?php echo xlt("Benefits"); ?></a></li>
                                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#elig-medicare-<?php echo $prKey . '-' . attr((string) $index); ?>"><?php echo xlt("Medicare"); ?></a></li>
                                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#elig-validations-<?php echo $prKey . '-' . attr((string) $index); ?>"><?php echo xlt("Validations"); ?></a></li>
                                </ul>
                                <div class="tab-content">
                                    <div id="elig-quick-<?php echo $prKey . '-' . attr((string) $index); ?>" class="tab-pane active">
                                        <?php include $path . '/quick_info.php'; ?>
                                    </div>
                                    <div id="elig-deductibles-<?php echo $prKey . '-' . attr((string) $index); ?>" class="tab-pane">
                                        <?php include $path . '/deductibles.php'; ?>
                                    </div>
                                    <div id="elig-benefits-<?php echo $prKey . '-' . attr((string) $index); ?>" class="tab-pane">
                                        <?php
                                        if (is_object($data)) {
                                            $source = property_exists($data, 'informationSourceName') ? $data->informationSourceName : '';
                                            include $path . '/source.php';
                                            $receiver = property_exists($data, 'receiver') ? $data->receiver : null;
                                            include $path . '/receiver.php';
                                        }
                                        if ($benefits != null) {
                                            include $path . '/subscriber_patient.php';
                                            include $path . '/benefit.php';
                                        }
                                        ?>
                                    </div>
                                    <div id="elig-medicare-<?php echo $prKey . '-' . attr((string) $index); ?>" class="tab-pane">
                                        <?php include $path . '/medicare_info.php'; ?>
                                    </div>
                                    <div id="elig-validations-<?php echo $prKey . '-' . attr((string) $index); ?>" class="tab-pane">
                                        <?php include $path . '/validation.php'; ?>
                                    </div>
                                </div>
                            <?php } //end foreach eligibility ?>
                            </div>
                        <?php } //end if eligibility ?>

                        <?php
                        // === Coverage Discovery Tab (Product 3) ===
                        if ($hasCoverageDiscovery) {
                            ?>
                            <div id="product-coverage-<?php echo $prKey; ?>" class="tab-pane <?php echo !$hasEligibility ? 'active' : ''; ?>">
                                <?php if ($hasCoverageDiscoveryResults && is_object($individual) && property_exists($individual, 'coverageDiscovery')) {
                                    $coverageResults = $individual->coverageDiscovery;
                                    include $path . '/coverage_discovery_results.php';
                                } elseif (strtolower($insuranceFinderStatus) === 'complete') { ?>
                                    <div class="text-center py-5">
                                        <i class="fa fa-search fa-3x text-muted mb-3" style="opacity:0.4"></i>
                                        <h5><?php echo xlt("No Coverage Found"); ?></h5>
                                        <p class="text-muted"><?php echo xlt("Coverage Discovery was run, but no results were found."); ?></p>
                                    </div>
                                <?php } else { ?>
                                    <div class="text-center py-5">
                                        <i class="fa fa-info-circle fa-3x text-muted mb-3" style="opacity:0.4"></i>
                                        <h5><?php echo xlt("Coverage Discovery"); ?></h5>
                                        <p class="text-muted"><?php echo xlt("Status"); ?>: <?php echo text($insuranceFinderStatus); ?></p>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>

                        <?php
                        // === Demographics Tab (Product 2) ===
                        if ($hasDemographics && is_object($individual) && property_exists($individual, 'demographicInfo')) {
                            $demographicInfo = $individual->demographicInfo;
                            ?>
                            <div id="product-demo-<?php echo $prKey; ?>" class="tab-pane <?php echo !$hasEligibility && !$hasCoverageDiscovery ? 'active' : ''; ?>">
                                <?php include $path . '/demographics_results.php'; ?>
                            </div>
                        <?php } ?>

                        <?php
                        // === MBI Finder Tab (Product 5) ===
                        if ($hasMbi && is_object($individual) && property_exists($individual, 'mbiFinderResults')) {
                            $mbiResults = $individual->mbiFinderResults;
                            ?>
                            <div id="product-mbi-<?php echo $prKey; ?>" class="tab-pane <?php echo !$hasEligibility && !$hasCoverageDiscovery && !$hasDemographics ? 'active' : ''; ?>">
                                <?php include $path . '/mbi_results.php'; ?>
                            </div>
                        <?php } ?>

                        <?php
                        // === Conversation Tab (AI Chat) ===
                        if ($hasAnyChatId) {
                            ?>
                            <div id="product-chat-<?php echo $prKey; ?>" class="tab-pane">
                                <?php
                                $chatPrKey = $prKey;
                                $chatProductResultIds = $productResultIds;
                                include $path . '/eligibility_chat.php';
                                ?>
                            </div>
                        <?php } ?>

                        </div><!-- end tab-content -->
                    <?php
            } //end else has results
        }//else individual_json not null
    }//end main foreach
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
<script>
function checkNow(responsibility) {
    var spinner = document.getElementById('spinner-' + responsibility);
    if (spinner) {
        spinner.classList.remove('d-none');
    }

    // Gather product selections for this responsibility tab
    var formData = {
        pid: <?php echo js_escape((string) $pid); ?>,
        responsibility: responsibility,
        csrf_token: <?php echo js_escape($eligibilityCsrfToken); ?>
    };

    var suffix = responsibility;
    var p1 = document.getElementById('product_1_' + suffix);
    var p2 = document.getElementById('product_2_' + suffix);
    var p3 = document.getElementById('product_3_' + suffix);
    var p5 = document.getElementById('product_5_' + suffix);

    if (p1 && p1.checked) formData.product_1 = '1';
    if (p2 && p2.checked) formData.product_2 = '1';
    if (p3 && p3.checked) formData.product_3 = '1';
    if (p5 && p5.checked) formData.product_5 = '1';

    // Must have at least one product selected
    if (!formData.product_1 && !formData.product_2 && !formData.product_3 && !formData.product_5) {
        alert(<?php echo xlj("Please select at least one product to check"); ?>);
        if (spinner) spinner.classList.add('d-none');
        return;
    }

    $.ajax({
        url: '../../modules/custom_modules/oe-module-claimrev-connect/public/eligibility_check_now.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (spinner) {
                spinner.classList.add('d-none');
            }
            if (response.success) {
                // Reload the page to show updated results
                location.reload();
            } else {
                alert(response.message || <?php echo xlj("Eligibility check failed"); ?>);
            }
        },
        error: function() {
            if (spinner) {
                spinner.classList.add('d-none');
            }
            alert(<?php echo xlj("Error communicating with server"); ?>);
        }
    });
}

function syncNativeEligibility(pid, payerResponsibility, btn) {
    var origHtml = btn.innerHTML;
    var origClass = btn.className;
    var statusSpan = btn.parentElement.querySelector('.cr-sync-status');

    btn.disabled = true;
    btn.className = 'btn btn-secondary btn-sm';
    btn.innerHTML = '<i class="fa fa-circle-notch fa-spin"></i> ' + <?php echo xlj("Syncing..."); ?>;
    if (statusSpan) {
        statusSpan.innerHTML = '';
    }

    $.ajax({
        url: '../../modules/custom_modules/oe-module-claimrev-connect/public/eligibility_sync_native.php',
        type: 'POST',
        data: {
            pid: pid,
            payer_responsibility: payerResponsibility,
            csrf_token: <?php echo js_escape($eligibilityCsrfToken); ?>
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                btn.className = 'btn btn-success btn-sm';
                btn.innerHTML = '<i class="fa fa-check"></i> ' + <?php echo xlj("Synced"); ?>;
                if (statusSpan) {
                    statusSpan.innerHTML = '<span class="text-success"><i class="fa fa-check-circle"></i> ' + <?php echo xlj("Insurance card updated — refresh the patient dashboard to see it."); ?> + '</span>';
                }
                setTimeout(function() {
                    btn.innerHTML = origHtml;
                    btn.className = origClass;
                    btn.disabled = false;
                }, 4000);
            } else {
                btn.className = 'btn btn-outline-danger btn-sm';
                btn.innerHTML = '<i class="fa fa-exclamation-triangle"></i> ' + <?php echo xlj("Failed"); ?>;
                if (statusSpan) {
                    statusSpan.innerHTML = '<span class="text-danger"><i class="fa fa-times-circle"></i> ' + escapeHtmlSync(response.message || <?php echo xlj("Sync failed"); ?>) + '</span>';
                }
                setTimeout(function() {
                    btn.innerHTML = origHtml;
                    btn.className = origClass;
                    btn.disabled = false;
                }, 4000);
            }
        },
        error: function(xhr) {
            btn.className = 'btn btn-outline-danger btn-sm';
            btn.innerHTML = '<i class="fa fa-exclamation-triangle"></i> ' + <?php echo xlj("Error"); ?>;
            if (statusSpan) {
                statusSpan.innerHTML = '<span class="text-danger"><i class="fa fa-times-circle"></i> ' + <?php echo xlj("Error communicating with server"); ?> + '</span>';
            }
            setTimeout(function() {
                btn.innerHTML = origHtml;
                btn.className = origClass;
                btn.disabled = false;
            }, 4000);
        }
    });
}

function escapeHtmlSync(text) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}

// Mutual exclusivity: Eligibility and Coverage Discovery can't both be checked
document.querySelectorAll('.cr-exclusive').forEach(function(cb) {
    cb.addEventListener('change', function() {
        if (this.checked) {
            var excludes = this.dataset.excludes;
            var pr = this.dataset.pr;
            if (excludes && pr) {
                var other = document.getElementById(excludes + '_' + pr);
                if (other) other.checked = false;
            }
        }
    });
});
</script>
