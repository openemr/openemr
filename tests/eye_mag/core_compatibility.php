<?php

/** Exercise real OpenEMR session/CSRF/enums with fixture records, without loading a site. */
namespace EyeCoreCompatibilityTest;

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Forms\EyeMag\CopyForward;
use OpenEMR\Forms\EyeMag\CopyMode;
use OpenEMR\Forms\EyeMag\Zone;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

if (empty($argv[1])) {
    fwrite(STDERR, "Usage: php core_compatibility.php /path/to/openemr\n");
    exit(1);
}
$loader = require rtrim($argv[1], '/') . '/vendor/autoload.php';
$eyeRoot = getenv('EYE_TEST_CORE') ?: dirname(__DIR__, 2);
$eyeClassDirectory = is_dir($eyeRoot . '/src/Forms/EyeMag') ? $eyeRoot . '/src/Forms/EyeMag' : rtrim($argv[1], '/') . '/src/Forms/EyeMag';
$eyeClasses = [];
foreach (glob($eyeClassDirectory . '/*.php') as $classFile) {
    $eyeClasses['OpenEMR\\Forms\\EyeMag\\' . basename($classFile, '.php')] = $classFile;
}
$loader->addClassMap($eyeClasses);

function check($value, $message): void {
    if (!$value) throw new \RuntimeException($message);
}

foreach ([CopyForward::class, CopyMode::class, Zone::class] as $class) {
    check(str_starts_with((new \ReflectionClass($class))->getFileName(), $eyeClassDirectory . '/'), 'Use the target upstream Eye class: ' . $class);
}

$session = new Session(new MockArraySessionStorage());
CsrfUtils::setupCsrfKey($session);
$token = CsrfUtils::collectCsrfToken($session);
check(CsrfUtils::verifyCsrfToken($token, $session), 'Core session token accepted');
check(!CsrfUtils::verifyCsrfToken('wrong', $session), 'Wrong token rejected');
check(!CsrfUtils::verifyCsrfToken('', $session), 'Missing token rejected');
$otherSession = new Session(new MockArraySessionStorage());
CsrfUtils::setupCsrfKey($otherSession);
check(!CsrfUtils::verifyCsrfToken($token, $otherSession), 'Other-session token rejected');

class QueryUtils {
    public static function querySingleRow($sql, $params) {
        $GLOBALS['copyQuery'] = [$sql, $params];
        return $GLOBALS['copyRecord'];
    }
}
function build_IMPPLAN_items($pid, $formId) { return []; }
$source = file_get_contents(__DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php');
$start = strpos($source, 'function copy_forward(');
$end = strpos($source, "\nfunction ", $start + 1);
check($start !== false && $end !== false, 'Copy-forward function located');
eval('namespace EyeCoreCompatibilityTest; use OpenEMR\\Forms\\EyeMag\\CopyForward; use OpenEMR\\Forms\\EyeMag\\CopyMode; use OpenEMR\\Forms\\EyeMag\\Zone; ' . substr($source, $start, $end - $start));

$copyRecord = [
    'STEREOPSIS' => 'legacy', 'STEREO_FLY' => 'Yes', 'STEREO_ANIMALS' => '3',
    'STEREO_CIRCLES' => '9', 'STEREO_FIXATION_PREF' => 'OU', 'not_a_chart_field' => 'excluded',
];
foreach ([Zone::NEURO, CopyMode::ALL] as $mode) {
    ob_start();
    copy_forward($mode, '8 OR 1=1', '10');
    $result = json_decode(ob_get_clean(), true, 512, JSON_THROW_ON_ERROR);
    check($copyQuery === [CopyForward::RECORD_QUERY, ['10', '8 OR 1=1']], 'Patient and source remain bound to the core query');
    foreach (['STEREO_FLY', 'STEREO_ANIMALS', 'STEREO_CIRCLES', 'STEREO_FIXATION_PREF'] as $field) {
        check($result[$field] === $copyRecord[$field], 'New stereopsis field preserved: ' . $field);
    }
    check(!isset($result['not_a_chart_field']), 'Copy still uses an explicit field list');
}
$copyRecord = null;
ob_start();
copy_forward(Zone::NEURO, '8', '99');
check(json_decode(ob_get_clean(), true) === [], 'Missing source for active patient returns no data');

echo "PASS: actual core session/CSRF APIs and typed copy-forward enums; patient-bound SQL and new stereopsis fields preserved.\n";
