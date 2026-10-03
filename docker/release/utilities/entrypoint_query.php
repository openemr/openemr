<?php

/**
 * CLI for docker entrypoints: sqlconf, version, and ini queries.
 *
 * Usage:
 *   php entrypoint_query.php is-configured <sqlconf.php>
 *   php entrypoint_query.php config-flag <sqlconf.php>
 *   php entrypoint_query.php schema-version <version.php>
 *   php entrypoint_query.php export-sqlconf <sqlconf.php>
 *   php entrypoint_query.php ini-get <ini.key>
 *
 * src/Common/Docker/entrypoint_query.php is the canonical copy; each image
 * carries a byte-identical copy under docker/<image>/utilities/. See
 * EntrypointQuery.php for why, and keep all four in step.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @copyright Copyright (c) 2026 Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once __DIR__ . '/EntrypointQuery.php';

use OpenEMR\Common\Docker\EntrypointQuery;

$command = $argv[1] ?? '';
$arg = $argv[2] ?? '';

switch ($command) {
    case 'is-configured':
        echo (string) EntrypointQuery::isConfigured($arg);
        break;
    case 'config-flag':
        echo EntrypointQuery::configFlag($arg);
        break;
    case 'schema-version':
        echo (string) EntrypointQuery::schemaVersion($arg);
        break;
    case 'export-sqlconf':
        echo EntrypointQuery::exportSqlconf($arg);
        break;
    case 'ini-get':
        echo EntrypointQuery::iniGet($arg);
        break;
    default:
        fwrite(STDERR, "Unknown command. Use is-configured, config-flag, schema-version, export-sqlconf, or ini-get.\n");
        exit(2);
}
