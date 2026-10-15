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
 * Markup lives in templates/reports/portal_lockout_tracker/report.html.twig;
 * this file reads the filter, fetches the rows, decorates each row with the
 * pre-computed auto-block state, and hands the whole payload off to Twig.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once("../globals.php");

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;

$request = CurrentRequest::get();
$session = SessionWrapperFactory::getInstance()->getActiveSession();

if ($request->isMethod('POST')) {
    CsrfUtils::checkCsrfInput(INPUT_POST, subject: 'portal_lockout_tracker', dieOnFail: true);
}

if (!AclMain::aclCheckCore('admin', 'super')) {
    AccessDeniedHelper::denyWithTemplate(
        "ACL check failed for admin/super: Portal Lockout Tracker",
        xl("Portal Lockout Tracker")
    );
}

$showOnlyAutoBlocked = $request->request->getBoolean('showOnlyAutoBlocked');
$formRefresh = $request->request->getString('form_refresh') !== '';

$maxFailed = OEGlobalsBag::getInstance()->getInt('password_max_failed_logins');
$resetWindow = OEGlobalsBag::getInstance()->getInt('time_reset_password_max_failed_logins');

/**
 * @var list<array{
 *     pid: int,
 *     portal_login_username: string,
 *     portal_fail_counter: int,
 *     portal_last_fail: ?string,
 *     autoBlocked: bool,
 *     autoBlockEnd: ?string
 * }> $rows
 */
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
    /** @var list<array<string, mixed>> $rawRows */
    $rawRows = QueryUtils::fetchRecords(
        "SELECT `pid`, `portal_login_username`, `portal_fail_counter`, `portal_last_fail`, "
            . "TIMESTAMPDIFF(SECOND, `portal_last_fail`, NOW()) AS `seconds_last_portal_fail` "
            . "FROM `patient_access_onsite` WHERE " . implode(' AND ', $whereFragments) . " "
            . "ORDER BY `portal_last_fail` DESC, `portal_fail_counter` DESC",
        $bindings
    );
    foreach ($rawRows as $raw) {
        $counter = is_numeric($raw['portal_fail_counter'] ?? null) ? (int) $raw['portal_fail_counter'] : 0;
        $lastFail = is_string($raw['portal_last_fail'] ?? null) ? $raw['portal_last_fail'] : null;
        $seconds = is_numeric($raw['seconds_last_portal_fail'] ?? null) ? (int) $raw['seconds_last_portal_fail'] : null;

        // Portal per-account gate is `>= password_max_failed_logins` and only
        // expires when elapsed seconds are strictly greater than the window,
        // see AuthUtils::isPortalAccountBlocked(). Match both boundaries here
        // (>= on counter, <= on seconds) so the display doesn't disagree with
        // the actual block.
        $autoBlocked = false;
        $autoBlockEnd = null;
        if ($maxFailed !== 0 && $counter >= $maxFailed) {
            if ($resetWindow !== 0) {
                if ($seconds !== null && $seconds <= $resetWindow) {
                    $autoBlocked = true;
                    $autoBlockEnd = date('Y-m-d H:i:s', time() + ($resetWindow - $seconds));
                }
            } else {
                $autoBlocked = true;
            }
        }

        $rows[] = [
            'pid' => is_numeric($raw['pid'] ?? null) ? (int) $raw['pid'] : 0,
            'portal_login_username' => is_string($raw['portal_login_username'] ?? null) ? $raw['portal_login_username'] : '',
            'portal_fail_counter' => $counter,
            'portal_last_fail' => $lastFail,
            'autoBlocked' => $autoBlocked,
            'autoBlockEnd' => $autoBlockEnd,
        ];
    }
}

echo ServiceContainer::getTwig()->render('reports/portal_lockout_tracker/report.html.twig', [
    'showOnlyAutoBlocked' => $showOnlyAutoBlocked,
    'formRefresh' => $formRefresh,
    'rows' => $rows,
    'formCsrfToken' => CsrfUtils::collectCsrfToken($session, 'portal_lockout_tracker'),
    'counterCsrfToken' => CsrfUtils::collectCsrfToken($session, 'counter'),
    'ajaxUrl' => OEGlobalsBag::getInstance()->getWebRoot() . '/library/ajax/login_counter_ip_tracker.php',
]);
