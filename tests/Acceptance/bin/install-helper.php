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
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

// Autoload from the mounted openemr tree (release tarballs include
// vendor/ pre-built by PackageAssembler at release time).
require_once '/var/www/localhost/htdocs/openemr/vendor/autoload.php';

// The Installer class lives at the root namespace in
// library/classes/Installer.class.php, autoloaded via composer's
// classmap. Same class docker/release/auto_configure.php uses.
//
// Logger obtained via ServiceContainer::getLogger() (project
// convention — a custom PHPStan rule
// `openemr.forbiddenInstantiation` rejects `new SystemLogger()`).
use OpenEMR\BC\ServiceContainer;

$installSettings = [
    // Admin user
    'iuser' => 'admin',
    'iuname' => 'Administrator',
    'iuserpass' => 'pass',
    'igroup' => 'Default',
    // Database — the compose stack's `mysql` service is the host
    'server' => 'mysql',
    // loginhost = '%' (wildcard) so the openemr user can connect from
    // any host — including the openemr container's IP, which isn't
    // localhost from mysql's perspective. Matches what flex image's
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
    // Advanced options unused for standard installs. Passed as empty
    // strings (not 'BLANK' as auto_configure.php uses — that's a sentinel
    // auto_configure.php converts to '' before calling Installer; here
    // we skip the sentinel and pass '' directly).
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
