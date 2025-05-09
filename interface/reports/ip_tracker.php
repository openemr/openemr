<?php

/**
 * IP tracker user interface.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2023 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Auth\AuthUtils;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;


if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"], 'ip_tracker')) {
        CsrfUtils::csrfNotVerified();
    }
}

if (!AclMain::aclCheckCore('admin', 'super')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("IP Tracker")]);
    exit;
}

if (!empty($_POST['showOnlyWithCount'])) {
    $showOnlyWithCount = true;
} else {
    $showOnlyWithCount = false;
}

if (!empty($_POST['showOnlyManuallyBlocked'])) {
    $showOnlyManuallyBlocked = true;
} else {
    $showOnlyManuallyBlocked = false;
}

if (!empty($_POST['showOnlyAutoBlocked'])) {
    $showOnlyAutoBlocked = true;
} else {
    $showOnlyAutoBlocked = false;
}

?>
<html>

<head>
    <title><?php echo xlt('IP Tracker'); ?></title>

    <?php Header::setupHeader(["report-helper"]); ?>

    <script>
        $(function () {
            var win = top.printLogSetup ? top : opener.top;
            win.printLogSetup(document.getElementById('printbutton'));
        });

        function refreshme() {
            document.forms[0].submit();
        }

        function manualBlock(ipId) {
            let func = "";
            if (document.getElementById("manual-block-" + ipId).checked) {
                func = "disableIp"
            } else {
                func = "enableIp"
            }
            top.restoreSession();
            request = new FormData;
            request.append("function", func);
            request.append("ipId", ipId);
            request.append("csrf_token_form", <?php echo js_escape(CsrfUtils::collectCsrfToken('counter')); ?>);
            fetch("<?php echo $GLOBALS["webroot"]; ?>/library/ajax/login_counter_ip_tracker.php", {
                method: 'POST',
                credentials: 'same-origin',
                body: request
            });
        }

        function skipTiming(ipId) {
            let func = "";
            if (document.getElementById("timing-skip-" + ipId).checked) {
                func = "skipTiming"
            } else {
                func = "noSkipTiming"
            }
            top.restoreSession();
            request = new FormData;
            request.append("function", func);
            request.append("ipId", ipId);
            request.append("csrf_token_form", <?php echo js_escape(CsrfUtils::collectCsrfToken('counter')); ?>);
            fetch("<?php echo $GLOBALS["webroot"]; ?>/library/ajax/login_counter_ip_tracker.php", {
                method: 'POST',
                credentials: 'same-origin',
                body: request
            });
        }

        function resetCounterIp(ipId) {
            top.restoreSession();
            request = new FormData;
            request.append("function", "resetIpCounter");
            request.append("ipId", ipId);
            request.append("csrf_token_form", <?php echo js_escape(CsrfUtils::collectCsrfToken('counter')); ?>);
            fetch("<?php echo $GLOBALS["webroot"]; ?>/library/ajax/login_counter_ip_tracker.php", {
                method: 'POST',
                credentials: 'same-origin',
                body: request
            });
            let failCounterElement = document.getElementById('fail-counter-' + ipId);
            failCounterElement.innerHTML = "0";
            let lastFailElement = document.getElementById('last-fail-' + ipId);
            lastFailElement.innerHTML = jsText(xl("Not Applicable"));
            let autoblockElement = document.getElementById('autoblock-' + ipId);
            autoblockElement.innerHTML = jsText(xl("No"));
        }

    </script>

    <style>
        /* specifically include & exclude from printing */
        @media print {
            #report_parameters {
                visibility: hidden;
                display: none;
            }
            #report_results table {
                margin-top: 0px;
            }
        }
    </style>
</head>

<body class="body_top">

<span class='title'><?php echo xlt('IP Tracker'); ?></span>

<form method='post' name='theform' id='theform' action='ip_tracker.php' onsubmit='return top.restoreSession()'>
    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken('ip_tracker')); ?>" />

    <div id="report_parameters">

        <table>
            <tr>
                <td width='650px'>
                    <div style='float: left'>

                        <table class='text'>
                            <tr>
                                <td>
                                    <div class="checkbox">
                                        <label>
                                            <input type='checkbox' id='showOnlyWithCount' name='showOnlyWithCount' <?php echo ($showOnlyWithCount) ? ' checked' : ''; ?>> <?php echo xlt('Show Only With Applicable Failed Logins'); ?>
                                        </label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="checkbox">
                                        <label>
                                            <input type='checkbox' id='showManuallyBlocked' name='showOnlyManuallyBlocked' <?php echo ($showOnlyManuallyBlocked) ? ' checked' : ''; ?>> <?php echo xlt('Show Only Manually Blocked'); ?>
                                        </label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="checkbox">
                                        <label>
                                            <input type='checkbox' id='showAutoBlocked' name='showOnlyAutoBlocked' <?php echo ($showOnlyAutoBlocked) ? ' checked' : ''; ?>> <?php echo xlt('Show Only Auto Blocked'); ?>
                                        </label>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
                <td class='h-100' align='left' valign='middle'>
                    <table class='w-100 h-100' style='border-left: 1px solid;'>
                        <tr>
                            <td>
                                <div class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href='#' class='btn btn-secondary btn-save' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'>
                                            <?php echo xlt('Submit'); ?>
                                        </a>
                                        <?php if (!empty($_POST['form_refresh'])) { ?>
                                            <a href='#' class='btn btn-secondary btn-print' id='printbutton'>
                                                <?php echo xlt('Print'); ?>
                                            </a>
                                        <?php } ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

    </div>
    <!-- end of search parameters --> <?php
    if (!empty($_POST['form_refresh'])) {
        ?>
        <div id="report_results">
            <table class='table'>

                <thead class='thead-light'>
                <th><?php echo xlt('IP String'); ?></th>
                <th><?php echo xlt('Total Failed Logins'); ?></th>
                <th><?php echo xlt('Applicable Failed Logins'); ?></th>
                <th><?php echo xlt('Last Failed Login'); ?></th>
                <th><?php echo xlt('Auto Blocked'); ?></th>
                <th><?php echo xlt('Manual Settings'); ?></th>
                </thead>
                <tbody>
                <!-- added for better print-ability -->
                <?php
                $ipLoginFailsSql = AuthUtils::collectIpLoginFailsSql($showOnlyWithCount, $showOnlyManuallyBlocked, $showOnlyAutoBlocked);

                while ($row = SqlFetchArray($ipLoginFailsSql)) {
                    ?>

                    <tr valign='top' bgcolor='<?php echo attr($bgcolor ?? ''); ?>'>
                        <td class="detail"><?php echo text($row['ip_string']); ?></td>
                        <td class="detail"><?php echo text($row['total_ip_login_fail_counter']); ?></td>
                        <td class="detail" id="fail-counter-<?php echo attr($row['id']) ?>">
                            <?php
                            echo text($row['ip_login_fail_counter']);
                            if ($row['ip_login_fail_counter'] > 0) {
                                echo '<button type="button" class="btn btn-sm btn-danger ml-2" onclick="resetCounterIp(' . attr_js($row["id"]) . ')">' . xlt("Reset Counter") . '</button>';
                            }
                            ?>
                        </td>
                        <td class="detail" id="last-fail-<?php echo attr($row['id']) ?>"><?php echo (!empty($row['ip_last_login_fail'])) ? text(oeFormatDateTime($row['ip_last_login_fail'])) : xlt("Not Applicable"); ?></td>
                        <td class="detail" id="autoblock-<?php echo attr($row['id']) ?>">
                            <?php
                            $autoBlocked = false;
                            $autoBlockEnd = null;
                            if ((int)$GLOBALS['ip_max_failed_logins'] != 0 && ($row['ip_login_fail_counter'] > (int)$GLOBALS['ip_max_failed_logins'])) {
                                if ((int)$GLOBALS['ip_time_reset_password_max_failed_logins'] != 0) {
                                    if ($row['seconds_last_ip_login_fail'] < (int)$GLOBALS['ip_time_reset_password_max_failed_logins']) {
                                        $autoBlocked = true;
                                        $autoBlockEnd = date('Y-m-d H:i:s', (time() + ((int)$GLOBALS['ip_time_reset_password_max_failed_logins'] - $row['seconds_last_ip_login_fail'])));
                                    }
                                } else {
                                    $autoBlocked = true;
                                }
                            }
                            if ($autoBlocked) {
                                echo xlt("Yes");
                                if (!empty($autoBlockEnd)) {
                                    echo ' (' . xlt("Autoblock ends on") . ' ' . text(oeFormatDateTime($autoBlockEnd)) . ')';
                                }
                            } else {
                                echo xlt("No");
                            }
                            ?>
                        </td>
                        <td class="detail">
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input type="checkbox" class="form-check-input" id="manual-block-<?php echo attr($row['id']) ?>" title="<?php echo xla('Manually block this IP address'); ?>" onclick="manualBlock(<?php echo attr_js($row['id']) ?>)" <?php echo ($row['ip_force_block']) ? "checked" : ""; ?>><?php echo xlt('Manual Block'); ?>
                                </label>
                            </div>
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input type="checkbox" class="form-check-input" id="timing-skip-<?php echo attr($row['id']) ?>" title="<?php echo xla('Skip the Timing Attack Prevention'); ?>" onclick="skipTiming(<?php echo attr_js($row['id']) ?>)" <?php echo ($row['ip_no_prevent_timing_attack']) ? "checked" : ""; ?>><?php echo xlt('Skip Timing'); ?>
                                </label>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
        <!-- end of search results -->
    <?php } else { ?>
        <div class='text'><?php echo xlt('Please select search criteria above, and click Submit to view results. If criteria(s) are not selected, then will show all.'); ?>
        </div>
    <?php } ?>
    <input type='hidden' name='form_refresh' id='form_refresh' value='' /></form>

</body>

</html>
