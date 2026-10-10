<?php

/**
 * Grapheus module API, called by the module's own pages inside OpenEMR.
 * Signed-in OpenEMR session + CSRF token on every call; Grapheus keys stay on
 * the server.
 *
 *   Scribe (clinicians):  state, connect, disconnect, start, segment, finish, visits, status, apply
 *   Assistant (by role):  assistant-state, assistant-connect, assistant-disconnect, assistant-settings,
 *                         assistant-chat, assistant-find-patient, assistant-apply, assistant-undo
 *
 * @package   Grapheus
 * @copyright Copyright (c) 2026 Exetazo Health
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once dirname(__FILE__, 5) . "/globals.php";
require_once dirname(__DIR__) . '/src/Compat.php';

use Exetazo\Grapheus\Applier;
use Exetazo\Grapheus\Assistant;
use Exetazo\Grapheus\Client;
use Exetazo\Grapheus\Compat;
use Exetazo\Grapheus\Http;
use Exetazo\Grapheus\Store;
use Exetazo\Grapheus\Val;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\ModulesClassLoader;

(new ModulesClassLoader(Compat::fileroot()))->registerNamespaceIfNotExists('Exetazo\\Grapheus\\', dirname(__DIR__) . '/src');

$request = Compat::request();
if (!Compat::csrfValid(Val::str($request->headers->get('X-CSRF-Token')))) {
    Http::json(403, ['ok' => false, 'error' => 'Your OpenEMR session expired. Reload the page.']);
}
$action = Val::str($request->query->get('action'));
$role = Assistant::role();
if (str_starts_with($action, 'assistant')) {
    if ($role === 'none') {
        Http::json(403, ['ok' => false, 'error' => 'The practice has limited the Grapheus Assistant to administrators.']);
    }
} elseif (!AclMain::aclCheckCore('encounters', 'notes', '', 'write') && !AclMain::aclCheckCore('encounters', 'notes_a', '', 'write')) {
    Http::json(403, ['ok' => false, 'error' => 'You do not have permission to write encounter notes.']);
}

$userId = Val::int(Compat::get('authUserID', 0));
$userName = Val::str(Compat::get('authUser', ''));
$groupName = Val::str(Compat::get('authProvider', 'Default'));
$authorized = Val::int(Compat::get('userauthorized', 0));
$pid = Val::int(Compat::get('pid', 0));
$encounter = Val::int(Compat::get('encounter', 0));

// Long uploads must not hold the session lock (the rest of OpenEMR would wait).
Compat::releaseSession();

$body = [];
if ($request->getMethod() === 'POST' && $action !== 'segment') {
    $body = Val::map(json_decode((string) $request->getContent(), true));
}

$conn = Store::key($userId);
$client = $conn !== null ? new Client(Store::server(), $conn['key']) : null;
$needClient = static function () use ($client): Client {
    if ($client === null) {
        Http::json(401, ['ok' => false, 'code' => 'connect', 'error' => 'Connect your Grapheus account first.']);
    }
    return $client;
};
$practiceClient = static function (): ?Client {
    $practice = Store::practiceKey();
    return $practice !== null ? new Client(Store::server(), $practice['key']) : null;
};
$adminOnly = static function () use ($role): void {
    if ($role !== 'admin') {
        Http::json(403, ['ok' => false, 'error' => 'Only an administrator can do that.']);
    }
};
$acceptKey = static function (string $key): string {
    if (preg_match('/^sxk_[A-Za-z0-9_-]{30,}$/', $key) !== 1) {
        Http::json(400, ['ok' => false, 'error' => 'That is not a Grapheus key.']);
    }
    $test = (new Client(Store::server(), $key))->call('GET', '/api/me', null, null, 15);
    if (($test['body']['ok'] ?? false) !== true) {
        Http::json(400, ['ok' => false, 'error' => 'Grapheus did not accept the key.']);
    }
    return Val::str(Val::map($test['body']['user'] ?? null)['email'] ?? '');
};
$patientName = static function (int $pid): string {
    if ($pid <= 0) {
        return '';
    }
    $p = Val::map(getPatientData($pid, 'fname, lname'));
    return trim(Val::str($p['fname'] ?? '') . ' ' . Val::str($p['lname'] ?? ''));
};

switch ($action) {
    case 'state':
        $account = null;
        if ($client !== null) {
            $r = $client->call('GET', '/api/me', null, null, 15);
            if ($r['status'] === 401) {
                Store::forget($userId);
                $client = null;
            } elseif (($r['body']['ok'] ?? false) === true) {
                $access = Val::map($r['body']['access'] ?? null);
                $account = ['email' => Val::str(Val::map($r['body']['user'] ?? null)['email'] ?? ''), 'plan' => Val::str($access['planLabel'] ?? ''), 'access' => $access];
            }
        }
        Http::json(200, ['ok' => true, 'connected' => $client !== null, 'account' => $account, 'server' => Store::server(),
            'pid' => $pid, 'encounter' => $encounter, 'patientName' => $patientName($pid), 'links' => array_values(Store::linksFor($pid, $encounter))]);

    case 'connect':
        $key = Val::str($body['key'] ?? '');
        $email = $acceptKey($key);
        Store::saveKey($userId, $key, $email);
        Http::json(200, ['ok' => true]);

    case 'disconnect':
        $client?->call('POST', '/api/ext/disconnect', [], null, 15);
        Store::forget($userId);
        Http::json(200, ['ok' => true]);

    case 'start':
        if ($pid <= 0 || $encounter <= 0) {
            Http::json(400, ['ok' => false, 'error' => 'Open the patient and the encounter first.']);
        }
        $r = $needClient()->call('POST', '/api/visits/start', [
            'label' => $patientName($pid),
            'mode' => Val::str($body['mode'] ?? '') === 'telehealth' ? 'telehealth' : 'in_person',
            'patientType' => Val::str($body['patientType'] ?? '') === 'new' ? 'new' : 'established',
        ], null, 20);
        $visitId = Val::str($r['body']['id'] ?? '');
        if (($r['body']['ok'] ?? false) === true && $visitId !== '') {
            Store::link($visitId, $pid, $encounter, $userId);
        }
        Http::relay($r);

    case 'segment':
        $visit = Val::str($request->query->get('visit'));
        $link = Store::linkOf($visit);
        if ($link === null || Val::int($link['user_id'] ?? 0) !== $userId) {
            Http::json(404, ['ok' => false, 'error' => 'Unknown recording.']);
        }
        $type = Val::str($request->headers->get('Content-Type', 'audio/webm'));
        $path = '/api/visits/segment?id=' . rawurlencode($visit) . '&seq=' . Val::int($request->query->get('seq'));
        Http::relay($needClient()->call('POST', $path, (string) $request->getContent(), $type, 600));

    case 'finish':
        Http::relay($needClient()->call('POST', '/api/visits/finish', [
            'id' => Val::str($body['visit'] ?? ''), 'pausedSecs' => Val::int($body['pausedSecs'] ?? 0), 'prepMin' => Val::int($body['prepMin'] ?? 0),
            'tz' => Val::str($body['tz'] ?? '', 60), 'reason' => Val::str($body['reason'] ?? 'stopped', 60), 'patientType' => Val::str($body['patientType'] ?? '', 20),
        ], null, 60));

    case 'visits':
        $r = $needClient()->call('GET', '/api/visits', null, null, 20);
        $links = Store::linksFor($pid, $encounter);
        $visits = [];
        foreach (Val::maps($r['body']['visits'] ?? null) as $v) {
            $id = Val::str($v['id'] ?? '');
            $v['thisEncounter'] = isset($links[$id]);
            $v['appliedAt'] = $links[$id]['applied_at'] ?? null;
            $visits[] = $v;
        }
        $r['body']['visits'] = $visits;
        Http::relay($r);

    case 'status':
        Http::relay($needClient()->call('GET', '/api/visits/status?id=' . rawurlencode(Val::str($request->query->get('visit'))), null, null, 30));

    case 'apply':
        if ($pid <= 0 || $encounter <= 0) {
            Http::json(400, ['ok' => false, 'error' => 'Open the patient and the encounter first.']);
        }
        $visit = Val::str($body['visit'] ?? '');
        $link = Store::linkOf($visit);
        if ($link !== null && (Val::int($link['pid'] ?? 0) !== $pid || Val::int($link['encounter'] ?? 0) !== $encounter)) {
            Http::json(409, ['ok' => false, 'error' => 'This draft was recorded in a different patient or encounter. Open that encounter to add it.']);
        }
        if ($link !== null && Val::str($link['applied_at'] ?? '') !== '') {
            Http::json(409, ['ok' => false, 'error' => 'This draft was already added to a chart on ' . Val::str($link['applied_at']) . '.']);
        }
        // The draft must belong to this clinician's Grapheus account and be ready.
        $check = $needClient()->call('GET', '/api/visits/status?id=' . rawurlencode($visit), null, null, 30);
        if (($check['body']['ok'] ?? false) !== true || Val::str(Val::map($check['body']['visit'] ?? null)['status'] ?? '') !== 'ready') {
            Http::json(400, ['ok' => false, 'error' => 'That draft is not ready or not yours.']);
        }
        try {
            $summary = (new Applier($pid, $encounter, $userId, $userName, $groupName, $authorized))->apply($body);
        } catch (\RuntimeException $e) {
            Http::json(500, ['ok' => false, 'error' => 'Nothing was added: ' . $e->getMessage()]);
        }
        Store::markApplied($visit, $pid, $encounter, $userId, $summary);
        Http::json(200, ['ok' => true, 'summary' => $summary]);

    // ------------------------------------------------------------ Grapheus Assistant
    case 'assistant-state':
        $practice = Store::practiceKey();
        Http::json(200, ['ok' => true, 'role' => $role, 'practiceConnected' => $practice !== null, 'practiceEmail' => $practice['email'] ?? '',
            'server' => Store::server(), 'adminsOnly' => Assistant::adminsOnly(), 'log' => $role === 'admin' ? Assistant::recent(30) : []]);

    case 'assistant-connect':
        $adminOnly();
        $key = Val::str($body['key'] ?? '');
        $email = $acceptKey($key);
        Store::savePracticeKey($key, $email);
        Http::json(200, ['ok' => true]);

    case 'assistant-disconnect':
        $adminOnly();
        Store::forgetPractice();
        Http::json(200, ['ok' => true]);

    case 'assistant-settings':
        $adminOnly();
        Assistant::setAdminsOnly(Val::bool($body['adminsOnly'] ?? false));
        Http::json(200, ['ok' => true]);

    case 'assistant-chat':
        $pc = $practiceClient();
        if ($pc === null) {
            Http::json(401, ['ok' => false, 'code' => 'connect', 'error' => "An administrator needs to connect the practice's Grapheus account first."]);
        }
        $r = $pc->call('POST', '/api/setup/chat', [
            'emr' => 'openemr', 'messages' => Val::maps($body['messages'] ?? null), 'snapshot' => Assistant::snapshot($role, $userName),
        ], null, 180);
        $changes = [];
        foreach (Val::maps($r['body']['changes'] ?? null) as $c) {
            $c['allowed'] = Assistant::allowed(Val::str($c['op'] ?? ''), $role);
            $changes[] = $c;
        }
        $r['body']['changes'] = $changes;
        Http::relay($r);

    case 'assistant-find-patient':
        if (!Assistant::allowed('create_appointment', $role)) {
            Http::json(403, ['ok' => false, 'error' => 'Your role cannot schedule.']);
        }
        Http::json(200, ['ok' => true, 'patients' => Assistant::findPatients(Val::str($body['name'] ?? ''), Val::str($body['dob'] ?? ''))]);

    case 'assistant-apply':
        $results = [];
        $applied = 0;
        foreach (array_slice(Val::maps($body['changes'] ?? null), 0, 300) as $c) {
            $chosen = Val::int($c['pid'] ?? 0);
            try {
                $res = Assistant::apply(Val::str($c['op'] ?? ''), Val::map($c['args'] ?? null), $role, $userId, $chosen > 0 ? $chosen : null);
            } catch (\RuntimeException $e) {
                $res = ['ok' => false, 'error' => $e->getMessage()];
            }
            $applied += $res['ok'] ? 1 : 0;
            $results[] = $res;
        }
        $requestId = Val::int($body['requestId'] ?? 0);
        if ($requestId > 0) {
            $practiceClient()?->call('POST', '/api/setup/applied', ['requestId' => $requestId, 'applied' => $applied], null, 15);
        }
        Http::json(200, ['ok' => true, 'results' => $results, 'applied' => $applied]);

    case 'assistant-undo':
        Http::json(200, Assistant::undo(Val::int($body['logId'] ?? 0), $role));

    default:
        Http::json(404, ['ok' => false, 'error' => 'Unknown action.']);
}
