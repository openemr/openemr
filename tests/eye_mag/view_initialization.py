#!/usr/bin/env python3
"""Run actual Eye view initialization and its complete helpers without a clinical DB."""
import argparse
import json
import os
from pathlib import Path
import shutil
import subprocess
import tempfile

BOOTSTRAP = r'''<?php
namespace {
    $fixture = json_decode(getenv('EYE_VIEW_CASE'), true, 512, JSON_THROW_ON_ERROR);
    $_GET = ['id' => $fixture['id'] ?? 8, 'pid' => 999, 'encounter' => 999];
    $_POST = [];
    $_REQUEST = $_GET;
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $pid = 10;
    $encounter = 20;
    $calls = [];
    $audit = [];
    $warnings = [];
    $patientQueryReached = false;
    class ViewInitializationComplete extends \RuntimeException {}
    class FixtureRows {
        public function __construct(public array $rows) {}
    }
    function xl($value) { return $value; }
    function xlt($value) { return htmlspecialchars((string)$value, ENT_QUOTES); }
    function text($value) { return xlt($value); }
    function prevSetting($prefix, $setting, $field, $default) { return $default; }
    function fixtureQuery($sql, $params = []) {
        global $calls, $fixture, $patientQueryReached;
        $calls[] = ['sql' => $sql, 'params' => $params];
        if (!preg_match('/^\s*SELECT\b/i', $sql)) {
            throw new \RuntimeException('View initialization attempted a database mutation: ' . $sql);
        }
        if (substr_count($sql, '?') !== count($params)) {
            throw new \RuntimeException('Query parameters were not bound correctly');
        }
        if (str_contains($sql, 'SELECT pid, encounter FROM forms')) {
            $owner = array_key_exists('owner', $fixture) ? $fixture['owner'] : ['pid' => 10, 'encounter' => 20];
            return $owner === null ? [] : [$owner];
        }
        if (str_contains($sql, 'form_eye_mag_prefs')) return [];
        if (str_contains($sql, "c.name = 'Handouts'")) return [['id' => 77]];
        if ($params === ['Eye_IOP_Methods']) {
            return [
                ['option_id' => 'AP', 'title' => 'Goldmann fixture', 'activity' => 1],
                ['option_id' => 'TPN', 'title' => 'Handheld fixture', 'activity' => 0],
            ];
        }
        if (str_contains($sql, "list_id = 'Eye_Motility_Display'")) return [['option_id' => 'H']];
        if (preg_match('/from\s+forms\s*,\s*form_encounter/i', $sql)) {
            $patientQueryReached = true;
            throw new ViewInitializationComplete('Bound patient query reached');
        }
        throw new \RuntimeException('Unexpected initialization query: ' . $sql);
    }
    function sqlQuery($sql, $params = []) { return fixtureQuery($sql, $params)[0] ?? null; }
    function sqlStatement($sql, $params = []) { return new FixtureRows(fixtureQuery($sql, $params)); }
    function sqlFetchArray($result) { return array_shift($result->rows); }
    register_shutdown_function(function () {
        global $calls, $audit, $warnings, $patientQueryReached;
        echo "\nEYE_VIEW_RESULT=" . json_encode([
            'status' => http_response_code() ?: 200,
            'calls' => $calls, 'audit' => $audit, 'warnings' => $warnings,
            'patient_query_reached' => $patientQueryReached,
            'materials_category' => $GLOBALS['materialsHandoutCategoryId'] ?? null,
            'iop_methods' => $GLOBALS['iopMethodOptions'] ?? null,
            'motility_display' => $GLOBALS['motilityDisplay'] ?? null,
            'csrf' => $GLOBALS['motilityCsrfToken'] ?? null,
            'helper_source' => function_exists('eye_mag_iop_method_options')
                ? (new \ReflectionFunction('eye_mag_iop_method_options'))->getFileName() : null,
        ], JSON_THROW_ON_ERROR);
    });
}
namespace OpenEMR\Common\Session {
    class FixtureSession {
        public function get($key, $default = null) {
            return ['authUserID' => 7, 'authUser' => 'fixture', 'authProvider' => 'fixture', 'pid' => 10, 'encounter' => 20][$key] ?? $default;
        }
    }
    class SessionWrapperFactory {
        public static function getInstance() { return new self(); }
        public function getActiveSession() { return new FixtureSession(); }
    }
}
namespace OpenEMR\Common\Csrf {
    class CsrfUtils {
        public static function collectCsrfToken($session) {
            if ($session->get('authUserID') !== 7) throw new \RuntimeException('Wrong fixture session');
            return 'fixture-startup-token';
        }
    }
}
namespace OpenEMR\Common\Database {
    class QueryUtils {
        public static function querySingleRow($sql, $params = []) { return \sqlQuery($sql, $params); }
        public static function sqlStatementThrowException($sql, $params = []) { return \sqlStatement($sql, $params); }
    }
}
namespace OpenEMR\Core {
    class OEGlobalsBag {
        public static function getInstance() { return new self(); }
        public function getSrcDir() { return getenv('EYE_VIEW_ROOT') . '/library'; }
    }
}
namespace OpenEMR\Services { class FacilityService {} }
namespace OpenEMR\BC {
    class ServiceContainer {
        public static function getLogger() { return new self(); }
        public function warning(...$args) { $GLOBALS['warnings'][] = $args; }
    }
}
namespace OpenEMR\Common\Logging {
    class EventAuditLogger {
        public static function getInstance() { return new self(); }
        public function newEvent(...$args) { $GLOBALS['audit'][] = $args; }
    }
}
'''


def run_case(root, settings):
    entry = root / 'interface/forms/eye_mag/view.php'
    script = 'try { require ' + json.dumps(str(entry)) + '; } catch (ViewInitializationComplete $e) {}'
    process = subprocess.run(
        ['php', '-d', 'error_reporting=-1', '-d', 'display_errors=1', '-r', script],
        env=dict(os.environ, EYE_VIEW_ROOT=str(root), EYE_VIEW_CASE=json.dumps(settings)),
        capture_output=True, text=True,
    )
    assert process.returncode == 0, process.stdout + process.stderr
    assert not process.stderr, process.stderr
    body, encoded = process.stdout.rsplit('\nEYE_VIEW_RESULT=', 1)
    result = json.loads(encoded)
    assert Path(result['helper_source']).resolve() == (root / 'interface/forms/eye_mag/php/eye_mag_functions.php').resolve(), result
    return result, body


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--form', type=Path, default=Path(__file__).resolve().parents[2] / 'interface/forms/eye_mag')
    args = parser.parse_args()
    with tempfile.TemporaryDirectory(prefix='eye-view-initialization-') as directory:
        root = Path(directory)
        target = root / 'interface/forms/eye_mag'
        (target / 'php').mkdir(parents=True)
        (root / 'library').mkdir()
        (root / 'library/FeeSheetHtml.class.php').write_text('<?php\n')
        (root / 'interface/globals.php').write_text(BOOTSTRAP)
        # Keep the production helper library intact: missing functions must remain missing.
        for relative in ['view.php', 'php/eye_mag_functions.php', 'php/drawing_backgrounds.php']:
            shutil.copyfile(args.form / relative, target / relative)
        for form_id in [8, 0]:
            result, body = run_case(root, {'id': form_id})
            assert result['status'] == 200 and result['patient_query_reached'], result
            assert body == '', body
            joined = result['calls'][-1]
            assert joined['params'] == [form_id, 10, 20], joined
            for predicate in ['forms.form_id =?', 'forms.pid =?', 'forms.encounter =?']:
                assert predicate in joined['sql'], joined
            assert result['materials_category'] == 77, result
            assert result['iop_methods']['AP']['title'] == 'Goldmann fixture', result
            assert result['iop_methods']['TPN']['active'] is False, result
            assert result['motility_display'] == 'H', result
            assert result['csrf'] == 'fixture-startup-token', result
            assert not result['audit'] and not result['warnings'], result
            print(f'PASS actual view + complete Eye helpers initialize form {form_id}, handouts, IOP labels, motility and token before bound session-patient query')
        for label, owner, audited in [
            ('different patient', {'pid': 99, 'encounter': 20}, True),
            ('different encounter', {'pid': 10, 'encounter': 99}, True),
            ('missing form', None, False),
        ]:
            result, body = run_case(root, {'owner': owner})
            assert result['status'] == 404 and not result['patient_query_reached'], result
            assert len(result['audit']) == int(audited) and len(result['warnings']) == int(audited), result
            assert result['materials_category'] is None and result['iop_methods'] is None, result
            assert not any('list_options' in call['sql'] or 'FROM categories' in call['sql'] for call in result['calls']), result
            assert 'Form not found' in body, body
            print(f'PASS {label} refuses before feature initialization or patient query')
    print('PASS: 5 actual view initialization cases; full production Eye helpers loaded, fixture SQL reads only, no clinical database.')


if __name__ == '__main__':
    main()
