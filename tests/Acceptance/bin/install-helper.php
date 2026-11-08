<?php

/**
 * Auto-install helper for the openemr acceptance-package workflow.
 *
 * Runs inside the flex image container (via `docker compose exec`) against
 * a bind-mounted openemr source tree (an extracted release tarball).
 * Instantiates the OpenEMR Installer class and calls quick_install()
 * with defaults matching what docker/release/auto_configure.php sets for
 * the production Docker image — same result, but standalone so we don't
 * depend on:
 *   1. `docker/release/` being present in the tarball (it isn't;
 *      .gitattributes marks docker/ as export-ignore)
 *   2. The flex image's `/root/devtools dev-install` machinery (which
 *      expects a /root/auto_configure.php that the flex image's
 *      openemr.sh removes at boot for security when EMPTY=yes)
 *
 * SECURITY: this file is mounted OUTSIDE the Apache document root by
 * acceptance-package-compose.yml (at /opt/openemr-acceptance-helper.php,
 * not inside /var/www/localhost/htdocs/openemr/). Apache cannot serve
 * it. The CLI guard below is defense-in-depth: even if someone drops
 * this file into a document root by accident, it refuses to execute
 * over HTTP.
 *
 * Approved DI exception (per CodeRabbit rule
 * openemr.forbiddenInstantiation): this is a standalone acceptance-
 * harness bootstrap. Directly constructs the Installer service +
 * resolves the logger via ServiceContainer::getLogger() because
 * there's no framework container in scope here — the script is
 * invoked as `php install-helper.php` under CLI, not through the
 * app's normal request lifecycle.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

// CLI-only guard. Non-CLI invocations (via web server, if the file
// somehow ends up in a document root) are refused. Matches the
// defense-in-depth pattern openemr's auto_configure.php uses.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("install-helper.php is a CLI-only bootstrap; refusing non-CLI invocation\n");
}

// Opt-in env guard. Mirrors the pattern in
// contrib/util/installScripts/InstallerAuto.php — refuses to run under
// CLI unless the caller explicitly sets the enable variable, so a stray
// `php install-helper.php` from an unrelated shell can't reinstall an
// already-installed openemr and wipe its DB. boot-package.sh sets this
// before invoking `docker compose exec`.
if (!getenv('OPENEMR_ENABLE_ACCEPTANCE_HELPER')) {
    fwrite(STDERR, "install-helper.php: refusing to run without OPENEMR_ENABLE_ACCEPTANCE_HELPER=1\n");
    exit(1);
}

// Autoload from the mounted openemr tree (release tarballs include
// vendor/ pre-built by PackageAssembler at release time). Path is
// container-absolute — only exists inside the flex container at
// execution time. This file is excluded from phpstan analysis via
// excludePaths.analyseAndScan in phpstan.neon.dist.
require_once '/var/www/localhost/htdocs/openemr/vendor/autoload.php';

// Installer lives at the root namespace in library/classes/Installer.class.php,
// autoloaded via composer's classmap. Same class docker/release/auto_configure.php
// uses.
use OpenEMR\BC\ServiceContainer;

$installSettings = [
    // Admin user
    'iuser' => 'admin',
    'iuname' => 'Administrator',
    'iuserpass' => 'pass',
    'igroup' => 'Default',
    // Database — the compose stack's `mysql` service is the host
    'server' => 'mysql',
    // loginhost='%' (wildcard) so the openemr user can connect from
    // any host — the openemr container's IP isn't localhost from
    // mysql's perspective. Matches what flex image's
    // devtoolsLibrary.source::prepareVariables() sets.
    'loginhost' => '%',
    'port' => '3306',
    'root' => 'root',
    'rootpass' => 'root',
    'login' => 'openemr',
    'pass' => 'openemr',
    'dbname' => 'openemr',
    'collate' => 'utf8mb4_general_ci',
    'site' => 'default',
    // Empty string (not 'BLANK' — auto_configure.php's sentinel that
    // it converts to '' before calling Installer).
    'source_site_id' => '',
    'clone_database' => '',
    'no_root_db_access' => '',
    'development_translations' => '',
];

$installer = new \Installer($installSettings, ServiceContainer::getLogger());

if (!$installer->quick_install()) {
    fwrite(STDERR, "install-helper.php: ERROR: " . $installer->error_message . "\n");
    exit(1);
}

echo $installer->debug_message . "\n";
echo "install-helper.php: install complete\n";
