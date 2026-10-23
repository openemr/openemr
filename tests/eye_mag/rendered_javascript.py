#!/usr/bin/env python3
"""Check shipped JavaScript and the complete PHP-generated script using synthetic data."""
from pathlib import Path
import shutil
import subprocess
import tempfile

form = Path(__file__).resolve().parents[2] / 'interface/forms/eye_mag'
scripts = sorted((form / 'js').glob('*.js'))
for script in scripts:
    subprocess.run(['node', '--check', str(script)], check=True)

with tempfile.TemporaryDirectory(prefix='eye-script-') as name:
    root = Path(name)
    target = root / 'interface/forms/eye_mag/js'
    target.mkdir(parents=True)
    shutil.copyfile(form / 'js/eye_base.php', target / 'eye_base.php')
    library = root / 'library'
    library.mkdir()
    for include in ['api.inc.php', 'forms.inc.php', 'patient.inc.php']:
        (library / include).write_text('<?php\n')
    (root / 'interface/globals.php').write_text(r'''<?php
namespace OpenEMR\Core {
    class OEGlobalsBag {
        public static function getInstance() { return new self(); }
        public function getSrcDir() { return ''; }
    }
}
namespace {
    $srcdir = dirname(__DIR__) . '/library';
    $_REQUEST = ['providerID' => 7, 'enc' => 20];
    // Synthetic provider defaults exercise the generated zone-default handlers.
    $EXT = ['RBROW' => 'Normal', 'LBROW' => 'Normal'];
    $ANTSEG = ['ODCORNEA' => 'Clear', 'OSCORNEA' => 'Clear'];
    $RETINA = ['ODDISC' => 'Normal', 'OSDISC' => 'Normal'];
    $NEURO = ['ODCOLOR' => '11/11', 'OSCOLOR' => '11/11'];
    function js_escape($value) { return json_encode($value); }
    function attr_js($value) { return json_encode($value); }
    function xlj($value) { return json_encode($value); }
    function xlt($value) { return htmlspecialchars($value, ENT_QUOTES); }
    function xla($value) { return htmlspecialchars($value, ENT_QUOTES); }
    function sqlStatement($sql, ...$args) { return $sql; }
    function sqlFetchArray($result) { return str_contains($result, 'max(seq)') ? ['maxseq' => 1] : false; }
    function getProviderName(...$args) { return 'Fixture Provider'; }
}
''')
    rendered = subprocess.run(['php', 'eye_base.php'], cwd=target, text=True, capture_output=True)
    assert rendered.returncode == 0 and not rendered.stderr, rendered.stderr
    checked = subprocess.run(['node', '--check'], input=rendered.stdout, text=True, capture_output=True)
    assert checked.returncode == 0, checked.stderr
print(f'PASS: {len(scripts)} JavaScript files and the complete rendered eye_base.php parse successfully.')
