<?php

/*
 * history_sdoh_health_concerns.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
 * SDOH Health Concerns Selection Page
 * Allows provider to select relevant health concerns after SDOH assessment
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    System Architect
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$srcdir = dirname(__FILE__, 4) . "/library";
require_once("../../globals.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\PatientIssuesService;
use OpenEMR\Services\SDOH\HistorySdohService;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\Logging\SystemLogger;

$pid = (int)($_GET['pid'] ?? 0);
$sdoh_id = (int)($_GET['sdoh_id'] ?? 0);
$logger = new SystemLogger();

// TODO: @adunsulag all of this needs to be wrapped into a Response and Controller for better structure
if (!$pid || !$sdoh_id) {
    $logger->errorLogCaller("Missing required parameters", ['pid' => $pid, "sdoh_id" => $sdoh_id]);
    die(xlt("Missing required parameters."));
}

if (!AclMain::aclCheckCore('patients', 'med', '', ['write', 'addonly'])) {
    $logger->errorLogCaller("Unauthorized access attempt to SDOH health concerns", ['pid' => $pid, "sdoh_id" => $sdoh_id]);
    die(xlt("Not authorized"));
}

$sdohService = new HistorySdohService();
$result = $sdohService->search(['id' => $sdoh_id, 'pid' => $pid]);
if (!$result->hasData()) {
    $logger->errorLogCaller("SDOH assessment not found", ['pid' => $pid, "sdoh_id" => $sdoh_id]);
    die("SDOH assessment not found.");
}
$assessmentConcerns = HistorySdohService::concernsFromAssessmentV3($result->getFirstDataResult());

$concerns = [];
foreach ($assessmentConcerns as $healthConcern) {
    $fullCode = $healthConcern['code_type'] . ':' . $healthConcern['code'];
    $concerns[] = [
        'id' => $fullCode,
        'title' => $healthConcern['code_text'],
        'diagnosis' => $fullCode,
        'comments' => $healthConcern['text'] ?? '',
        'date' => $healthConcern['date'] ?? date("Y-m-d"),
        'author_id' => $healthConcern['author']['author_id'] ?? $_SESSION['authUserID']
    ];
}

// Get patient name
$patientService = new PatientService();
$patient = $patientService->findByPid($pid);
$patientName = ($patient['fname'] ?? '') . ' ' . ($patient['lname'] ?? '');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"] ?? '')) {
        CsrfUtils::csrfNotVerified();
    }

    $selectedConcerns = $_POST['health_concerns'] ?? [];
    $userId = $_SESSION['authUserID'] ?? 0;

    try {
        $committed = false;
        if (!empty($selectedConcerns)) {
            QueryUtils::startTransaction();
            // Create patient-specific condition records and link them to SDOH assessment
            $patientIssuesService = new PatientIssuesService();
            $hashMap = array_combine($selectedConcerns, $selectedConcerns);

            foreach ($concerns as $concern) {
                if (!isset($hashMap[$concern['id']])) {
                    continue; // Not selected
                }

                // Create patient-specific condition record
                $conditionData = [
                    'pid' => $pid,
                    'type' => 'health_concern',
                    'title' => $concern['title'],
                    'diagnosis' => $concern['diagnosis'],
                    'date' => date('Y-m-d H:i:s'),
                    'activity' => 1,
                    'user' => $concern['author_id'] ?? '',
                    'groupname' => $_SESSION['authProvider'] ?? '',
                    'comments' => $concern['comments'],
                    'subtype' => 'sdoh',
                    'begdate' => $concern['date'] ?? date("Y-m-d"),
                    'verification' => 'confirmed', // Assumed confirmed since provider is adding it after assessment
                ];

                $savedCondition = $patientIssuesService->createIssue($conditionData);
                $conditionRecordId = $savedCondition['id'] ?? null;

                if ($conditionRecordId) {
                    // Link the condition to the SDOH assessment
                    QueryUtils::sqlInsert(
                        "INSERT INTO form_history_sdoh_health_concerns (sdoh_history_id, health_concern_id, created_by) VALUES (?, ?, ?)",
                        [$sdoh_id, $conditionRecordId, $userId]
                    );
                }
            }
            QueryUtils::commitTransaction();
            $committed = true;
        }
        // Redirect to SDOH summary
        $redirectUrl = $GLOBALS['webroot'] . "/interface/patient_file/history/history_sdoh_widget.php?pid=" . urlencode((string) $pid);
        header("Location: " . $redirectUrl);
        exit();
    } catch (SqlQueryException $exception) {
        (new SystemLogger())->errorLogCaller("Failed to save health concerns: " . $exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
        die(xlt("An error occurred while saving health concerns."));
    } finally {
        if (!$committed) {
            QueryUtils::rollBackTransaction();
        }
    }
}

$csrf = CsrfUtils::collectCsrfToken();
?>

<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(['common']); ?>
    <title><?php echo xlt("Add Health Concerns"); ?></title>
    <style>
        .success-message {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .concern-item {
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            margin-bottom: 10px;
            transition: background-color 0.2s;
        }
        .concern-item:hover {
            background-color: #f8f9fa;
        }
        .concern-item label {
            margin-bottom: 0;
            cursor: pointer;
            font-weight: 500;
        }
        .concern-description {
            font-size: 0.9em;
            color: #6c757d;
            margin-top: 5px;
        }
        .button-group {
            margin-top: 30px;
            text-align: center;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            margin-right: 15px;
        }
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }
    </style>
</head>
<body class="body_top">
<div class="container mt-4">
    <!-- Success Message -->
    <div class="success-message">
        <h5 class="mb-1">
            <i class="fa fa-check-circle"></i>
            <?php echo xlt("SDOH Assessment Saved Successfully"); ?>
        </h5>
        <p class="mb-0">
            <?php echo xlt("Assessment completed for"); ?> <strong><?php echo text($patientName); ?></strong>
        </p>
    </div>

    <!-- Health Concerns Selection -->
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0"><?php echo xlt("Add Related Health Concerns"); ?></h4>
        </div>
        <div class="card-body">
            <p class="text-muted mb-4">
                <?php echo xlt("Based on the SDOH assessment, the following health concerns may be relevant for this patient. Select any that apply:"); ?>
            </p>

            <form method="post" action="">
                <input type="hidden" name="csrf_token_form" value="<?php echo attr($csrf); ?>" />

                <?php if (empty($concerns)) : ?>
                    <div class="alert alert-info">
                        <?php echo xlt("No health concerns are currently configured in the system."); ?>
                    </div>
                <?php else : ?>
                    <div class="row">
                        <?php foreach ($concerns as $concern) : ?>
                            <div class="col-md-6 mb-3">
                                <div class="concern-item">
                                    <label class="form-check-label w-100">
                                        <input type="checkbox"
                                               name="health_concerns[]"
                                               value="<?php echo attr($concern['id']); ?>"
                                               class="form-check-input mr-2">
                                        <?php echo text($concern['title']); ?>
                                    <?php if (!empty($concern['diagnosis'])) : ?>
                                        <div class="concern-description">
                                            <?php echo text($concern['diagnosis']); ?>
                                        </div>
                                    <?php endif; ?>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fa fa-plus"></i>
                        <?php echo xlt("Add Selected Concerns"); ?>
                    </button>

                    <a href="<?php echo attr($GLOBALS['webroot'] . "/interface/patient_file/history/history_sdoh_widget.php?pid=" . urlencode((string) $pid)); ?>"
                       class="btn btn-secondary btn-lg">
                        <i class="fa fa-times"></i>
                        <?php echo xlt("Skip - No Concerns"); ?>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Help Text -->
    <div class="mt-4">
        <small class="text-muted">
            <i class="fa fa-info-circle"></i>
            <?php echo xlt("Selected health concerns will be added as active conditions for this patient and linked to this SDOH assessment."); ?>
        </small>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Select all functionality
        $('.concern-item input[type="checkbox"]').change(function() {
            if ($(this).is(':checked')) {
                $(this).closest('.concern-item').addClass('bg-light');
            } else {
                $(this).closest('.concern-item').removeClass('bg-light');
            }
        });

        // Enhance form submission
        $('form').submit(function() {
            var checkedCount = $('input[name="health_concerns[]"]:checked').length;
            if (checkedCount === 0) {
                return confirm(<?php echo xlj("No health concerns selected. Continue without adding any?"); ?>);
            }
            return true;
        });
    });
</script>
</body>
</html>
