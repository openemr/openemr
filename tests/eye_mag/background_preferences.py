#!/usr/bin/env python3
"""Exercise real background endpoints with real multipart uploads and an isolated site/DB."""
from contextlib import contextmanager
import hashlib
import json
import os
from pathlib import Path
import shutil
import socket
import subprocess
import tempfile
import time
import urllib.error
import urllib.request

FORM = Path(__file__).resolve().parents[2] / 'interface/forms/eye_mag'
BOOTSTRAP = r'''<?php
namespace Symfony\Component\HttpFoundation\Session { interface SessionInterface {} }
namespace OpenEMR\Common\Session {
    class FixtureSession implements \Symfony\Component\HttpFoundation\Session\SessionInterface {
        public function get($key, $default = null) { return ['authUserID' => (int) ($_SERVER['HTTP_X_FIXTURE_USER'] ?? 9), 'authUser' => 'fixture', 'site_id' => 'fixture'][$key] ?? $default; }
    }
    class SessionWrapperFactory {
        public static function getInstance() { return new self(); }
        public function getActiveSession() { return new FixtureSession(); }
    }
}
namespace OpenEMR\Common\Csrf {
    function valid($token) {
        if (isset($_SERVER['HTTP_X_FIXTURE_CSRF_THROW'])) throw new \RuntimeException('Verifier unavailable');
        return $token === 'fixture-valid';
    }
    class CsrfUtils {
        public static function verifyCsrfToken($token, \Symfony\Component\HttpFoundation\Session\SessionInterface $session, string $subject = 'default'): bool { return valid($token) && $session->get('authUserID') > 0 && $subject === 'default'; }
        public static function collectCsrfToken(\Symfony\Component\HttpFoundation\Session\SessionInterface $session, string $subject = 'default'): string { return $session->get('authUserID') > 0 && $subject === 'default' ? 'fixture-valid' : ''; }
    }
}
namespace OpenEMR\Core {
    class OEGlobalsBag {
        public static function getInstance() { return new self(); }
        public function getWebRoot() { return ''; }
        public function getString($key) { return ['OE_SITE_DIR' => getenv('EYE_FIXTURE_ROOT') . '/sites/fixture', 'OE_SITE_WEBROOT' => '/sites/fixture'][$key] ?? ''; }
        public function get($key) { return ['webroot' => '', 'web_root' => '', 'webserver_root' => getenv('EYE_FIXTURE_ROOT'), 'oer_config' => ['documents' => ['repository' => getenv('EYE_FIXTURE_ROOT') . '/documents/']]][$key] ?? ''; }
    }
}
namespace OpenEMR\Common\Database {
    class QueryUtils {
        public static function querySingleRow($sql, $params = []) { return \sqlQuery($sql, $params); }
        public static function sqlStatementThrowException($sql, $params = []) { return \sqlStatement($sql, $params); }
    }
}
namespace OpenEMR\Common\Acl {
    class AclMain {
        public static function aclCheckCore($section, $value, $unused = '', $access = '') {
            $acl = $_SERVER['HTTP_X_FIXTURE_ACL'] ?? 'write';
            return $acl !== 'none' && ($access !== 'write' || $acl === 'write');
        }
    }
}
namespace {
    require __DIR__ . '/../src/Forms/EyeMag/BaseDrawing.php';
    $GLOBALS['OE_SITE_DIR'] = getenv('EYE_FIXTURE_ROOT') . '/sites/fixture';
    $GLOBALS['webroot'] = '';
    function xlt($v) { return htmlspecialchars((string) $v, ENT_QUOTES); }
    function xl($v) { return $v; }
    function attr($v) { return htmlspecialchars((string) $v, ENT_QUOTES); }
    function attr_url($v) { return rawurlencode((string) $v); }
    function text($v) { return attr($v); }
    function xla($v) { return attr($v); }
    function fixture_state() { return json_decode(file_get_contents(getenv('EYE_FIXTURE_ROOT') . '/state.json'), true); }
    function sqlQuery($sql, $params = []) {
        $state = fixture_state();
        if (strpos($sql, 'DRAW_BACKGROUND') !== false) {
            $key = $params[0] . ':' . $params[1];
            return isset($state['prefs'][$key]) ? ['GOVALUE' => $state['prefs'][$key]] : [];
        }
        if (strpos($sql, 'drawing_overlays') !== false) return $state['overlay'] ?? [];
        if (stripos($sql, 'from documents') !== false) return !empty($state['saved']) ? ['id' => 123] : [];
        return [];
    }
    function sqlStatement($sql, $params = []) {
        $state = fixture_state();$state['writes'][] = ['sql' => $sql, 'params' => $params];
        if (strpos($sql, 'DRAW_BACKGROUND') !== false) {
            if (strpos($sql, 'UPDATE ') === 0) {
                $key = $params[1] . ':' . $params[2];
                if (($state['prefs'][$key] ?? '') === $params[3]) $state['prefs'][$key] = $params[0];
            } else $state['prefs'][$params[1] . ':' . $params[0]] = $params[3];
        }
        file_put_contents(getenv('EYE_FIXTURE_ROOT') . '/state.json', json_encode($state));
        return true;
    }
}
'''
ROUTER = r'''<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($path === '/fixture/page') { require __DIR__ . '/background_preferences_fixture.php'; return; }
if ($path === '/controller.php') {
    require __DIR__ . '/interface/globals.php';
    $state = fixture_state();
    if (is_string($state['saved_image'] ?? null) && preg_match('/\Adata:image\/(png|jpeg);base64,([A-Za-z0-9+\/=\r\n]+)\z/', $state['saved_image'], $image)) {
        $bytes = base64_decode($image[2], true);
        if ($bytes !== false) { header('Content-Type: image/' . $image[1]); echo $bytes; return; }
    }
    header('Content-Type: image/jpeg'); readfile(__DIR__ . '/interface/forms/eye_mag/images/OU_EXT_BASE.jpg'); return;
}
if ($path === '/fixture/read') {
    require __DIR__ . '/interface/globals.php';
    require __DIR__ . '/interface/forms/eye_mag/php/drawing_backgrounds.php';
    $session = \OpenEMR\Common\Session\SessionWrapperFactory::getInstance()->getActiveSession();
    $zone = $_GET['zone'] ?? 'RETINA';
    header('Content-Type: application/json');
    echo json_encode(['key' => eye_mag_drawing_background_preference($zone, $session->get('authUserID')), 'options' => eye_mag_drawing_background_options($zone, $session->get('authUserID')), 'csrf' => eye_mag_drawing_background_csrf_token($session)]);
    return;
}
return false;
'''


@contextmanager
def harness(shape='modern'):
    with tempfile.TemporaryDirectory(prefix='eye-background-tests-') as directory:
        root = Path(directory)
        target = root / 'interface/forms/eye_mag'
        shutil.copytree(FORM, target)
        (root / 'interface/globals.php').write_text(BOOTSTRAP)
        core_source = Path(os.environ.get('EYE_TEST_CORE', str(FORM.parents[2]))) / 'src/Forms/EyeMag/BaseDrawing.php'
        core_target = root / 'src/Forms/EyeMag/BaseDrawing.php'
        core_target.parent.mkdir(parents=True)
        shutil.copyfile(core_source, core_target)
        (root / 'router.php').write_text(ROUTER)
        fixture = Path(__file__).with_name('background_preferences_fixture.php')
        if fixture.exists():
            shutil.copyfile(fixture, root / 'background_preferences_fixture.php')
            konva = Path(os.environ.get('EYE_TEST_KONVA', str(FORM.parents[2] / 'public/assets/konva/konva.js')))
            if konva.exists(): shutil.copyfile(konva, root / 'fixture-konva.js')
        (root / 'state.json').write_text(json.dumps({'prefs': {}, 'writes': []}))
        (root / 'sites/fixture/images/eye_mag').mkdir(parents=True)
        with socket.socket() as sock:
            sock.bind(('127.0.0.1', 0)); port = sock.getsockname()[1]
        log = open(root / 'server.log', 'w+')
        process = subprocess.Popen(['php', '-d', 'error_reporting=-1', '-d', 'display_errors=1', '-d', 'upload_max_filesize=4M', '-d', 'post_max_size=5M', '-S', f'127.0.0.1:{port}', '-t', str(root), str(root / 'router.php')], stdout=log, stderr=log, env=dict(os.environ, EYE_FIXTURE_ROOT=str(root), EYE_CSRF_SHAPE=shape))
        base = f'http://127.0.0.1:{port}'
        try:
            for _ in range(100):
                try:
                    urllib.request.urlopen(base + '/fixture/read', timeout=.3).read(); break
                except (OSError, urllib.error.URLError): time.sleep(.03)
            else:
                log.seek(0); raise RuntimeError(log.read())
            yield root, base
        finally:
            process.terminate(); process.wait(timeout=5); log.close()


def request(base, data=None, upload=None, user=9, token='fixture-valid', extra_headers=None):
    headers = {'X-Fixture-User': str(user), **(extra_headers or {})}
    if data is None:
        req = urllib.request.Request(base + '/fixture/read', headers=headers)
    else:
        fields = {'csrf_token_form': token, **data}
        boundary = 'EyeBackgroundFixtureBoundary'
        parts = []
        for key, value in fields.items():
            parts.extend([f'--{boundary}\r\nContent-Disposition: form-data; name="{key}"\r\n\r\n'.encode(), str(value).encode(), b'\r\n'])
        if upload:
            name, content, mime = upload
            parts.extend([f'--{boundary}\r\nContent-Disposition: form-data; name="background_file"; filename="{name}"\r\nContent-Type: {mime}\r\n\r\n'.encode(), content, b'\r\n'])
        parts.append(f'--{boundary}--\r\n'.encode())
        headers['Content-Type'] = f'multipart/form-data; boundary={boundary}'
        req = urllib.request.Request(base + '/interface/forms/eye_mag/drawing_background.php', data=b''.join(parts), headers=headers)
    try:
        response = urllib.request.urlopen(req, timeout=10)
    except urllib.error.HTTPError as error:
        response = error
    raw = response.read().decode()
    try: body = json.loads(raw)
    except json.JSONDecodeError: raise AssertionError((response.status, raw))
    return response.status, body


def snapshot(root):
    return {str(path.relative_to(root)): hashlib.sha256(path.read_bytes()).hexdigest() for path in [root / 'state.json', *sorted((root / 'sites').rglob('*'))] if path.is_file()}


def main():
    count = 0
    for shape in ['modern']:
        with harness(shape) as (root, base):
            assert request(base)[1]['csrf'] == 'fixture-valid'
            rejected = [
                ({'action': 'select', 'zone': 'RETINA', 'key': 'blank'}, {'extra_headers': {'X-Fixture-Acl': 'none'}}, 'fixture-valid', 403),
                ({'action': 'select', 'zone': 'RETINA', 'key': 'blank'}, {'extra_headers': {'X-Fixture-Acl': 'read'}}, 'fixture-valid', 403),
                ({'action': 'select', 'zone': 'RETINA', 'key': 'blank'}, {}, '', 403),
                ({'action': 'select', 'zone': 'RETINA', 'key': 'blank'}, {}, 'wrong', 403),
                ({'action': 'select', 'zone': 'RETINA', 'key': 'blank'}, {'user': 0}, 'fixture-valid', 403),
                ({'action': 'select', 'zone': 'RETINA', 'key': 'blank'}, {'extra_headers': {'X-Fixture-Csrf-Throw': '1'}}, 'fixture-valid', 403),
                ({'action': 'select', 'zone': '../RETINA', 'key': 'blank'}, {}, 'fixture-valid', 400),
                ({'action': 'select', 'zone': 'RETINA', 'key': '../9/RETINA/image.jpg'}, {}, 'fixture-valid', 400),
                ({'action': 'select', 'zone': 'RETINA', 'key': 'custom-0123456789abcdef/../x'}, {}, 'fixture-valid', 400),
                ({'action': 'select', 'zone': 'RETINA', 'key': 'blank', 'provider_id': 99}, {}, 'fixture-valid', 400),
                ({'action': 'upload', 'zone': 'RETINA', 'provider_id': 99}, {}, 'fixture-valid', 400),
                ({'action': 'select', 'zone': 'RETINA', 'key': 'practice'}, {}, 'fixture-valid', 400),
                ({'action': 'select', 'zone': 'RETINA', 'key': 'retina'}, {}, 'fixture-valid', 400),
            ]
            for fields, kwargs, token, status in rejected:
                before = snapshot(root)
                actual, body = request(base, fields, token=token, **kwargs)
                assert actual == status and body['success'] is False, (shape, fields, actual, body)
                assert snapshot(root) == before, (shape, fields, 'denied request changed files/preferences')
                count += 1
            valid_png = root / 'valid.png'
            subprocess.run(['php', '-r', '$im=imagecreatetruecolor(80,40);imagefill($im,0,0,imagecolorallocate($im,50,120,190));imagepng($im,$argv[1]);', str(valid_png)], check=True)
            png = valid_png.read_bytes()
            for name, data, mime in [('fake.png', b'not an image', 'image/png'), ('truncated.png', png[:35], 'image/png'), ('large.png', png + b'x' * (2 * 1024 * 1024), 'image/png')]:
                before = snapshot(root)
                actual, body = request(base, {'action': 'upload', 'zone': 'RETINA'}, (name, data, mime))
                assert actual == 400 and not body['success'], (name, actual, body)
                assert snapshot(root) == before
                count += 1
            for width, height in [(4097, 1), (3000, 3000)]:
                oversized = root / 'dimensions.png'
                subprocess.run(['php', '-r', '$im=imagecreatetruecolor((int)$argv[1],(int)$argv[2]);imagepng($im,$argv[3]);', str(width), str(height), str(oversized)], check=True)
                before = snapshot(root)
                actual, body = request(base, {'action': 'upload', 'zone': 'RETINA'}, ('dimensions.png', oversized.read_bytes(), 'image/png'))
                assert actual == 400 and not body['success'], (width, height, actual, body)
                assert snapshot(root) == before; count += 1
            status, uploaded = request(base, {'action': 'upload', 'zone': 'RETINA'}, ('../../evil.php.png', png, 'image/png'))
            assert status == 200 and uploaded['success'], uploaded
            key = uploaded['key']; assert key.startswith('custom-') and len(key) == 23
            asset = root / ('sites/fixture/images/eye_mag/providers/9/RETINA/' + key[7:] + '.jpg')
            assert asset.is_file() and asset.read_bytes().startswith(b'\xff\xd8')
            dimensions = json.loads(subprocess.check_output(['php', '-r', 'echo json_encode(getimagesize($argv[1]));', str(asset)], text=True))
            assert dimensions['0'] == 450 and dimensions['1'] == 250
            before_bytes, before_time = asset.read_bytes(), asset.stat().st_mtime_ns
            assert request(base, {'action': 'upload', 'zone': 'RETINA'}, ('other-name.png', png, 'image/png'))[1]['key'] == key
            assert asset.read_bytes() == before_bytes and asset.stat().st_mtime_ns == before_time
            assert request(base)[1]['key'] == key
            assert request(base, user=10)[1]['key'] == 'default'
            for fields, user in [({'action': 'select', 'zone': 'RETINA', 'key': key}, 10), ({'action': 'select', 'zone': 'ANTSEG', 'key': key}, 9)]:
                before = snapshot(root); assert request(base, fields, user=user)[0] == 400; assert snapshot(root) == before
            jpg = (FORM / 'images/OU_EXT_BASE.jpg').read_bytes()
            status, second = request(base, {'action': 'upload', 'zone': 'RETINA'}, ('test.jpg', jpg, 'image/jpeg'))
            assert status == 200 and second['key'] != key and asset.read_bytes() == before_bytes
            practice = root / 'sites/fixture/images/eye_mag/OU_RETINA_BASE.jpg'; practice.write_bytes(jpg)
            status, selected = request(base, {'action': 'select', 'zone': 'RETINA', 'key': 'practice'})
            assert status == 200 and request(base)[1]['key'] == 'practice'
            assert any(o['key'] == 'practice' and not o['structured'] for o in selected['options'])
            for option in ['blank', 'stock-neuro', 'default']:
                assert request(base, {'action': 'select', 'zone': 'RETINA', 'key': option})[0] == 200
                assert request(base)[1]['key'] == option
            state = json.loads((root / 'state.json').read_text())
            assert state['writes'] and all('form_eye_mag_prefs' in write['sql'] for write in state['writes'])
            assert all(write['params'][1] == 9 for write in state['writes'])
            assert asset.read_bytes() == before_bytes
            count += 10
    print(f'PASS: {count} real endpoint checks; core-session CSRF and ACL, auth/zone/path isolation, real PNG/JPEG multipart uploads, bounds, immutable assets, provider preference persistence and no encounter writes.')


if __name__ == '__main__':
    import sys
    if '--browser' in sys.argv:
        with harness() as (root, base):
            subprocess.run(['node', str(Path(sys.argv[sys.argv.index('--browser') + 1])), base, str(root)], check=True)
    else:
        main()
