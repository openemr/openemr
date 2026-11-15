<?php

/**
 * Child-process fixture for the TherapyGroupSessionUtil guards, which exit.
 *
 * Arguments: the guard (`switch` or `write`), the requested group ID, and
 * the token to send (`with-token`, `array-token`, or none).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Common\Session\TherapyGroupSessionUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

// @codeCoverageIgnoreStart
// TherapyGroupSessionUtilTest runs this script in a child process, which
// coverage does not instrument, so none of its lines can register as hit.
require_once dirname(__DIR__, 6) . '/vendor/autoload.php';

// Keep the real xlt() response path while preventing this isolated
// child process from attempting a translation database lookup.
$GLOBALS['disable_translation'] = true;

$guard = $argv[1] ?? null;
if ($guard !== 'switch' && $guard !== 'write') {
    throw new InvalidArgumentException('Expected the guard to run: switch or write');
}
$requestedGroupArg = $argv[2] ?? null;
if (!is_string($requestedGroupArg) || !ctype_digit($requestedGroupArg)) {
    throw new InvalidArgumentException('Expected the requested group ID');
}
$requestedGroupId = (int) $requestedGroupArg;

$session = new Session(new MockArraySessionStorage());
$session->set('pid', 11);
CsrfUtils::setupCsrfKey($session);
SessionWrapperFactory::getInstance()->setActiveSession($session);

$query = ['method' => 'groupDetails', 'group_id' => $requestedGroupId];
$query['csrf_token_form'] = match ($argv[3] ?? '') {
    'with-token' => CsrfUtils::collectCsrfToken($session),
    'array-token' => [CsrfUtils::collectCsrfToken($session)],
    default => null,
};
$request = Request::create('/therapy_groups/index.php', 'GET', $query);

// Report the session patient and group after the guard, even when it
// exits, so the test can see that a refused request changed nothing.
register_shutdown_function(static function () use ($session): void {
    $pid = $session->get('pid');
    $group = $session->get('therapy_group');
    echo ' pid=' . (is_int($pid) ? $pid : 'none') . ' group=' . (is_int($group) ? $group : 'none');
});

if ($guard === 'write') {
    TherapyGroupSessionUtil::requireCsrfToken($request);
    echo 'allowed';
    exit;
}

TherapyGroupSessionUtil::requireCsrfForGroupSwitch($request, $requestedGroupId);
// Stand in for TherapyGroupsController::setSession() once the guard lets it through.
$session->set('pid', 0);
$session->set('therapy_group', $requestedGroupId);
echo 'allowed';
// @codeCoverageIgnoreEnd
