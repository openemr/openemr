#!/usr/bin/env python3
"""Execute actual entry points with isolated core/SQL fixtures; no clinical database."""
import json
from pathlib import Path
import shutil
import subprocess
import tempfile

FORM = Path(__file__).resolve().parents[2] / 'interface/forms/eye_mag'
BOOTSTRAP = r'''<?php
namespace {
    $fixture = json_decode(getenv('EYE_TEST_CASE'), true);
    $_GET = $fixture['get'] ?? [];
    $_POST = $fixture['post'] ?? [];
    $_REQUEST = array_merge($_GET, $_POST);
    $_SERVER['REQUEST_METHOD'] = $fixture['method'] ?? 'POST';
    $pid = $fixture['patient'] ?? 10;
    $encounter = 20;
    $calls = [];
    $status = 200;
    class BoundaryStop extends \RuntimeException {}
    function xl($value) { return $value; }
    function xlt($value) { return $value; }
    function text($value) { return (string) $value; }
    function prevSetting(...$args) { return ''; }
    function queryFixture($sql, $params = []) {
        global $calls, $fixture;
        $calls[] = ['sql' => $sql, 'params' => $params];
        if (strpos($sql, 'SELECT pid, encounter FROM forms') !== false) {
            return $fixture['owner'] ?? ['pid' => 10, 'encounter' => 20];
        }
        if (str_contains($sql, 'FROM form_eye_dotphrases WHERE id=')) {
            return ['id' => 9, 'owner_uid' => $fixture['phrase_owner'] ?? 7];
        }
        if (strpos($sql, 'form_eye_mag_prefs') !== false && str_starts_with($sql, 'SELECT')) return false;
        if (str_contains(strtolower($sql), 'from forms,form_encounter')) {
            if (empty($fixture['rx_mutation'])) throw new BoundaryStop('patient query reached');
            return [];
        }
        if (str_contains($sql, 'SELECT pid FROM form_eye_mag_dispense')) return $fixture['rx_owner'] ?? null;
        return [];
    }
    function sqlStatement($sql, $params = []) { return queryFixture($sql, $params); }
    function sqlQuery($sql, $params = []) { return queryFixture($sql, $params); }
    function sqlFetchArray($result) { return false; }
    function oeFormatShortDate($value) { return ''; }
    register_shutdown_function(function () {
        global $calls;
        echo "\nEYE_TEST_RESULT=" . json_encode(['status' => http_response_code() ?: 200, 'calls' => $calls]);
    });
    if (($fixture['authenticated'] ?? true) === false) {
        http_response_code(401);
        exit('Core authentication rejected request');
    }
}
namespace OpenEMR\Common\Acl {
    class AclMain {
        public static function aclCheckCore($section, $value, $unused = '', $access = null) {
            global $fixture;
            if ($section === 'admin') return $fixture['admin'] ?? false;
            if (($fixture['acl'] ?? 'write') === 'none') return false;
            if (($fixture['acl'] ?? 'write') === 'addonly') return is_array($access) && in_array('addonly', $access, true);
            return true;
        }
    }
    class AccessDeniedHelper {
        public static function deny(...$args) { http_response_code(403); throw new \BoundaryStop('access denied'); }
        public static function denyWithTemplate(...$args) { self::deny(); }
    }
}
namespace OpenEMR\Common\Session {
    class SessionWrapperFactory {
        public static function getInstance() { return new self(); }
        public function getActiveSession() { return $this; }
        public function get($key, $default = null) {
            global $fixture;
            return ['authUserID' => 7, 'authUser' => 'fixture', 'pid' => $fixture['patient'] ?? 10, 'encounter' => 20][$key] ?? $default;
        }
    }
    class PatientSessionUtil {
        public static function getPid() { global $fixture; return $fixture['patient'] ?? 10; }
    }
}
namespace OpenEMR\Common\Csrf {
    class CsrfUtils {
        public static function verifyCsrfToken($token, $session, $subject = 'default') { return $token === 'fixture-valid'; }
        public static function collectCsrfToken($session, $subject = 'default') { return 'fixture-valid'; }
        public static function checkCsrfInput($inputType, $session = null, $key = 'csrf_token_form', $subject = 'default', $dieOnFail = false) {
            if (!self::verifyCsrfToken($_POST[$key] ?? '', $session)) self::csrfNotVerified();
        }
        public static function csrfNotVerified() { http_response_code(403); throw new \BoundaryStop('csrf denied'); }
    }
}
namespace OpenEMR\Common\Database {
    class QueryUtils {
        public static function querySingleRow($sql, $params = []) { return \queryFixture($sql, $params); }
        public static function sqlStatementThrowException($sql, $params = []) { return \queryFixture($sql, $params); }
        public static function getLastInsertId() { return 123; }
        public static function fetchSingleValue($sql, $column, $params = []) { return \queryFixture($sql, $params); }
    }
}
namespace OpenEMR\Core {
    class OEGlobalsBag {
        public static function getInstance() { return new self(); }
        public function getSrcDir() { return dirname(__DIR__) . '/library'; }
    }
}
namespace OpenEMR\Services { class FacilityService { public function getPrimaryBusinessEntity() { return []; } } }
namespace OpenEMR\BC {
    class ServiceContainer {
        public static function getLogger() { return new self(); }
        public function warning(...$args) {}
    }
}
namespace OpenEMR\Common\Logging {
    class EventAuditLogger {
        public static function getInstance() { return new self(); }
        public function newEvent(...$args) {}
    }
}
namespace OpenEMR\Common\Http {
    class CurrentRequest { public static function get() { return \Symfony\Component\HttpFoundation\Request::createFromGlobals(); } }
    class RequestTerminator {
        public function error($status, $message) { http_response_code($status); throw new \BoundaryStop($message); }
    }
}
namespace Symfony\Component\HttpFoundation {
    class Bag {
        public function __construct(private array $values) {}
        public function get($key, $default = null) { return $this->values[$key] ?? $default; }
        public function getInt($key, $default = 0) { return (int) $this->get($key, $default); }
    }
    class Request {
        public $request;
        public $query;
        public static function createFromGlobals() {
            $request = new self();
            $request->request = new Bag($_POST);
            $request->query = new Bag($_GET);
            return $request;
        }
        public function getMethod() { return $_SERVER['REQUEST_METHOD']; }
    }
    class Response { const HTTP_BAD_REQUEST = 400; }
}
'''


def run_fixture(folder, endpoint, settings):
    import os
    env = dict(os.environ, EYE_TEST_CASE=json.dumps(settings))
    script = "try { require '" + str(folder / 'interface/forms/eye_mag' / endpoint) + "'; } catch (BoundaryStop $e) {}"
    result = subprocess.run(['php', '-d', 'display_errors=1', '-r', script], env=env, capture_output=True, text=True)
    assert result.returncode == 0, result.stdout + result.stderr
    assert not result.stderr, result.stderr
    body, encoded = result.stdout.rsplit('\nEYE_TEST_RESULT=', 1)
    return json.loads(encoded), body


def writes(result):
    return [call for call in result['calls'] if call['sql'].lstrip().split()[0].upper() in ['INSERT', 'REPLACE', 'UPDATE', 'DELETE', 'ALTER', 'CREATE']]


def main():
    cases = 0
    with tempfile.TemporaryDirectory(prefix='eye-boundaries-') as name:
        folder = Path(name)
        target = folder / 'interface/forms/eye_mag'
        (target / 'php').mkdir(parents=True)
        (folder / 'interface/globals.php').write_text(BOOTSTRAP)
        for path in ['library/FeeSheetHtml.class.php', 'library/documents.php', 'library/options.inc.php', 'library/report.inc.php', 'controllers/C_Document.class.php', 'interface/forms/eye_mag/php/eye_mag_functions.php']:
            file = folder / path
            file.parent.mkdir(parents=True, exist_ok=True)
            file.write_text('<?php\n')
        for endpoint in ['save.php', 'view.php', 'SpectacleRx.php']:
            shutil.copyfile(FORM / endpoint, target / endpoint)

        rejected = [
            ('missing login', 'save.php', {'authenticated': False}, 401),
            ('write ACL', 'save.php', {'acl': 'none'}, 403),
            ('addonly update', 'save.php', {'acl': 'addonly', 'post': {'mode': 'update'}}, 403),
            ('unsupported mode', 'save.php', {'post': {'mode': 'arbitrary'}}, 403),
            ('phrase missing CSRF', 'save.php', {'post': {'action': 'dotphrase', 'sub': 'create'}}, 403),
            ('phrase wrong CSRF', 'save.php', {'post': {'action': 'dotphrase', 'sub': 'create', 'csrf_token_form': 'wrong'}}, 403),
            ('phrase GET mutation', 'save.php', {'method': 'GET', 'get': {'action': 'dotphrase', 'sub': 'delete'}}, 405),
            ('drawing preference CSRF', 'save.php', {'post': {'action': 'drawing_tool_preference'}}, 403),
            ('drawing zone validation', 'save.php', {'post': {'action': 'drawing_tool_preference', 'csrf_token_form': 'fixture-valid', 'zone': 'unknown', 'tool': 'draw'}}, 400),
            ('Rx read ACL', 'SpectacleRx.php', {'acl': 'none'}, 403),
            ('Rx missing patient', 'SpectacleRx.php', {'patient': 0}, 400),
        ]
        for label, endpoint, settings, status in rejected:
            result, body = run_fixture(folder, endpoint, settings)
            assert result['status'] == status, (label, result, body)
            assert not result['calls'], (label, result)
            cases += 1

        result, body = run_fixture(folder, 'save.php', {'post': {'action': 'dotphrase', 'sub': 'create', 'csrf_token_form': 'fixture-valid', 'phrase_key': '.test', 'name': 'Fixture', 'content': 'Example', 'authUserID': 999}})
        assert json.loads(body)['ok'] is True
        assert len(writes(result)) == 1 and writes(result)[0]['params'][2] == 7, result
        cases += 1
        result, body = run_fixture(folder, 'save.php', {'phrase_owner': 99, 'post': {'action': 'dotphrase', 'sub': 'update', 'csrf_token_form': 'fixture-valid', 'id': 9}})
        assert json.loads(body)['ok'] is False and not writes(result), result
        cases += 1
        result, body = run_fixture(folder, 'save.php', {'post': {'action': 'drawing_tool_preference', 'csrf_token_form': 'fixture-valid', 'zone': 'RETINA', 'tool': 'draw', 'authUserID': 999}})
        assert json.loads(body)['ok'] is True and writes(result)[0]['params'][1] == 7, result
        cases += 1
        for owner in [{'pid': 99, 'encounter': 20}, {'pid': 10, 'encounter': 99}]:
            result, body = run_fixture(folder, 'view.php', {'get': {'id': 8, 'pid': 99, 'encounter': 99}, 'owner': owner})
            assert result['status'] == 404 and not writes(result), result
            assert not any('from forms,form_encounter' in c['sql'].lower() for c in result['calls']), result
            cases += 1
        result, body = run_fixture(folder, 'SpectacleRx.php', {'get': {'form_id': 8, 'pid': 999, 'encounter': 20}})
        query = result['calls'][-1]
        assert query['sql'].count('?') == len(query['params']) == 3, result
        assert query['params'] == [8, 20, 10] and not writes(result), result
        cases += 1
        for post in [{'mode': 'update', 'id': 8}, {'mode': 'remove', 'delete_id': 8}, {'RXTYPE': 'Single', 'id': 8}]:
            for token in ['', 'wrong']:
                result, body = run_fixture(folder, 'SpectacleRx.php', {'rx_mutation': True, 'post': dict(post, csrf_token_form=token)})
                assert result['status'] == 403 and not writes(result), (post, result, body)
                cases += 1
        for identity, expected in [(0, 400), (8, 403)]:
            result, body = run_fixture(folder, 'SpectacleRx.php', {'rx_mutation': True, 'post': {'mode': 'update', 'id': identity, 'csrf_token_form': 'fixture-valid'}})
            assert result['status'] == expected and not writes(result), (identity, result, body)
            if identity:
                assert result['calls'][-1]['params'] == [identity, 10], result
            cases += 1
        for post, expected in [({'mode': 'remove', 'delete_id': 999}, [999, 10]), ({'RXTYPE': 'Single', 'id': 999}, ['Single', 999, 10])]:
            result, body = run_fixture(folder, 'SpectacleRx.php', {'rx_mutation': True, 'post': dict(post, csrf_token_form='fixture-valid', pid=999)})
            assert len(writes(result)) == 1 and writes(result)[0]['params'] == expected, (post, result, body)
            cases += 1
    print(f'PASS: {cases} entry-point boundaries; denied requests perform no writes, patient and author identities come from the session.')


if __name__ == '__main__':
    main()
