<?php
/** Isolated fixtures plus real mPDF embedding/import checks. No clinical data. */
namespace EyeMaterialsTest;

function check($condition, $message) { if (!$condition) throw new \RuntimeException($message); }
function text($value) { return htmlspecialchars((string) $value, ENT_QUOTES); }
function xl($value) { return $value; }
function xlt($value) { return text($value); }
function report_zone_heading($value) { echo '<h2>' . text($value) . '</h2>'; }
function sqlStatement($sql, $params = []) {
    if (strpos($sql, 'REPLACE INTO form_eye_mag_materials') !== false) {
        [$formId, $pid, $content, $include, $legacy, $ids] = $params;
        $GLOBALS['rows'][$pid . ':' . $formId] = ['content'=>$content, 'include_text'=>$include,
            'handout_document_id'=>$legacy, 'handout_document_ids'=>$ids];
    }
    return true;
}
function sqlQuery($sql, $params = []) {
    if (strpos($sql, 'FROM form_eye_mag_materials') !== false) {
        return $GLOBALS['rows'][$params[1] . ':' . $params[0]] ?? [];
    }
    check(strpos($sql, 'd.deleted = 0') !== false, 'Exclude deleted originals');
    check(strpos($sql, "c.name = 'Handouts'") !== false, 'Only education handouts');
    check(strpos($sql, 'd.foreign_id = ? OR d.foreign_id IN (0, -1)') !== false, 'Constrain patient/practice ownership');
    $doc = $GLOBALS['documents'][$params[0]] ?? [];
    return !empty($doc['deleted']) || !in_array($doc['pid'] ?? null, [0, -1, $params[1]], true) ? [] : $doc;
}
class Document {
    private $id;
    public function __construct($id) { $this->id = $id; }
    public function get_data() {
        $GLOBALS['reads'][$this->id] = ($GLOBALS['reads'][$this->id] ?? 0) + 1;
        $value = $GLOBALS['documents'][$this->id]['bytes'];
        if ($value instanceof \Throwable) throw $value;
        return $value;
    }
}
class OEGlobalsBag {
    public static function getInstance() { return new self(); }
    public function get($key) { return sys_get_temp_dir(); }
}
class PdfFixture {
    public $associatedFiles = [];
    public function SetAssociatedFiles($files) { $this->associatedFiles = $files; }
}
function fixture($formId, $text = '', $include = 0, $document = null, $pid = 2) {
    $GLOBALS['rows'][$pid . ':' . $formId] = ['content'=>$text, 'include_text'=>$include, 'handout_document_id'=>$document];
}
function render($formId, $output = 1, $localPdf = null, $pid = 2) {
    $GLOBALS['PDF_OUTPUT'] = $output;
    ob_start();
    eye_mag_report_materials_text($pid, $formId, $localPdf);
    return ob_get_clean();
}
function embeddedBytes($bytes) {
    preg_match_all('~<<\s*/Type /EmbeddedFile\s.*?/Length (\d+).*?\nstream\r?\n~s', $bytes, $matches, PREG_OFFSET_CAPTURE);
    $files = [];
    foreach ($matches[0] as $index => $match) {
        $stream = substr($bytes, $match[1] + strlen($match[0]), (int) $matches[1][$index][0]);
        $files[] = gzuncompress($stream);
    }
    return $files;
}

$source = file_get_contents(__DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php');
$start = strpos($source, 'function eye_mag_material_ids(');
$end = strpos($source, '/** Return the six fixed dilation slots', $start);
eval('namespace EyeMaterialsTest; ' . substr($source, $start, $end - $start));
$report = file_get_contents(__DIR__ . '/../../interface/forms/eye_mag/report.php');
$start = strpos($report, 'function eye_mag_report_materials_text(');
$end = strpos($report, 'function display_draw_image(', $start);
eval('namespace EyeMaterialsTest; ' . substr($report, $start, $end - $start));

$pdf = new PdfFixture(); // Core custom report owns this global.
fixture(1);
check(render(1) === '', 'No materials: no heading or pagebreak');
fixture(2, 'Draft not added', 0);
check(render(2) === '', 'Unselected text must not be included');
fixture(3, "  Instructions <test>\nLine two  ", 1);
$html = render(3);
check(strpos($html, 'Patient instructions included') !== false && strpos($html, '&lt;test&gt;') !== false, 'Text acknowledged and escaped');
check($pdf->associatedFiles[0]['content'] === "  Instructions <test>\nLine two  ", 'Exact original text retained');
check(count($pdf->associatedFiles) === 1, 'Text-only attachment');
$documents[10] = ['id'=>10,'name'=>'Handout <A>.pdf','url'=>'file:///fixture/10.pdf','pid'=>0,'bytes'=>"%PDF-1.4\noriginal bytes"];
fixture(4, '', 0, 10);
$html = render(4);
check(strpos($html, 'Patient PDF instructions included') !== false, 'PDF-only receipt');
check(strpos($html, 'Handout &lt;A&gt;.pdf') !== false && strpos($html, 'Attachments panel') !== false, 'Name escaped, attachment discoverable');
check(count($pdf->associatedFiles) === 2, 'Existing attachments retained');
render(4);
check(count($pdf->associatedFiles) === 2 && $reads[10] === 1, 'No duplicates and read once per report');
$documents[10]['bytes'] = "%PDF-1.4\nchanged master";
$snapshot = eye_mag_materials_for_report(4, 2);
check($snapshot['handouts'][0]['pdf_data'] === "%PDF-1.4\noriginal bytes", 'One report never mixes master versions');
fixture(5, 'Also includes text', 1, 10);
$localPdf = new PdfFixture();
$html = render(5, 1, $localPdf);
check(count($localPdf->associatedFiles) === 2 && count($pdf->associatedFiles) === 2, 'Taskman uses explicit local mPDF');
check(strpos($html, 'Also includes text') !== false, 'Text and PDF both shown');
$html = render(5, 0);
check(strpos($html, '<pagebreak') === false && strpos($html, 'Attachments panel') === false, 'HTML does not claim embedded PDF');
foreach ([11=>['deleted'=>1,'bytes'=>'%PDF-deleted'], 12=>['pid'=>99,'bytes'=>'%PDF-other patient'],
    13=>['bytes'=>'not a PDF'], 14=>['bytes'=>new \RuntimeException('Cannot decrypt')]] as $docId => $overrides) {
    $documents[$docId] = array_replace($documents[10], ['id'=>$docId], $overrides);
    fixture($docId, '', 0, $docId);
    $html = render($docId);
    check(strpos($html, 'could not be included') !== false, 'Unavailable document has visible warning');
    check(strpos($html, 'Patient PDF instructions included') === false, 'Never acknowledge unavailable content as included');
}
fixture(15, '', 0, 999);
check(strpos(render(15), 'could not be included') !== false, 'Missing source warning');
check(render(4, 1, null, 99) === '', 'Different patient cannot use cached snapshot');
fixture(16, '', 0, 10);
check(strpos(render(16, 1, new \stdClass()), 'could not be included') !== false, 'Unsupported renderer does not claim attachment');
$failingImporter = new class extends PdfFixture {
    public function setSourceFile($path) { throw new \RuntimeException('Unsupported PDF import'); }
};
check(!eye_mag_append_materials_handout_pdf($failingImporter, 4, 2), 'Import failure reported');
check($failingImporter->associatedFiles[0]['content'] === $snapshot['handouts'][0]['pdf_data'], 'Exact original survives failed page import');

check(eye_mag_material_ids(['handout_document_id'=>10]) === [10], 'Legacy encounter retains single handout');
check(eye_mag_material_ids(['handout_document_ids'=>'[]', 'handout_document_id'=>10]) === [], 'Empty list never revives a removed legacy handout');
check(eye_mag_material_ids(['handout_document_ids'=>'[10,"11",10,-1,0,"oops",true,1.5]']) === [10,11], 'IDs normalized, deduplicated in order');
$documents[17] = array_replace($documents[10], ['id'=>17,'bytes'=>"%PDF-1.4\nanother original"]);
fixture(30, '', 0, 10);
eye_mag_save_materials(30, 2, ['MATERIALS_TO_PRINT'=>'instructions', 'MATERIALS_TEXT_INCLUDED'=>1,
    'MATERIALS_HANDOUT_IDS'=>'[10,17,10,12,999]']);
check(eye_mag_material_ids($rows['2:30']) === [10,17], 'Save rejects other-patient and missing new sources, preserves first and second');
$multiplePdf = new PdfFixture();
$html = render(30, 1, $multiplePdf);
check(substr_count($html, 'Patient PDF instructions included') === 2, 'Both PDFs acknowledged');
check(count($multiplePdf->associatedFiles) === 3, 'Both PDFs and text embedded');
check(count(array_unique(array_column($multiplePdf->associatedFiles, 'name'))) === 3, 'Same display names cannot collide');
eye_mag_save_materials(30, 2, ['MATERIALS_TO_PRINT'=>'instructions', 'MATERIALS_HANDOUT_ID'=>10]);
check(eye_mag_material_ids($rows['2:30']) === [10,17], 'Old tab cannot collapse list');
eye_mag_save_materials(30, 2, ['MATERIALS_HANDOUT_IDS'=>'bad JSON']);
check(eye_mag_material_ids($rows['2:30']) === [10,17], 'Malformed data cannot erase selections');
eye_mag_save_materials(30, 2, ['MATERIALS_HANDOUT_IDS'=>'[17]']);
check(eye_mag_material_ids($rows['2:30']) === [17], 'Individual removal persists');
eye_mag_save_materials(30, 2, ['MATERIALS_HANDOUT_IDS'=>'[]']);
check(eye_mag_material_ids($rows['2:30']) === [] && $rows['2:30']['handout_document_id'] === null, 'Remove last stays empty');
fixture(31, '', 0, 10);
$rows['2:31']['handout_document_ids'] = '[10,11,17]';
$mixedPdf = new PdfFixture();
$html = render(31, 1, $mixedPdf);
check(substr_count($html, 'Patient PDF instructions included') === 2 && strpos($html, 'could not be included') !== false,
    'Unavailable handout does not suppress the other two');
$recoveringImporter = new class extends PdfFixture {
    public $attempts = 0;
    public $pages = 0;
    public function setSourceFile($path) {
        if (++$this->attempts === 1) throw new \RuntimeException('Unsupported first PDF');
        return 1;
    }
    public function AddPage() { ++$this->pages; }
    public function importPage($page) { return $page; }
    public function useTemplate($template, $options) {}
};
check(!eye_mag_append_materials_handout_pdf($recoveringImporter, 31, 2), 'Partial import reports failure');
check($recoveringImporter->attempts === 2 && $recoveringImporter->pages === 1, 'Later valid PDF still appended after failed import');
$names = [];
for ($i = 0; $i < 100; ++$i) $names[] = eye_mag_report_filename(2, 395);
check(count(array_unique($names)) === 100, 'Consecutive versions have distinct filenames');
check((bool) preg_match('/^Eye_Report_2_395_\d{8}_\d{6}_[a-f0-9]{12}\.pdf$/', $names[0]), 'Safe descriptive report filename');
$save = file_get_contents(__DIR__ . '/../../interface/forms/eye_mag/save.php');
$taskman = file_get_contents(__DIR__ . '/../../interface/forms/eye_mag/php/taskman_functions.php');
$store = substr($save, strpos($save, '== "store_PDF"'), strpos($save, '// Store the IMPPLAN area.') - strpos($save, '== "store_PDF"'));
check(stripos($store, 'DELETE from documents') === false && strpos($store, 'glob(') === false, 'Store-PDF preserves earlier chart copies');
check(stripos($taskman, 'DELETE from documents') === false && strpos($taskman, 'eye_mag_report_filename') !== false, 'Taskman preserves earlier chart copies');
check(strpos($taskman, "\$form_id, 'full', \$pdf)") !== false, 'Taskman passes its actual PDF to the report');
echo "PASS: patient materials acknowledgment, permissions, exact snapshots, errors, deduplication and retained report versions.\n";

if (!empty($argv[1])) {
    require $argv[1];
    $config = ['tempDir'=>sys_get_temp_dir() . '/eye-materials-mpdf-test', 'format'=>'Letter'];
    $original = new \Mpdf\Mpdf($config);
    $original->WriteHTML('<h1>Original patient handout</h1><p>Page one.</p><pagebreak/><p>Page two.</p>');
    $originalBytes = $original->Output('', 'S');
    $documents[20] = array_replace($documents[10], ['id'=>20,'bytes'=>$originalBytes]);
    fixture(20, "Preserved text\nwith exact line breaks.", 1, 20);
    $pdf = new \Mpdf\Mpdf($config);
    $pdf->WriteHTML('<h1>Clinical report</h1>' . render(20));
    $download = $pdf->Output('', 'S');
    $embedded = embeddedBytes($download);
    check(in_array($originalBytes, $embedded, true), 'Downloaded report contains byte-identical PDF, not a link');
    check(in_array($rows['2:20']['content'], $embedded, true), 'Downloaded report contains byte-identical text');
    check(strpos($download, '/EmbeddedFiles') !== false, 'Attachments listed in PDF catalog');
    $saved = new \Mpdf\Mpdf($config);
    $saved->WriteHTML('<h1>Clinical report</h1>' . render(20, 1, $saved));
    $beforePages = $saved->page;
    check(eye_mag_append_materials_handout_pdf($saved, 20, 2), 'Saved report imports handout pages');
    check($saved->page === $beforePages + 2, 'Both handout pages appended');
    $savedBytes = $saved->Output('', 'S');
    $documents[20]['bytes'] = '%PDF-1.4 revised master';
    check(in_array($originalBytes, embeddedBytes($savedBytes), true), 'Already-generated report unaffected by master revision');
    check(count(embeddedBytes($savedBytes)) === 2, 'Saved report does not duplicate attachments');
    echo "PASS: actual mPDF download and stored-report output embed byte-identical originals; stored report also appends both handout pages.\n";

    $second = new \Mpdf\Mpdf($config);
    $second->WriteHTML('<h1>Second patient handout</h1>');
    $secondBytes = $second->Output('', 'S');
    $documents[40] = array_replace($documents[10], ['id'=>40,'bytes'=>$originalBytes]);
    $documents[41] = array_replace($documents[10], ['id'=>41,'bytes'=>$secondBytes]);
    fixture(40, 'Multiple instructions', 1);
    $rows['2:40']['handout_document_ids'] = '[40,41]';
    $multi = new \Mpdf\Mpdf($config);
    $multi->WriteHTML('<h1>Clinical report</h1>' . render(40, 1, $multi));
    $beforePages = $multi->page;
    check(eye_mag_append_materials_handout_pdf($multi, 40, 2), 'Both real PDFs import');
    check($multi->page === $beforePages + 3, 'Two-page and one-page handouts both appended');
    $files = embeddedBytes($multi->Output('', 'S'));
    check($files === ['Multiple instructions', $originalBytes, $secondBytes], 'All originals embedded once, byte-identical and in order');
    echo "PASS: multiple real PDFs with identical display names are individually acknowledged, embedded and appended.\n";
}
