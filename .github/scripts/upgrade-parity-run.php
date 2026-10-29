<?php

/**
 * Run an upgrade file over the parity_upgraded database with OpenEMR's own
 * SQLUpgradeService. Called by upgrade-parity.sh.
 *
 * OpenEMR boots against the database sites/default/sqlconf.php names (the
 * fresh one), then the connection switches with USE. ADODB and QueryUtils
 * share that one connection; both are checked before the upgrade starts.
 *
 * Usage:
 *   php .github/scripts/upgrade-parity-run.php sql/X_Y_Z-to-A_B_C_upgrade.sql
 *
 * Exit codes:
 *   0  The upgrade file ran.
 *   2  Bad arguments, an upgrade file that can't be read, or the connection
 *      was not on parity_upgraded. An SQL error in the upgrade file throws,
 *      which also exits non-zero.
 * A web request gets a 403 and nothing else.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\Utils\SQLUpgradeService;

// It boots OpenEMR with $ignoreAuth and runs SQL against a database, so it never
// runs from a web request, whatever the server config lets through.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("upgrade-parity-run.php runs only from the command line\n");
}

$upgradeFile = isset($argv[1]) ? realpath($argv[1]) : false;
// SQLUpgradeService only logs a file it can't open and returns, which would read as a clean run.
if ($upgradeFile === false || !is_file($upgradeFile) || !is_readable($upgradeFile)) {
    fwrite(STDERR, "usage: php upgrade-parity-run.php <upgrade file>\n");
    exit(2);
}

$ignoreAuth = true;
$sessionAllowWrite = true;
require_once __DIR__ . '/../../interface/globals.php';

QueryUtils::sqlStatementThrowException('USE `parity_upgraded`', [], true);
foreach ([sqlQueryNoLog('SELECT DATABASE() AS d'), QueryUtils::querySingleRow('SELECT DATABASE() AS d')] as $row) {
    if (!is_array($row) || ($row['d'] ?? null) !== 'parity_upgraded') {
        fwrite(STDERR, "upgrade-parity-run: the connection is not on parity_upgraded\n");
        exit(2);
    }
}

// As sql_upgrade.php does on the command line: no per-line browser progress, which with output
// off would otherwise be kept in the service's buffer for every line of the file.
OEGlobalsBag::getInstance()->set('force_simple_sql_upgrade', true);
$service = new SQLUpgradeService();
$service->setRenderOutputToScreen(false);
$service->setThrowExceptionOnError(true);
$service->upgradeFromSqlFile(basename($upgradeFile), dirname($upgradeFile));
