<?php

/**
 * Child-process fixture for TherapyGroupSessionUtil::requireCsrfForGroupSwitch().
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

require_once dirname(__DIR__, 6) . '/vendor/autoload.php';

// Keep the real xlt() response path while preventing this isolated
// child process from attempting a translation database lookup.
$GLOBALS['disable_translation'] = true;

$requestedGroupArg = $argv[1] ?? null;
if (!is_string($requestedGroupArg) || !ctype_digit($requestedGroupArg)) {
    throw new InvalidArgumentException('Expected the requested group ID');
}
$requestedGroupId = (int) $requestedGroupArg;

$session = new Session(new MockArraySessionStorage());
$session->set('pid', 11);
CsrfUtils::setupCsrfKey($session);
SessionWrapperFactory::getInstance()->setActiveSession($session);

$query = ['method' => 'groupDetails', 'group_id' => $requestedGroupId];
$query['csrf_token_form'] = match ($argv[2] ?? '') {
    'with-token' => CsrfUtils::collectCsrfToken($session),
    'array-token' => [CsrfUtils::collectCsrfToken($session)],
    default => null,
};

// Report the session patient and group after the guard, even when it
// exits, so the test can see that a refused switch left the chart open.
register_shutdown_function(static function () use ($session): void {
    $pid = $session->get('pid');
    $group = $session->get('therapy_group');
    echo ' pid=' . (is_int($pid) ? $pid : 'none') . ' group=' . (is_int($group) ? $group : 'none');
});

TherapyGroupSessionUtil::requireCsrfForGroupSwitch(Request::create('/therapy_groups/index.php', 'GET', $query), $requestedGroupId);
// Stand in for TherapyGroupsController::setSession() once the guard lets it through.
$session->set('pid', 0);
$session->set('therapy_group', $requestedGroupId);
echo 'allowed';
