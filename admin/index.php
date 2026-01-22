<?php

/**
 * Multi Site Administration Front Controller (Authenticated)
 *
 * Lightweight controller for displaying all configured sites and their status.
 * Requires authentication via admin login.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (C) 2010 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2019 Ranganath Pathak <pathak@scrs1.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Admin\AdminAuthService;
use OpenEMR\Admin\Services\ConnectionPoolManager;
use OpenEMR\Admin\Services\SiteConfigLoader;
use OpenEMR\Admin\Services\SiteDiscoveryService;
use OpenEMR\Admin\Services\SiteInfoService;
use OpenEMR\Admin\Services\SiteVersionReader;
use OpenEMR\Admin\ValueObjects\ConnectionConfig;
use OpenEMR\Admin\Exceptions\SiteAdminException;
use OpenEMR\Common\Twig\TwigContainer;

// Check PHP compatibility
require_once(dirname(__FILE__) . "/../src/Common/Compatibility/Checker.php");
$response = OpenEMR\Common\Compatibility\Checker::checkPhpVersion();
if ($response !== true) {
    die(htmlspecialchars($response));
}

// Set session to allow write
$ignoreAuth = true;
$sessionAllowWrite = true;

// Include globals to initialize the system
require_once(dirname(__FILE__) . "/../interface/globals.php");

// Ensure site_id is set to default
$_SESSION['site_id'] = 'default';

// Initialize authentication service
$authService = new AdminAuthService();

// Check if user is authenticated
if (!$authService->isAuthenticated() || !$authService->checkSessionTimeout()) {
    // Not authenticated or session timeout, redirect to login
    header('Location: login.php');
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    $authService->logout();
    header('Location: login.php');
    exit;
}

// Load version
require_once dirname(__FILE__) . "/../version.php";

$webserver_root = dirname(__FILE__) . "/..";
if (stripos(PHP_OS, 'WIN') === 0) {
    $webserver_root = str_replace("\\", "/", $webserver_root);
}

// Initialize the OpenEMR kernel to enable Twig
$kernel = $GLOBALS['kernel'] ?? null;

if ($kernel === null && file_exists("$webserver_root/src/Core/Kernel.php")) {
    require_once "$webserver_root/src/Core/Kernel.php";
    $kernel = new \OpenEMR\Core\Kernel();
    $GLOBALS['kernel'] = $kernel;
}

$OE_SITES_BASE = "$webserver_root/sites";

// Initialize services with dependency injection
$discovery = new SiteDiscoveryService($OE_SITES_BASE);
$configLoader = new SiteConfigLoader();
$connectionManager = new ConnectionPoolManager(new ConnectionConfig());
$versionReader = new SiteVersionReader(dirname(__FILE__) . "/../version.php");
$siteInfoService = new SiteInfoService($discovery, $configLoader, $connectionManager, $versionReader);

// Get all sites information with exception handling
try {
    $sites = $siteInfoService->getAllSitesInfo();
    $sitesInfo = array_map(fn($site) => $site->toArray(), $sites);
} catch (SiteAdminException $e) {
    error_log('Site administration error: ' . $e->getMessage());
    $sitesInfo = [];
} finally {
    $connectionManager->closeAllConnections();
}

// Check if user can add new sites (admin users only)
$showAddSiteButton = $authService->isAuthenticated();

// Prepare template variables
$templateVars = [
    'sites' => $sitesInfo,
    'show_add_site_button' => $showAddSiteButton,
    'webroot' => $GLOBALS['webroot'] ?? $webserver_root,
    'authenticated' => true,
    'username' => $_SESSION['authUser'] ?? 'Admin',
];

// Render with Twig
if ($kernel !== null) {
    try {
        $twig = new TwigContainer(null, $kernel);
        echo $twig->getTwig()->render('admin/dashboard.html.twig', $templateVars);
        exit;
    } catch (\Exception $e) {
        error_log("Twig rendering failed: " . $e->getMessage());
        // Fall back to inline HTML below
    }
}

// Fallback inline HTML (if Twig fails)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars('OpenEMR Site Administration'); ?></title>
    <link rel="stylesheet" href="../public/assets/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../public/assets/@fortawesome/fontawesome-free/css/all.min.css">
    <link rel="shortcut icon" href="../public/images/favicon.ico" />
</head>
<body>
    <div class="container-fluid">
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-3">
            <span class="navbar-brand">OpenEMR Multi-Site Administration</span>
            <div class="ml-auto">
                <span class="navbar-text text-white mr-3">
                    <i class="fa fa-user"></i> <?php echo htmlspecialchars($_SESSION['authUser'] ?? 'Admin'); ?>
                </span>
                <a href="?logout=1" class="btn btn-outline-light btn-sm">
                    <i class="fa fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </nav>

        <div class="container mt-3">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2><?php echo htmlspecialchars('Configured Sites'); ?></h2>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="table-responsive">
                        <table class="table table-striped text-center">
                            <thead>
                                <tr>
                                    <th><?php echo htmlspecialchars('Site ID'); ?></th>
                                    <th><?php echo htmlspecialchars('DB Name'); ?></th>
                                    <th><?php echo htmlspecialchars('Site Name'); ?></th>
                                    <th><?php echo htmlspecialchars('Version'); ?></th>
                                    <th><?php echo htmlspecialchars('Is Current'); ?></th>
                                    <th><?php echo htmlspecialchars('Log In'); ?></th>
                                    <th><?php echo htmlspecialchars('Patient Portal'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sitesInfo as $site): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($site['site_id']); ?></td>
                                        <td><?php echo htmlspecialchars($site['db_name']); ?></td>

                                        <?php if ($site['needs_setup']): ?>
                                            <td colspan="3">
                                                <a href="../setup.php?site=<?php echo urlencode($site['site_id']); ?>" class="text-decoration-none">
                                                    <?php echo htmlspecialchars('Needs setup, click here to run it'); ?>
                                                </a>
                                            </td>
                                        <?php elseif ($site['error']): ?>
                                            <td colspan="3" class="text-danger">
                                                <?php echo htmlspecialchars($site['error']); ?>
                                            </td>
                                        <?php else: ?>
                                            <td><?php echo htmlspecialchars($site['site_name']); ?></td>
                                            <td><?php echo htmlspecialchars($site['version']); ?></td>

                                            <?php if ($site['requires_upgrade']): ?>
                                                <td>
                                                    <a href="../sql_upgrade.php?site=<?php echo urlencode($site['site_id']); ?>" class="text-decoration-none">
                                                        <?php echo htmlspecialchars('Upgrade Required'); ?>
                                                    </a>
                                                </td>
                                            <?php else: ?>
                                                <td>
                                                    <i class="fa fa-check fa-lg text-success" aria-hidden="true"></i>
                                                </td>
                                            <?php endif; ?>

                                            <?php if ($site['is_current']): ?>
                                                <td>
                                                    <a href="../interface/login/login.php?site=<?php echo urlencode($site['site_id']); ?>" class="text-decoration-none">
                                                        <i class="fa fa-sign-in-alt fa-lg" aria-hidden="true"></i>
                                                    </a>
                                                </td>
                                                <td>
                                                    <a href="../portal/index.php?site=<?php echo urlencode($site['site_id']); ?>" class="text-decoration-none">
                                                        <i class="fa fa-sign-in-alt fa-lg" aria-hidden="true"></i>
                                                    </a>
                                                </td>
                                            <?php else: ?>
                                                <td>
                                                    <i class="fa fa-ban fa-lg text-secondary" aria-hidden="true"></i>
                                                </td>
                                                <td>
                                                    <i class="fa fa-ban fa-lg text-secondary" aria-hidden="true"></i>
                                                </td>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($showAddSiteButton): ?>
                        <form method="post" action="../setup.php">
                            <button type="submit" class="btn btn-primary font-weight-bold" name="form_submit" value="Add New Site">
                                <?php echo htmlspecialchars('Add New Site'); ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="../public/assets/jquery/dist/jquery.min.js"></script>
    <script src="../public/assets/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
// Ensure connections are closed
$service->closeAllConnections();
?>
