<?php
namespace EyePhotoTest;

function check($value, $message) { if (!$value) throw new \RuntimeException($message); }
class OEGlobalsBag {
    public static function getInstance() { return new self(); }
    public function getString($key) {
        check($key === 'patient_photo_category_name', 'Read configured photo category');
        return $GLOBALS['photoCategory'];
    }
}

function sqlQuery($sql, $parameters) {
    $GLOBALS['photoQuery'] = [$sql, $parameters];
    return $GLOBALS['photoResult'];
}
$source = file_get_contents(__DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php');
$start = strpos($source, 'function eye_mag_patient_photo(');
$end = strpos($source, 'function menu_overhaul_left(', $start);
eval('namespace EyePhotoTest; ' . substr($source, $start, $end - $start));
$photoResult = ['id' => 91, 'mimetype' => 'image/jpeg'];
foreach (['Patient Photograph', 'Portrait', 'Foto del paciente', '患者の写真', 'صورة المريض', 'Photo - Eye', 'Portrait_100%'] as $photoCategory) {
    check(eye_mag_patient_photo(7) === $photoResult, 'Configured photo returned');
    [$sql, $params] = $photoQuery;
    check($params === [$photoCategory, 7], 'Keep exact configured Unicode name and patient ID');
    check(strpos($sql, 'category.name = ?') !== false && stripos($sql, 'LIKE') === false, 'No wildcard interpretation');
    check(strpos($sql, 'doc.deleted = 0') !== false, 'Exclude deleted photos');
    check(strpos($sql, 'ORDER BY doc.id DESC LIMIT 1') !== false, 'Consistent latest-photo selection');
}
$photoResult = false;
check(eye_mag_patient_photo(7) === [], 'Missing photo is safe');
foreach (['', '   '] as $photoCategory) {
    $photoQuery = null;
    check(eye_mag_patient_photo(7) === [] && $photoQuery === null, 'Empty configuration does not fall back to English');
}
$photoCategory = 'Portrait';
$photoQuery = null;
check(eye_mag_patient_photo(0) === [] && $photoQuery === null, 'No patient means no query');
$menu = substr($source, strpos($source, 'function menu_overhaul_left('));
check(strpos($menu, '$patientPhoto = eye_mag_patient_photo($pid)') !== false, 'Screen uses shared lookup');
check(strpos($menu, "['docs_in_name']['Patient Photograph']") === false, 'No hardcoded English photo category');
$report = file_get_contents(__DIR__ . '/../../interface/forms/eye_mag/report.php');
check(strpos($report, '$result = eye_mag_patient_photo($pid)') !== false, 'Report uses same lookup');
echo "PASS: seven category names, literal wildcard characters, missing/blank handling, patient isolation and shared screen/report lookup.\n";
