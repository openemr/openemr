<?php
declare(strict_types=1);

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
use OpenEMR\Core\Header;
use OpenEMR\Core\Kernel;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Reports\Email\EmailQueueService;

$globalsBag = OEGlobalsBag::getInstance();
/**
 * @var mixed $kernelValue
 */
$kernelValue = $globalsBag->get('kernel');
$kernel = $kernelValue instanceof Kernel ? $kernelValue : null;
$webrootValue = $globalsBag->get('webroot');
$webroot = is_string($webrootValue) ? $webrootValue : '';

// ACL check - requires billing or admin access
if (!AclMain::aclCheckCore('admin', 'super') && !AclMain::aclCheckCore('acct', 'bill')) {
    echo (new TwigContainer(null, $kernel))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl('Email Queue Report')]);
    exit;
}

// Initialize service
$service = new EmailQueueService();
/**
 * @param string $name
 * @return string
 */
$getStringFilter = static function (string $name): string {
    $value = filter_input(INPUT_GET, $name, FILTER_UNSAFE_RAW);
    return is_string($value) ? $value : '';
};

// Get filters from request
$filters = [
    'search' => $getStringFilter('search'),
    'status' => $getStringFilter('status'),
    'template_name' => $getStringFilter('template_name'),
    'date_from' => $getStringFilter('date_from'),
    'date_to' => $getStringFilter('date_to'),
];

// Pagination
$perPage = 50;
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);
$currentPage = is_int($page) ? $page : 1;
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
    if ($value !== '') {
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
    'webroot' => $webroot,
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
        $twig = new TwigContainer(null, $kernel);
        echo $twig->getTwig()->render('reports/email/queue.html.twig', $templateVars);
        ?>
    </div>
</body>
</html>
