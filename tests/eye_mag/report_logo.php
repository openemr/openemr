<?php
/** Header rendering fixtures; no patient database or practice assets are read. */
namespace EyeLogoTest;

class SessionWrapperFactory {
    public static function getInstance() { return new self(); }
    public function getActiveSession() { return $this; }
    public function get($key) { return null; }
}
class OEGlobalsBag {
    public static function getInstance() { return new self(); }
    public function get($key) { return '/openemr'; }
    public function getWebRoot() { return '/openemr'; }
}
function attr($value) { return htmlspecialchars((string) $value, ENT_QUOTES); }
function text($value) { return attr($value); }
function xlt($value) { return attr($value); }
function oeFormatShortDate($value = '') { return '10/11/2026'; }
function oeFormatSDFT($value) { return '10/11/2026'; }
function getEncounterDateByEncounter($value) { return ['date' => '2026-10-11']; }
function getProviderIdOfEncounter($value) { return 1; }
function getProviderName($value) { return 'Fixture Provider'; }
function getPatientData($pid, $fields) { return ['fname'=>'Fixture','lname'=>'Patient','DOB'=>'2000-01-01']; }
function file_exists($path) { return basename($path) === ($GLOBALS['fixtureLogo'] ?? ''); }
function filemtime($path) { return 123; }
function check($condition, $message) { if (!$condition) throw new \RuntimeException($message); }

$source = \file_get_contents(__DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php');
$start = strpos($source, 'function report_header(');
$end = strpos($source, "\n/**", $start);
eval('namespace EyeLogoTest; ' . substr($source, $start, $end - $start));
$encounter = 1;
$OE_SITE_DIR = '/fixture/site';
class FacilityService {
    public function getPrimaryBillingLocation() {
        return ['name'=>'Fixture Practice','street'=>'100 Test Street','city'=>'Test City',
            'state'=>'MA','postal_code'=>'00000','phone'=>'555-0100','fax'=>'555-0101'];
    }
}
$checks = 0;
foreach ([['web','practice_logo.gif',true],['web','practice_logo.gif',false],
    ['web','',true],['PDF','practice_logo.gif',false],['PDF','login_logo.gif',false],['PDF','',false]] as $case) {
    [$direction,$fixtureLogo,$edit] = $case;
    $html = report_header(1, $direction, $edit);
    $dom = new \DOMDocument();
    @$dom->loadHTML($html);
    $cells = $dom->getElementsByTagName('td');
    check($cells->length === 3, 'Retain three header columns');
    check(strpos($cells->item(0)->getAttribute('style'), 'width:33%') !== false, 'Logo gets one third');
    $images = $dom->getElementsByTagName('img');
    check($images->length === ($fixtureLogo ? 1 : 0), 'Missing/fallback logo handling');
    if ($fixtureLogo) {
        $style = $images->item(0)->getAttribute('style');
        check(strpos($style, 'height:auto') !== false, 'Preserve logo proportions');
        check(strpos($style, $direction === 'web' ? 'width:100%' : 'width:55mm') !== false, 'Use larger logo width');
        check(!preg_match('/width:(72|100|150)px/', $style), 'No old small-logo cap');
    }
    check($dom->getElementsByTagName('a')->length === ($direction === 'web' && $edit ? 1 : 0), 'Edit permission/print behavior unchanged');
    if ($direction === 'PDF' && $fixtureLogo === 'practice_logo.gif') $pdfHeader = $html;
    $checks++;
}
$report = \file_get_contents(__DIR__ . '/../../interface/forms/eye_mag/report.php');
check(strpos($report, 'width:72px;opacity:0.8;') === false, 'PDF must not shrink or fade the logo again');
echo "PASS: $checks report/Rx header fixtures, including fallback/no logo and Edit visibility.\n";

// Optional smoke render using the installed OpenEMR mPDF dependency.
if (!empty($argv[1])) {
    require $argv[1];
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="300" height="100"><rect width="300" height="100" fill="#286070"/><text x="12" y="60" fill="#fff" font-size="28">Practice Logo</text></svg>';
    $pdfHeader = preg_replace("~src='[^']*'~", "src='data:image/svg+xml;base64," . base64_encode($svg) . "'", $pdfHeader);
    $pdf = new \Mpdf\Mpdf(['tempDir'=>sys_get_temp_dir() . '/eye-logo-mpdf-test','format'=>'Letter']);
    $pdf->WriteHTML($pdfHeader);
    $bytes = $pdf->Output('', 'S');
    check(strpos($bytes, '%PDF-') === 0, 'PDF renderer output');
    check($pdf->page === 1, 'Header should fit one page');
    echo "PASS: mPDF renders the enlarged logo header on one page.\n";
}
