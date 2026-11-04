<?php

/**
 * Child-process fixture for PatientSessionUtil::requireCsrfForLinkSwitch().
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\PatientSessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

require_once dirname(__DIR__, 6) . '/vendor/autoload.php';

// Keep the real xlt() response path while preventing this isolated
// child process from attempting a translation database lookup.
$GLOBALS['disable_translation'] = true;

$requestedPid = filter_var($argv[1] ?? null, FILTER_VALIDATE_INT);
if ($requestedPid === false) {
    fwrite(STDERR, "Expected the requested patient ID.\n");
    exit(2);
}

$session = new Session(new MockArraySessionStorage());
$session->set('pid', 11);
CsrfUtils::setupCsrfKey($session);
SessionWrapperFactory::getInstance()->setActiveSession($session);

$query = ['set_pid' => $requestedPid];
if (($argv[2] ?? '') === 'with-token') {
    $query['csrf_token_form'] = CsrfUtils::collectCsrfToken($session);
}

PatientSessionUtil::requireCsrfForLinkSwitch(Request::create('/demographics.php', 'GET', $query), $requestedPid);
echo 'allowed';
