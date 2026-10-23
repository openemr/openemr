#!/usr/bin/env python3
"""Real multipart named-library requests against an isolated site and preference store."""
import hashlib
import json
from pathlib import Path
import re
import subprocess
import urllib.error
import urllib.request

from background_preferences import harness, snapshot


def request(base, fields, image=None, user=9, token='fixture-valid'):
    boundary = 'EyeNamedLibraryFixture'
    data = []
    for key, value in {'csrf_token_form': token, **fields}.items():
        data.extend([f'--{boundary}\r\nContent-Disposition: form-data; name="{key}"\r\n\r\n'.encode(), str(value).encode(), b'\r\n'])
    if image is not None:
        filename, content, mime = image
        data.extend([f'--{boundary}\r\nContent-Disposition: form-data; name="image_file"; filename="{filename}"\r\nContent-Type: {mime}\r\n\r\n'.encode(), content, b'\r\n'])
    data.append(f'--{boundary}--\r\n'.encode())
    req = urllib.request.Request(base + '/interface/forms/eye_mag/drawing_background.php', data=b''.join(data),
        headers={'Content-Type': 'multipart/form-data; boundary=' + boundary, 'X-Fixture-User': str(user)})
    try:
        response = urllib.request.urlopen(req, timeout=10)
    except urllib.error.HTTPError as error:
        response = error
    raw = response.read().decode()
    try:
        result = json.loads(raw)
    except json.JSONDecodeError:
        raise AssertionError((response.status, raw))
    return response.status, result


def read_pixel(path, x, y):
    return json.loads(subprocess.check_output(['php', '-r', '$im=imagecreatefrompng($argv[1]);echo json_encode(imagecolorsforindex($im,imagecolorat($im,(int)$argv[2],(int)$argv[3])));', str(path), str(x), str(y)], text=True))


def main():
    assertions = 0
    for shape in ['modern']:
        with harness(shape) as (root, base):
            png_path = root / 'source.png'
            subprocess.run(['php', '-r', '$im=imagecreatetruecolor(80,40);imagealphablending($im,false);imagesavealpha($im,true);imagefill($im,0,0,imagecolorallocatealpha($im,0,0,0,127));imagefilledrectangle($im,20,10,60,30,imagecolorallocatealpha($im,30,90,180,0));imagepng($im,$argv[1]);', str(png_path)], check=True)
            png = png_path.read_bytes()
            image = ('../../patient-name.php.png', png, 'image/png')
            original = snapshot(root)
            status, initial = request(base, {'action': 'list', 'zone': 'RETINA'})
            assert status == 200 and initial['success'] and initial['key'] == 'default'
            assert not any(option.get('saved') for option in initial['options'])
            assert snapshot(root) == original
            assertions += 3

            rejected = [
                ({'action': 'list', 'zone': 'RETINA'}, None, 'invalid', 9, 403),
                ({'action': 'save', 'zone': 'RETINA', 'name': 'Retinal marks'}, image, 'invalid', 9, 403),
                ({'action': 'save', 'zone': 'RETINA', 'name': 'Retinal marks'}, image, 'fixture-valid', 0, 403),
                ({'action': 'list', 'zone': 'RETINA', 'provider_id': 10}, None, 'fixture-valid', 9, 400),
                ({'action': 'save', 'zone': 'RETINA', 'name': 'Retinal marks', 'provider_id': 10}, image, 'fixture-valid', 9, 400),
                ({'action': 'save', 'zone': '../RETINA', 'name': 'Retinal marks'}, image, 'fixture-valid', 9, 400),
                ({'action': 'save', 'zone[]': 'RETINA', 'name': 'Retinal marks'}, image, 'fixture-valid', 9, 400),
                ({'action': 'save', 'zone': 'RETINA', 'name[]': 'Retinal marks'}, image, 'fixture-valid', 9, 400),
                ({'action': 'save', 'zone': 'RETINA', 'name': ' '}, image, 'fixture-valid', 9, 400),
                ({'action': 'save', 'zone': 'RETINA', 'name': 'x' * 81}, image, 'fixture-valid', 9, 400),
                ({'action': 'save', 'zone': 'RETINA', 'name': 'line\nbreak'}, image, 'fixture-valid', 9, 400),
                ({'action': 'save', 'zone': 'RETINA', 'name': 'Missing image'}, None, 'fixture-valid', 9, 400),
                ({'action': 'save', 'zone': 'RETINA', 'name': 'Bad image'}, ('bad.png', b'not an image', 'image/png'), 'fixture-valid', 9, 400),
                ({'action': 'save', 'zone': 'RETINA', 'name': 'Truncated image'}, ('bad.png', png[:35], 'image/png'), 'fixture-valid', 9, 400),
                ({'action': 'save', 'zone': 'RETINA', 'name': 'Large image'}, ('large.png', png + b'x' * (2 * 1024 * 1024), 'image/png'), 'fixture-valid', 9, 400),
                ({'action': 'select', 'zone': 'RETINA', 'key': 'saved-0123456789abcdef/../x'}, None, 'fixture-valid', 9, 400),
            ]
            for fields, upload, token, user, expected in rejected:
                before = snapshot(root)
                status, result = request(base, fields, upload, user, token)
                assert status == expected and not result['success'], (shape, fields, status, result)
                assert snapshot(root) == before, ('rejected request changed assets/preferences', fields)
                assertions += 2

            name = 'My reusable retinal marks'
            before_state = (root / 'state.json').read_bytes()
            status, saved = request(base, {'action': 'save', 'zone': 'RETINA', 'name': '  ' + name + '  '}, image)
            assert status == 200 and saved['success'] and re.fullmatch('saved-[a-f0-9]{16}', saved['key']), saved
            key = saved['key']
            option = next(option for option in saved['options'] if option['key'] == key)
            assert option['label'] == name and option['saved'] is True and option['structured'] is False
            assert option['url'].endswith('/providers/9/RETINA/library/' + key[6:] + '.png')
            assert (root / 'state.json').read_bytes() == before_state, 'saving a library image changed the default or encounter'
            asset = root / option['url'].lstrip('/')
            metadata = asset.with_suffix('.json')
            info = json.loads(subprocess.check_output(['php', '-r', 'echo json_encode(getimagesize($argv[1]));', str(asset)], text=True))
            assert info['0'] == 450 and info['1'] == 250 and info['2'] == 3
            assert read_pixel(asset, 0, 0)['alpha'] == 127 and read_pixel(asset, 20, 125)['alpha'] == 127
            center = read_pixel(asset, 225, 125)
            assert center == {'red': 30, 'green': 90, 'blue': 180, 'alpha': 0}, center
            entry = json.loads(metadata.read_text())
            assert entry['label'] == name and entry['sha256'] == hashlib.sha256(asset.read_bytes()).hexdigest()
            assert 'patient-name' not in metadata.read_text() and 'patient-name' not in str(asset)
            assert request(base, {'action': 'list', 'zone': 'RETINA'})[1]['key'] == 'default'
            assertions += 10

            before = snapshot(root)
            before_times = (asset.stat().st_mtime_ns, metadata.stat().st_mtime_ns)
            before_asset = asset.read_bytes()
            repeated = request(base, {'action': 'save', 'zone': 'RETINA', 'name': name}, ('another.png', png, 'image/png'))[1]
            assert repeated['key'] == key and snapshot(root) == before
            assert before_times == (asset.stat().st_mtime_ns, metadata.stat().st_mtime_ns)
            second = request(base, {'action': 'save', 'zone': 'RETINA', 'name': 'Second reusable image'}, image)[1]
            assert second['key'] != key and len([o for o in second['options'] if o.get('saved')]) == 2
            assert asset.read_bytes() == before_asset
            assertions += 4

            for zone, user in [('RETINA', 10), ('ANTSEG', 9)]:
                before = snapshot(root)
                listed = request(base, {'action': 'list', 'zone': zone}, user=user)[1]
                assert all(option['key'] != key for option in listed['options'])
                assert request(base, {'action': 'select', 'zone': zone, 'key': key}, user=user)[0] == 400
                assert snapshot(root) == before
                assertions += 3
            assert request(base, {'action': 'select', 'zone': 'RETINA', 'key': key})[0] == 200
            listed = request(base, {'action': 'list', 'zone': 'RETINA'})[1]
            assert listed['key'] == key and any(o['key'] == key and o['label'] == name for o in listed['options'])
            state = json.loads((root / 'state.json').read_text())
            assert len(state['writes']) == 1 and 'form_eye_mag_prefs' in state['writes'][0]['sql'] and state['writes'][0]['params'][1] == 9
            assertions += 3

            jpg_path = root / 'source.jpg'
            subprocess.run(['php', '-r', '$im=imagecreatetruecolor(80,30);imagefill($im,0,0,imagecolorallocate($im,140,70,20));imagejpeg($im,$argv[1]);', str(jpg_path)], check=True)
            before_state = (root / 'state.json').read_bytes()
            status, jpg = request(base, {'action': 'save', 'zone': 'SDRETINA', 'name': 'Large retina image'}, ('image.jpg', jpg_path.read_bytes(), 'image/jpeg'))
            assert status == 200 and jpg['success']
            image_path = root / next(o['url'] for o in jpg['options'] if o['key'] == jpg['key']).lstrip('/')
            info = json.loads(subprocess.check_output(['php', '-r', 'echo json_encode(getimagesize($argv[1]));', str(image_path)], text=True))
            assert info['0'] == 1000 and info['1'] == 500 and info['2'] == 3
            assert read_pixel(image_path, 0, 0)['alpha'] == 127 and read_pixel(image_path, 500, 250)['alpha'] == 0
            assert (root / 'state.json').read_bytes() == before_state
            assert request(base, {'action': 'list', 'zone': 'RETINA'})[1]['key'] == key
            assert not list((asset.parent).glob('.saving-*'))
            assertions += 6
            assert not re.search(r'(Deprecated|Warning|Fatal error|Notice):', (root / 'server.log').read_text())
            assertions += 1
    print(f'PASS: {assertions} named-library HTTP assertions; transparent PNG/JPEG, immutable named assets, core-session CSRF and ACL, user/zone isolation, list/save without preference mutation, explicit default selection.')


if __name__ == '__main__':
    main()
