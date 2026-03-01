<?php

/**
 * Email Queue Report
 * Built with Warp-Terminal
 * Displays email queue status and history with search and filtering capabilities
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Generated for PoppyBilling
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Reports\Email\EmailQueueService;
use OpenEMR\Core\Header;

// ACL check - requires billing or admin access
if (!AclMain::aclCheckCore('admin', 'super') && !AclMain::aclCheckCore('acct', 'bill')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl('Email Queue Report')]);
    exit;
}

// Initialize service
$service = new EmailQueueService();

// Get filters from request
$filters = [
    'search' => $_GET['search'] ?? '',
    'status' => $_GET['status'] ?? '',
    'template_name' => $_GET['template_name'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
];

// Pagination
$perPage = 50;
$currentPage = (int)($_GET['page'] ?? 1);
$offset = ($currentPage - 1) * $perPage;

// Get data
$statistics = $service->getStatistics();
$templateNames = $service->getTemplateNames();
$emails = $service->getEmailQueue($filters, $perPage, $offset);
$totalCount = $service->getEmailQueueCount($filters);
$totalPages = ceil($totalCount / $perPage);

// Build query string for pagination
$queryParams = [];
foreach ($filters as $key => $value) {
    if (!empty($value)) {
        $queryParams[] = urlencode($key) . '=' . urlencode($value);
    }
}
$queryString = implode('&', $queryParams);

// Prepare template variables
$templateVars = [
    'statistics' => $statistics,
    'templateNames' => $templateNames,
    'emails' => $emails,
    'filters' => $filters,
    'totalCount' => $totalCount,
    'currentPage' => $currentPage,
    'totalPages' => $totalPages,
    'queryString' => $queryString,
    'webroot' => $GLOBALS['webroot'],
];

// Render page
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('Email Queue Report'); ?></title>
    <?php Header::setupHeader(['datetime-picker']); ?>
</head>
<body class="body_top">
    <div class="container-fluid">
        <?php
        $twig = new TwigContainer(null, $GLOBALS['kernel']);
        echo $twig->getTwig()->render('reports/email/queue.html.twig', $templateVars);
        ?>
    </div>
</body>
</html>
