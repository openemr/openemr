<?php

/**
 * Portal Lockout Tracker admin UI.
 *
 * Companion to interface/reports/ip_tracker.php for the per-portal-account
 * axis: lists patient_access_onsite rows with a non-zero portal_fail_counter
 * (i.e. an in-progress lockout against a specific portal account) and lets
 * an admin clear the counter manually. Unlike the per-IP / per-user axes,
 * this counter is keyed by portal_login_username and does not share a UI
 * with any pre-existing report — it is the primary recovery surface when
 * clear_ip_counter_on_auth_success is off (or the reset window is 0) and
 * a legitimate portal user has locked themselves out.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once("../globals.php");

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\Utils\DateFormatterUtils;

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$request = CurrentRequest::get();

if ($request->isMethod('POST')) {
    CsrfUtils::checkCsrfInput(INPUT_POST, subject: 'portal_lockout_tracker', dieOnFail: true);
}

if (!AclMain::aclCheckCore('admin', 'super')) {
    AccessDeniedHelper::denyWithTemplate("ACL check failed for admin/super: Portal Lockout Tracker", xl("Portal Lockout Tracker"));
}

$showOnlyAutoBlocked = $request->request->getBoolean('showOnlyAutoBlocked');
$formRefresh = $request->request->getString('form_refresh') !== '';

$maxFailed = OEGlobalsBag::getInstance()->getInt('password_max_failed_logins');
$resetWindow = OEGlobalsBag::getInstance()->getInt('time_reset_password_max_failed_logins');

/** @var list<array{pid: int, portal_login_username: string, portal_fail_counter: int, portal_last_fail: ?string, seconds_last_portal_fail: ?int}> $rows */
$rows = [];
if ($formRefresh) {
    // Only surface rows with an active counter — a zero-counter row on
    // patient_access_onsite is not a lockout candidate, and the table holds
    // every registered portal user, so a bare SELECT would list the entire
    // portal roster.
    $whereFragments = [' (`portal_fail_counter` > 0) '];
    $bindings = [];
    if ($showOnlyAutoBlocked) {
        if ($maxFailed !== 0) {
            $whereFragments[] = ' (`portal_fail_counter` >= ?) ';
            $bindings[] = $maxFailed;
            if ($resetWindow > 0) {
                // isPortalAccountBlocked() expires the block only when
                // elapsed seconds are strictly greater than $window, so the
                // account is still blocked at seconds == window; use <= to
                // keep the filter aligned with the gate.
                $whereFragments[] = ' (TIMESTAMPDIFF(SECOND, `portal_last_fail`, NOW()) <= ?) ';
                $bindings[] = $resetWindow;
            }
        } else {
            // Auto-block is globally disabled, so no row can be auto-blocked.
            // Return an empty set rather than the whole active-counter list,
            // which the renderer would uniformly label "No" and mislead the
            // admin.
            $whereFragments[] = ' 1 = 0 ';
        }
    }
    $where = implode(' AND ', $whereFragments);
    /** @var list<array<string, mixed>> $rawRows */
    $rawRows = QueryUtils::fetchRecords(
        "SELECT `pid`, `portal_login_username`, `portal_fail_counter`, `portal_last_fail`, "
            . "TIMESTAMPDIFF(SECOND, `portal_last_fail`, NOW()) AS `seconds_last_portal_fail` "
            . "FROM `patient_access_onsite` WHERE " . $where . " "
            . "ORDER BY `portal_last_fail` DESC, `portal_fail_counter` DESC",
        $bindings
    );
    foreach ($rawRows as $raw) {
        $rows[] = [
            'pid' => is_numeric($raw['pid'] ?? null) ? (int) $raw['pid'] : 0,
            'portal_login_username' => is_string($raw['portal_login_username'] ?? null)
                ? $raw['portal_login_username']
                : '',
            'portal_fail_counter' => is_numeric($raw['portal_fail_counter'] ?? null)
                ? (int) $raw['portal_fail_counter']
                : 0,
            'portal_last_fail' => is_string($raw['portal_last_fail'] ?? null)
                ? $raw['portal_last_fail']
                : null,
            'seconds_last_portal_fail' => is_numeric($raw['seconds_last_portal_fail'] ?? null)
                ? (int) $raw['seconds_last_portal_fail']
                : null,
        ];
    }
}

/**
 * Whether a row is currently auto-blocked given the configured thresholds.
 * Matches AuthUtils::isPortalAccountBlocked(): counter >= max, and (window == 0
 * → always blocked once at cap) or (elapsed seconds within the window).
 */
$computeAutoBlockEnd = (static fn(int $seconds): string => date('Y-m-d H:i:s', time() + ($resetWindow - $seconds)));

?>
<html>

<head>
    <title><?php echo xlt('Portal Lockout Tracker'); ?></title>

    <?php Header::setupHeader(["report-helper"]); ?>

    <script>
        $(function () {
            var win = top.printLogSetup ? top : opener.top;
            win.printLogSetup(document.getElementById('printbutton'));
        });

        function resetPortalCounter(portalLoginUsername) {
            top.restoreSession();
            request = new FormData;
            request.append("function", "resetPortalAccountCounter");
            request.append("portalLoginUsername", portalLoginUsername);
            request.append("csrf_token_form", <?php echo js_escape(CsrfUtils::collectCsrfToken($session, 'counter')); ?>);
            fetch("<?php echo OEGlobalsBag::getInstance()->getWebRoot(); ?>/library/ajax/login_counter_ip_tracker.php", {
                method: 'POST',
                credentials: 'same-origin',
                body: request
            });
            // getElementById takes a raw id, not a CSS selector, so the raw
            // portal_login_username is concatenated as-is; CSS.escape() would
            // add backslashes before dots/@ and break lookups for the common
            // case of an email-address portal username.
            let counterEl = document.getElementById('portal-fail-counter-' + portalLoginUsername);
            if (counterEl) {
                counterEl.innerHTML = "0";
            }
            let lastFailEl = document.getElementById('portal-last-fail-' + portalLoginUsername);
            if (lastFailEl) {
                lastFailEl.innerHTML = jsXlt("Not Applicable");
            }
            let autoBlockEl = document.getElementById('portal-autoblock-' + portalLoginUsername);
            if (autoBlockEl) {
                autoBlockEl.innerHTML = jsXlt("No");
            }
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

<span class='title'><?php echo xlt('Portal Lockout Tracker'); ?></span>

<form method='post' name='theform' id='theform' action='portal_lockout_tracker.php' onsubmit='return top.restoreSession()'>
    <input type="hidden" name="csrf_token_form" value="<?php echo CsrfUtils::collectCsrfToken($session, 'portal_lockout_tracker'); ?>" />

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
                                            <input type='checkbox' id='showOnlyAutoBlocked' name='showOnlyAutoBlocked' <?php echo ($showOnlyAutoBlocked) ? ' checked' : ''; ?>> <?php echo xlt('Show Only Auto Blocked'); ?>
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
                                        <?php if ($formRefresh) { ?>
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
    <!-- end of search parameters -->
    <?php if ($formRefresh) { ?>
        <div id="report_results">
            <table class='table'>
                <thead class='thead-light'>
                    <th><?php echo xlt('Portal Login Username'); ?></th>
                    <th><?php echo xlt('Patient ID'); ?></th>
                    <th><?php echo xlt('Failed Login Counter'); ?></th>
                    <th><?php echo xlt('Last Failed Login'); ?></th>
                    <th><?php echo xlt('Auto Blocked'); ?></th>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row) {
                        // Portal per-account gate is `>= password_max_failed_logins` and only expires when
                        // elapsed seconds are strictly greater than the window — see
                        // AuthUtils::isPortalAccountBlocked(). Match both boundaries here (>= on counter,
                        // <= on seconds) so the display doesn't disagree with the actual block.
                        $portalAutoBlocked = false;
                        $portalAutoBlockEnd = null;
                        if ($maxFailed !== 0 && $row['portal_fail_counter'] >= $maxFailed) {
                            if ($resetWindow !== 0) {
                                $seconds = $row['seconds_last_portal_fail'];
                                if ($seconds !== null && $seconds <= $resetWindow) {
                                    $portalAutoBlocked = true;
                                    $portalAutoBlockEnd = $computeAutoBlockEnd($seconds);
                                }
                            } else {
                                $portalAutoBlocked = true;
                            }
                        }
                        ?>
                        <tr valign='top'>
                            <td class="detail"><?php echo text($row['portal_login_username']); ?></td>
                            <td class="detail"><?php echo text((string) $row['pid']); ?></td>
                            <td class="detail" id="portal-fail-counter-<?php echo attr($row['portal_login_username']); ?>">
                                <?php
                                echo text((string) $row['portal_fail_counter']);
                                if ($row['portal_fail_counter'] > 0) {
                                    echo '<button type="button" class="btn btn-sm btn-danger ml-2" onclick="resetPortalCounter(' . attr_js($row['portal_login_username']) . ')">' . xlt("Reset Counter") . '</button>';
                                }
                                ?>
                            </td>
                            <td class="detail" id="portal-last-fail-<?php echo attr($row['portal_login_username']); ?>"><?php echo ($row['portal_last_fail'] !== null) ? text(DateFormatterUtils::oeFormatDateTime($row['portal_last_fail'])) : xlt("Not Applicable"); ?></td>
                            <td class="detail" id="portal-autoblock-<?php echo attr($row['portal_login_username']); ?>">
                                <?php
                                if ($portalAutoBlocked) {
                                    echo xlt("Yes");
                                    if ($portalAutoBlockEnd !== null) {
                                        echo ' (' . xlt("Autoblock ends on") . ' ' . text(DateFormatterUtils::oeFormatDateTime($portalAutoBlockEnd)) . ')';
                                    }
                                } else {
                                    echo xlt("No");
                                }
                                ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <!-- end of search results -->
    <?php } else { ?>
        <div class='text'><?php echo xlt('Please click Submit to view results.'); ?></div>
    <?php } ?>
    <input type='hidden' name='form_refresh' id='form_refresh' value='' />
</form>

</body>

</html>
