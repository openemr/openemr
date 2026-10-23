#!/usr/bin/env python3
"""Rename/delete through the real endpoint; retain images already referenced by exams."""
import json
from pathlib import Path
import re
import subprocess
import urllib.request
from background_preferences import harness, snapshot
from background_library import request


def main():
    checks = 0
    for shape in ['modern']:
        with harness(shape) as (root, base):
            source = root / 'clinical.png'
            subprocess.run(['php', '-r', '$i=imagecreatetruecolor(40,20);imagealphablending($i,false);imagesavealpha($i,true);imagefill($i,0,0,imagecolorallocatealpha($i,0,0,0,127));imagefilledellipse($i,20,10,12,12,imagecolorallocatealpha($i,180,40,20,0));imagepng($i,$argv[1]);', str(source)], check=True)
            image = ('clinical.png', source.read_bytes(), 'image/png')
            def call(action, key=None, name=None, zone='RETINA', user=9, token='fixture-valid'):
                fields = {'action': action, 'zone': zone}
                if key is not None: fields['key'] = key
                if name is not None: fields['name'] = name
                return request(base, fields, image if action == 'save' else None, user, token)
            def save(name, zone='RETINA', user=9):
                status, result = call('save', name=name, zone=zone, user=user)
                assert status == 200 and result['success'], result
                return result['key'], next(o for o in result['options'] if o['key'] == result['key'])
            def state(): return json.loads((root / 'state.json').read_text())
            key, original = save('Original drawing')
            other_user, _ = save('Other provider', user=10)
            other_zone, _ = save('Other zone', zone='ANTSEG')
            assert call('select', other_user, user=10)[0] == 200
            assert call('select', other_zone, zone='ANTSEG')[0] == 200
            png = root / original['url'].lstrip('/')
            initial_png = png.read_bytes()
            initial_state = (root / 'state.json').read_bytes()
            for action, target, name, zone, user, token in [
                ('rename', key, 'New', 'RETINA', 9, 'bad'),
                ('delete', key, None, 'RETINA', 9, 'bad'),
                ('rename', key, 'New', 'RETINA', 10, 'fixture-valid'),
                ('delete', key, None, 'ANTSEG', 9, 'fixture-valid'),
                ('rename', 'default', 'New', 'RETINA', 9, 'fixture-valid'),
                ('delete', 'saved-../bad', None, 'RETINA', 9, 'fixture-valid'),
                ('rename', key, ' ', 'RETINA', 9, 'fixture-valid'),
                ('rename', key, 'x' * 81, 'RETINA', 9, 'fixture-valid'),
                ('rename', key, 'a\nb', 'RETINA', 9, 'fixture-valid'),
                ('delete', key, None, 'RETINA', 0, 'fixture-valid'),
            ]:
                before = snapshot(root)
                status, result = call(action, target, name, zone, user, token)
                assert status in [400, 403] and not result['success'], (action, result)
                assert snapshot(root) == before
                checks += 2
            status, renamed = call('rename', key, ' Renamed clinical overlay ')
            assert status == 200 and renamed['success']
            updated = next(o for o in renamed['options'] if o['key'] == key)
            assert updated['label'] == 'Renamed clinical overlay' and updated['url'] == original['url']
            assert png.read_bytes() == initial_png and (root / 'state.json').read_bytes() == initial_state
            assert next(o for o in call('list')[1]['options'] if o['key'] == key)['label'] == updated['label']
            assert save('Renamed clinical overlay')[0] == key
            replacement, _ = save('Original drawing')
            assert replacement != key
            checks += 6
            assert call('select', replacement)[0] == 200
            before = state()
            status, deleted = call('delete', key)
            assert status == 200 and deleted['key'] == replacement and all(o['key'] != key for o in deleted['options'])
            assert state() == before and png.read_bytes() == initial_png
            assert urllib.request.urlopen(base + original['url']).read() == initial_png
            before_repeat = snapshot(root)
            assert call('delete', key)[0] == 200 and snapshot(root) == before_repeat
            assert call('rename', key, 'Cannot rename deleted')[0] == 400 and snapshot(root) == before_repeat
            checks += 6
            # A default delete resets only this user's exact preference and retains both PNGs.
            before = state()
            status, reset = call('delete', replacement)
            after = state()
            assert status == 200 and reset['key'] == 'default'
            assert after['prefs']['9:RETINA_BACKGROUND'] == 'default'
            assert after['prefs']['10:RETINA_BACKGROUND'] == other_user and after['prefs']['9:ANTSEG_BACKGROUND'] == other_zone
            assert len(after['writes']) == len(before['writes']) + 1
            write = after['writes'][-1]
            assert 'UPDATE form_eye_mag_prefs' in write['sql'] and 'AND GOVALUE = ?' in write['sql']
            assert write['params'] == ['default', 9, 'RETINA_BACKGROUND', replacement]
            revived, _ = save('Original drawing')
            assert revived not in [key, replacement] and save('Original drawing')[0] == revived
            assert png.read_bytes() == initial_png
            checks += 8
            # Archived backgrounds resolve for saved exams, but never reappear in the library.
            neuro, neuro_option = save('Neuro background', zone='NEURO')
            assert call('delete', neuro, zone='NEURO')[0] == 200
            current = state(); current['saved'] = True; current['overlay'] = {'overlay_json': '[]', 'background_key': neuro}
            (root / 'state.json').write_text(json.dumps(current))
            html = urllib.request.urlopen(base + '/fixture/page').read().decode()
            match = re.search(r'id="base_url_NEURO"[^>]*value="([^"]+)"', html)
            assert match and match[1] == neuro_option['url'], match
            neuro_html = html.split('id="NEURO_canvas"', 1)[1]
            assert 'data-structured-background="0"' in neuro_html
            assert all(o['key'] != neuro for o in call('list', zone='NEURO')[1]['options'])
            assert not list(png.parent.glob('.saving-*'))
            assert not re.search(r'(Deprecated|Warning|Fatal error|Notice):', (root / 'server.log').read_text())
            checks += 6
    print(f'PASS: {checks} management HTTP assertions; rename stable URL, delete/default isolation, retained encounter assets, archived backgrounds, resave after rename/delete and CSRF rejection.')


if __name__ == '__main__': main()
