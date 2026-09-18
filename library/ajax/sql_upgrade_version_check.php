<?php

/**
 * sql_upgrade_version_check.php
 *
 * Lightweight backup completion signal for sql_upgrade.php's status polling.
 *
 * Returns the current DB version as "v_major.v_minor.v_patch". sql_upgrade.php's
 * JS compares that against its captured-at-page-load INITIAL_VERSION and
 * TARGET_VERSION constants; completion is inferred when the returned value
 * differs from the initial version AND matches the target (see the version-
 * check block in sql_upgrade.php).
 *
 * sql_upgrade.php's last DB write is $versionService->update(), so the
 * version-table columns atomically reflect "upgrade complete." This survives
 * every failure mode that can affect the main serverStatus() polling loop's
 * termination signal (streaming chunk delivery through output buffering,
 * poll endpoint self-noise from globals.php bootstrap queries polluting the
 * "no DB activity" signal, etc.).
 *
 * Called on a JS setInterval() from sql_upgrade.php's page, independent of
 * the main polling loop. Whichever mechanism notices completion first ends
 * the polling display.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

$ignoreAuth = true;
// Match sql_server_status.php's bootstrap posture -- keys table may be in a
// partial state during upgrade, connection pooling would confuse the per-
// request lifecycle, and per-poll audit log INSERTs are pure noise. These
// flags gate behavior inside globals.php itself so they must be set as
// $GLOBALS entries before globals.php loads and the autoloader is available
// (matches sql_server_status.php pattern).
// @phpstan-ignore openemr.forbiddenGlobalsAccess (Required before globals.php loads the autoloader)
$GLOBALS['ongoing_sql_upgrade'] = true;
// @phpstan-ignore openemr.forbiddenGlobalsAccess (Required before globals.php loads the autoloader)
$GLOBALS['connection_pooling_off'] = true;
$skipAuditLog = true;
require_once(__DIR__ . '/../../interface/globals.php');

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;

$session = SessionWrapperFactory::getInstance()->getActiveSession();
// Same guard as sql_server_status.php -- only sql_upgrade.php's page should
// be able to invoke this endpoint (CSRF token minted there).
CsrfUtils::checkCsrfInput(INPUT_POST, subject: 'sqlupgrade', dieOnFail: true);

header('Cache-Control: no-cache');
header('Content-Type: text/plain; charset=utf-8');

$row = QueryUtils::querySingleRow("SELECT v_major, v_minor, v_patch FROM version LIMIT 1");
if (
    !is_array($row)
    || !is_string($row['v_major'] ?? null)
    || !is_string($row['v_minor'] ?? null)
    || !is_string($row['v_patch'] ?? null)
) {
    // Should not happen on a properly-installed OpenEMR (globals.php would
    // have failed before us if the DB were not reachable). Empty response
    // keeps the client-side comparison safely in the "not complete" state.
    echo '';
    exit();
}
echo $row['v_major'] . '.' . $row['v_minor'] . '.' . $row['v_patch'];
