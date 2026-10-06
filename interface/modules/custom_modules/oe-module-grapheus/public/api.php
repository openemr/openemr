<?php

/**
 * Grapheus module API, called by panel.js and recorder.js inside OpenEMR.
 * Signed-in OpenEMR session + CSRF token on every call; the clinician's
 * Grapheus key stays here on the server.
 *
 *   GET  ?action=state                     connection, patient, encounter
 *   POST ?action=connect   {key, email}    after the Grapheus Connect window
 *   POST ?action=disconnect
 *   POST ?action=start     {patientType, prepMin, mode}
 *   POST ?action=segment&visit=&seq=       raw audio
 *   POST ?action=finish    {visit, pausedSecs, prepMin, tz, reason, patientType}
 *   GET  ?action=visits                    the clinician's Grapheus drafts (48 h)
 *   GET  ?action=status&visit=
 *   POST ?action=apply     {visit, soap, problems, allergies, prescriptions, diagnoses, billing}
 *
 * @package   Grapheus
 * @copyright Copyright (c) 2026 Exetazo Health
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once dirname(__FILE__, 5) . "/globals.php";
require_once dirname(__DIR__) . '/src/Compat.php';
require_once dirname(__FILE__, 5) . "/../library/forms.inc.php";

use Exetazo\Grapheus\Applier;
use Exetazo\Grapheus\Assistant;
use Exetazo\Grapheus\Client;
use Exetazo\Grapheus\Store;
use OpenEMR\Common\Acl\AclMain;
use Exetazo\Grapheus\Compat;
use OpenEMR\Core\ModulesClassLoader;

(new ModulesClassLoader(Compat::fileroot()))->registerNamespaceIfNotExists('Exetazo\\Grapheus\\', dirname(__DIR__) . '/src');

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function out(int $status, array $body): never
{
    http_response_code($status);
    echo json_encode($body);
    exit;
}

$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!Compat::csrfValid((string) $csrf)) {
    out(403, ['ok' => false, 'error' => 'Your OpenEMR session expired. Reload the page.']);
}
$action = $_GET['action'] ?? '';
$isAssistant = str_starts_with($action, 'assistant');
$role = Assistant::role();
if ($isAssistant) {
    if ($role === 'none') {
        out(403, ['ok' => false, 'error' => 'The practice has limited the Grapheus Assistant to administrators.']);
    }
} elseif (!AclMain::aclCheckCore('encounters', 'notes', '', 'write') && !AclMain::aclCheckCore('encounters', 'notes_a', '', 'write')) {
    out(403, ['ok' => false, 'error' => 'You do not have permission to write encounter notes.']);
}

$userId = (int) Compat::get('authUserID', 0);
$userName = (string) Compat::get('authUser', '');
$groupName = (string) Compat::get('authProvider', 'Default');
$authorized = (int) Compat::get('userauthorized', 0);
$pid = (int) Compat::get('pid', 0);
$encounter = (int) Compat::get('encounter', 0);
$method = $_SERVER['REQUEST_METHOD'];

// Long uploads must not hold the session lock (the rest of OpenEMR would freeze).
Compat::releaseSession();

$body = [];
if ($method === 'POST' && $action !== 'segment') {
    $body = json_decode((string) file_get_contents('php://input'), true) ?: [];
}

$conn = Store::key($userId);
$client = $conn ? new Client(Store::server(), $conn['key']) : null;
$needClient = function () use ($client) {
    if (!$client) {
        out(401, ['ok' => false, 'code' => 'connect', 'error' => 'Connect your Grapheus account first.']);
    }
    return $client;
};
$relay = function (array $r) {
    if ($r['status'] === 401) {
        out(401, ['ok' => false, 'code' => 'connect', 'error' => 'Your Grapheus connection has ended. Connect again.']);
    }
    out($r['status'] ?: 502, $r['body']);
};

switch ($action) {
    case 'state':
        $patient = $pid ? getPatientData($pid, 'fname, lname, DOB') : [];
        $me = null;
        if ($client) {
            $r = $client->call('GET', '/api/me', null, null, 15);
            if ($r['status'] === 401) {
                Store::forget($userId);
                $client = null;
            } elseif (!empty($r['body']['ok'])) {
                $me = ['email' => $r['body']['user']['email'] ?? '', 'plan' => $r['body']['access']['planLabel'] ?? '', 'access' => $r['body']['access'] ?? null, 'name' => $r['body']['profile']['fullName'] ?? ''];
            }
        }
        out(200, ['ok' => true, 'connected' => (bool) $client, 'account' => $me, 'server' => Store::server(),
            'pid' => $pid, 'encounter' => $encounter, 'patientName' => trim(($patient['fname'] ?? '') . ' ' . ($patient['lname'] ?? '')),
            'links' => array_values(Store::linksFor($pid, $encounter))]);

    case 'connect':
        $key = (string) ($body['key'] ?? '');
        if (!preg_match('/^sxk_[A-Za-z0-9_-]{30,}$/', $key)) {
            out(400, ['ok' => false, 'error' => 'That is not a Grapheus key.']);
        }
        $test = (new Client(Store::server(), $key))->call('GET', '/api/me', null, null, 15);
        if (empty($test['body']['ok'])) {
            out(400, ['ok' => false, 'error' => 'Grapheus did not accept the key.']);
        }
        Store::saveKey($userId, $key, (string) ($test['body']['user']['email'] ?? ''));
        out(200, ['ok' => true]);

    case 'disconnect':
        if ($client) {
            $client->call('POST', '/api/ext/disconnect', new stdClass(), null, 15);
        }
        Store::forget($userId);
        out(200, ['ok' => true]);

    case 'start':
        if (!$pid || !$encounter) {
            out(400, ['ok' => false, 'error' => 'Open the patient and the encounter first.']);
        }
        $patient = getPatientData($pid, 'fname, lname');
        $label = trim(($patient['fname'] ?? '') . ' ' . ($patient['lname'] ?? ''));
        $r = $needClient()->call('POST', '/api/visits/start', [
            'label' => $label, 'mode' => ($body['mode'] ?? '') === 'telehealth' ? 'telehealth' : 'in_person',
            'patientType' => ($body['patientType'] ?? '') === 'new' ? 'new' : 'established',
        ], null, 20);
        if (!empty($r['body']['ok']) && !empty($r['body']['id'])) {
            Store::link((string) $r['body']['id'], $pid, $encounter, $userId);
        }
        $relay($r);

    case 'segment':
        $visit = (string) ($_GET['visit'] ?? '');
        $link = Store::linkOf($visit);
        if (!$link || (int) $link['user_id'] !== $userId) {
            out(404, ['ok' => false, 'error' => 'Unknown recording.']);
        }
        $type = (string) ($_SERVER['CONTENT_TYPE'] ?? 'audio/webm');
        $audio = (string) file_get_contents('php://input');
        $relay($needClient()->call('POST', '/api/visits/segment?id=' . rawurlencode($visit) . '&seq=' . (int) ($_GET['seq'] ?? 0), $audio, $type, 600));

    case 'finish':
        $visit = (string) ($body['visit'] ?? '');
        $relay($needClient()->call('POST', '/api/visits/finish', [
            'id' => $visit, 'pausedSecs' => (int) ($body['pausedSecs'] ?? 0), 'prepMin' => (int) ($body['prepMin'] ?? 0),
            'tz' => (string) ($body['tz'] ?? ''), 'reason' => (string) ($body['reason'] ?? 'stopped'), 'patientType' => (string) ($body['patientType'] ?? ''),
        ], null, 60));

    case 'visits':
        $r = $needClient()->call('GET', '/api/visits', null, null, 20);
        $links = Store::linksFor($pid, $encounter);
        foreach ($r['body']['visits'] ?? [] as $i => $v) {
            $r['body']['visits'][$i]['thisEncounter'] = isset($links[$v['id']]);
            $r['body']['visits'][$i]['appliedAt'] = $links[$v['id']]['applied_at'] ?? null;
        }
        $relay($r);

    case 'status':
        $relay($needClient()->call('GET', '/api/visits/status?id=' . rawurlencode((string) ($_GET['visit'] ?? '')), null, null, 30));

    case 'apply':
        if (!$pid || !$encounter) {
            out(400, ['ok' => false, 'error' => 'Open the patient and the encounter first.']);
        }
        $visit = (string) ($body['visit'] ?? '');
        $link = Store::linkOf($visit);
        if ($link && !empty($link['applied_at'])) {
            out(409, ['ok' => false, 'error' => 'This draft was already added to a chart on ' . $link['applied_at'] . '.']);
        }
        // The draft must belong to this clinician's Grapheus account.
        $check = $needClient()->call('GET', '/api/visits/status?id=' . rawurlencode($visit), null, null, 30);
        if (empty($check['body']['ok']) || ($check['body']['visit']['status'] ?? '') !== 'ready') {
            out(400, ['ok' => false, 'error' => 'That draft is not ready or not yours.']);
        }
        try {
            $summary = (new Applier($pid, $encounter, $userId, $userName, $groupName, $authorized))->apply($body);
        } catch (\Throwable $e) {
            (new \OpenEMR\Common\Logging\SystemLogger())->error('Grapheus apply failed', ['error' => $e->getMessage()]);
            out(500, ['ok' => false, 'error' => 'Nothing was added: ' . $e->getMessage()]);
        }
        Store::markApplied($visit, $pid, $encounter, $userId, $summary);
        out(200, ['ok' => true, 'summary' => $summary]);

    // ------------------------------------------------------------ Grapheus Assistant
    case 'assistant-state':
        $practice = Store::practiceKey();
        out(200, ['ok' => true, 'role' => $role, 'practiceConnected' => (bool) $practice, 'practiceEmail' => $practice['email'] ?? '',
            'server' => Store::server(), 'adminsOnly' => Assistant::adminsOnly(), 'log' => $role === 'admin' ? Assistant::recent(30) : []]);

    case 'assistant-connect':
        if ($role !== 'admin') {
            out(403, ['ok' => false, 'error' => 'Only an administrator can connect the practice account.']);
        }
        $key = (string) ($body['key'] ?? '');
        $test = preg_match('/^sxk_[A-Za-z0-9_-]{30,}$/', $key) ? (new Client(Store::server(), $key))->call('GET', '/api/me', null, null, 15) : ['body' => []];
        if (empty($test['body']['ok'])) {
            out(400, ['ok' => false, 'error' => 'Grapheus did not accept the key.']);
        }
        Store::savePracticeKey($key, (string) ($test['body']['user']['email'] ?? ''));
        out(200, ['ok' => true]);

    case 'assistant-disconnect':
        if ($role !== 'admin') {
            out(403, ['ok' => false, 'error' => 'Only an administrator can do that.']);
        }
        Store::forgetPractice();
        out(200, ['ok' => true]);

    case 'assistant-settings':
        if ($role !== 'admin') {
            out(403, ['ok' => false, 'error' => 'Only an administrator can do that.']);
        }
        Assistant::setAdminsOnly(!empty($body['adminsOnly']));
        out(200, ['ok' => true]);

    case 'assistant-chat':
        $practice = Store::practiceKey();
        if (!$practice) {
            out(401, ['ok' => false, 'code' => 'connect', 'error' => 'An administrator needs to connect the practice\'s Grapheus account first.']);
        }
        $r = (new Client(Store::server(), $practice['key']))->call('POST', '/api/setup/chat', [
            'emr' => 'openemr', 'messages' => $body['messages'] ?? [], 'snapshot' => Assistant::snapshot($role, $userName),
        ], null, 180);
        if (!empty($r['body']['changes'])) {
            foreach ($r['body']['changes'] as $i => $c) {
                $r['body']['changes'][$i]['allowed'] = Assistant::allowed((string) ($c['op'] ?? ''), $role);
            }
        }
        $relay($r);

    case 'assistant-find-patient':
        if (!Assistant::allowed('create_appointment', $role)) {
            out(403, ['ok' => false, 'error' => 'Your role cannot schedule.']);
        }
        out(200, ['ok' => true, 'patients' => Assistant::findPatients((string) ($body['name'] ?? ''), (string) ($body['dob'] ?? ''))]);

    case 'assistant-apply':
        $results = [];
        $applied = 0;
        foreach (array_slice((array) ($body['changes'] ?? []), 0, 300) as $c) {
            try {
                $res = Assistant::apply((string) ($c['op'] ?? ''), (array) ($c['args'] ?? []), $role, $userId, isset($c['pid']) ? (int) $c['pid'] : null);
            } catch (\Throwable $e) {
                $res = ['ok' => false, 'error' => $e->getMessage()];
            }
            $applied += $res['ok'] ? 1 : 0;
            $results[] = $res;
        }
        $practice = Store::practiceKey();
        if ($practice && !empty($body['requestId'])) {
            (new Client(Store::server(), $practice['key']))->call('POST', '/api/setup/applied', ['requestId' => (int) $body['requestId'], 'applied' => $applied], null, 15);
        }
        out(200, ['ok' => true, 'results' => $results, 'applied' => $applied]);

    case 'assistant-undo':
        out(200, Assistant::undo((int) ($body['logId'] ?? 0), $role));

    default:
        out(404, ['ok' => false, 'error' => 'Unknown action.']);
}
