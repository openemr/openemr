<?php
/**
 * MedEx Navigation Bar Template
 *
 * This navigation bar is injected into messages.php and patient_tracker.php
 * when the MedEx module is enabled.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2026 MedEx
 * @license   Proprietary - All Rights Reserved
 */

// This file is included by event listeners, so $loggedIn variable is available
// $loggedIn contains the user's MedEx session data

use OpenEMR\Core\OEGlobalsBag;

global $webroot;
$isLoggedIn = !empty($loggedIn['token'] ?? false);
$disableRcb = '0';

$normalizeServices = static function ($services): array {
    $normalized = [];
    if (!is_array($services)) {
        return $normalized;
    }
    foreach ($services as $key => $value) {
        if (is_int($key)) {
            $serviceKey = strtolower(trim((string)$value));
            if ($serviceKey !== '') {
                $normalized[$serviceKey] = true;
            }
            continue;
        }
        if ($value === true || $value === 1 || $value === '1') {
            $serviceKey = strtolower(trim((string)$key));
            if ($serviceKey !== '') {
                $normalized[$serviceKey] = true;
            }
        }
    }
    return $normalized;
};

$enabledServices = $normalizeServices($loggedIn['enabled_services'] ?? []);
if (empty($enabledServices)) {
    $status = sqlQuery("SELECT status FROM medex_prefs ORDER BY MedEx_lastupdated DESC LIMIT 1");
    $decodedStatus = json_decode((string)($status['status'] ?? ''), true);
    foreach ([
        $decodedStatus['enabled_services'] ?? [],
        $decodedStatus['practice']['enabled_services'] ?? [],
    ] as $candidateServices) {
        $enabledServices = $normalizeServices($candidateServices);
        if (!empty($enabledServices)) {
            break;
        }
    }
}
$hasSecureChat = !empty($enabledServices['secure_chat'])
    || is_file(__DIR__ . '/../../public/secure_chat.php');

if (class_exists('OpenEMR\Core\OEGlobalsBag')) {
    $globalsBag = OEGlobalsBag::getInstance();
    $disableRcb = $globalsBag->get('disable_rcb');
}
?>

<nav class="navbar navbar-expand-md navbar-light bg-light medex-navbar" id="medex_nav">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#medexNavbar"
            aria-controls="medexNavbar" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="medexNavbar">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a class="nav-link" href="<?php echo $webroot; ?>/interface/main/messages/messages.php">
                    <i class="fas fa-envelope"></i> <?php echo xlt('Messages'); ?>
                </a>
            </li>

            <?php if ($disableRcb != '1'): ?>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo $webroot; ?>/interface/main/messages/messages.php?go=Recalls">
                    <i class="fas fa-calendar-check"></i> <?php echo xlt('Recall Board'); ?>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="<?php echo $webroot; ?>/interface/main/messages/messages.php?go=addRecall">
                    <i class="fas fa-plus"></i> <?php echo xlt('New Recall'); ?>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isLoggedIn): ?>
            <li class="nav-item">
                <a class="nav-link" href="#" onclick="event.preventDefault(); window.open('<?php echo $webroot; ?>/interface/modules/custom_modules/oe-module-medex/public/sms_bot_list.php?nomenu=1', '_blank', 'width=450,height=800,resizable=1,scrollbars=1'); return false;">
                    <i class="fas fa-sms"></i> <?php echo xlt('SMS Bot'); ?>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="<?php echo $webroot; ?>/interface/patient_tracker/patient_tracker.php">
                    <i class="fas fa-procedures"></i> <?php echo xlt('Flow Board'); ?>
                </a>
            </li>

            <?php if ($hasSecureChat): ?>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo $webroot; ?>/interface/modules/custom_modules/oe-module-medex/public/secure_chat.php">
                    <i class="fas fa-comments"></i> <?php echo xlt('Secure Chat'); ?>
                </a>
            </li>
            <?php endif; ?>

            <li class="nav-item">
                <a class="nav-link" href="<?php echo $webroot; ?>/interface/main/messages/messages.php?go=Preferences">
                    <i class="fas fa-cog"></i> <?php echo xlt('Preferences'); ?>
                </a>
            </li>
            <?php else: ?>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo $webroot; ?>/interface/main/messages/messages.php?go=setup&stage=1">
                    <i class="fas fa-wrench"></i> <?php echo xlt('Setup'); ?>
                </a>
            </li>
            <?php endif; ?>
        </ul>

        <?php if ($isLoggedIn): ?>
        <span class="navbar-text">
            <i class="fas fa-circle text-success"></i>
            <?php echo xlt('MedEx Connected'); ?>
        </span>
        <?php else: ?>
        <span class="navbar-text">
            <i class="fas fa-circle text-danger"></i>
            <?php echo xlt('MedEx Offline'); ?>
        </span>
        <?php endif; ?>
    </div>
</nav>

<style>
.medex-navbar {
    margin-bottom: 20px;
    border-radius: 4px;
}

.medex-navbar .nav-link {
    color: #333;
}

.medex-navbar .nav-link:hover {
    color: #007bff;
}

.medex-navbar .navbar-text {
    font-size: 0.9rem;
}
</style>

<script>
// Override core SMS_direct function to use module entry point
function SMS_direct() {
    var pid = $("#sms_pid").val();
    var m = $("#sms_mobile").val();
    var allow = $("#sms_allow").val();
    if ((pid === "") || (m === "")) {
        alert(<?php echo xlj("MedEx needs a valid mobile number to send SMS messages..."); ?>);
    } else if (allow === "NO") {
        alert(<?php echo xlj("This patient does not allow SMS messaging!"); ?>);
    } else {
        // Use direct module entry point to avoid session issues
        var url = "<?php echo $webroot; ?>/interface/modules/custom_modules/oe-module-medex/public/sms_bot_list.php";
        var params = new URLSearchParams({
            pid: pid,
            m: m,
            nomenu: "1"
        });
        var features = "width=450,height=800,resizable=1,scrollbars=1";
        var win = window.open(url + "?" + params.toString(), "_blank", features);
        if (win) {
            win.focus();
        }
    }
}
</script>
