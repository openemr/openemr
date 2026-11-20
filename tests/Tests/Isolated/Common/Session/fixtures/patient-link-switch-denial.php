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

$requestedPidArg = $argv[1] ?? null;
if (!is_string($requestedPidArg) || !ctype_digit($requestedPidArg)) {
    throw new InvalidArgumentException('Expected the requested patient ID');
}
$requestedPid = (int) $requestedPidArg;

$session = new Session(new MockArraySessionStorage());
$session->set('pid', 11);
CsrfUtils::setupCsrfKey($session);
SessionWrapperFactory::getInstance()->setActiveSession($session);

$query = ['set_pid' => $requestedPid];
$query['csrf_token_form'] = match ($argv[2] ?? '') {
    'with-token' => CsrfUtils::collectCsrfToken($session),
    'array-token' => [CsrfUtils::collectCsrfToken($session)],
    default => null,
};

// Report the session patient after the guard, even when it exits, so the
// test can see that a refused switch left the chart alone.
register_shutdown_function(static function () use ($session): void {
    $pid = $session->get('pid');
    echo ' pid=' . (is_int($pid) ? $pid : 'none');
});

PatientSessionUtil::requireCsrfForLinkSwitch(Request::create('/demographics.php', 'GET', $query), $requestedPid);
// Stand in for demographics.php's setpid() once the guard lets it through.
$session->set('pid', $requestedPid);
echo 'allowed';
