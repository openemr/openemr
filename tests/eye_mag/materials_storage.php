<?php
/** Optional real-database regression. Uses only a connection-local TEMPORARY table. */
namespace EyeMaterialsStorageTest;

if (empty($argv[1])) {
    fwrite(STDERR, "Usage: php materials_storage.php /path/to/site/sqlconf.php\n");
    exit(1);
}
require $argv[1];
\mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = new \mysqli($host, $login, $pass, $dbase, (int) ($port ?? 3306));
$db->set_charset('utf8mb4');
$db->query('CREATE TEMPORARY TABLE eye_materials_test (
    form_id BIGINT NOT NULL PRIMARY KEY, pid BIGINT NOT NULL, content LONGTEXT NOT NULL,
    include_text TINYINT NOT NULL DEFAULT 0, handout_document_id BIGINT NULL, handout_document_ids LONGTEXT NULL, updated_at DATETIME NOT NULL)');

function check($value, $message) { if (!$value) throw new \RuntimeException($message); }
function sqlStatement($sql, $params = []) {
    global $db;
    if (strpos($sql, 'CREATE TABLE IF NOT EXISTS') === 0) return true;
    $sql = str_replace('form_eye_mag_materials', 'eye_materials_test', $sql);
    $statement = $db->prepare($sql);
    if ($params) $statement->bind_param(str_repeat('s', count($params)), ...$params);
    $statement->execute();
    return $statement;
}
function sqlQuery($sql, $params = []) {
    if (strpos($sql, 'FROM documents') !== false) {
        return $params[1] === 2 && in_array($params[0], [10,20,30], true) ? ['id'=>$params[0]] : [];
    }
    return sqlStatement($sql, $params)->get_result()->fetch_assoc() ?: [];
}
$source = file_get_contents(__DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php');
$start = strpos($source, 'function eye_mag_material_ids(');
$end = strpos($source, '/**' . "\n * Read once per report request.", $start);
eval('namespace EyeMaterialsStorageTest; ' . substr($source, $start, $end - $start));

// Exercise actual SQL save/reload using the materials table schema.

check((bool) $db->query("SHOW COLUMNS FROM eye_materials_test LIKE 'handout_document_ids'")->fetch_assoc(), 'Materials schema includes list column');
$db->query("INSERT INTO eye_materials_test (form_id,pid,content,handout_document_id,updated_at) VALUES (1,2,'legacy',10,NOW())");
$load = static function () { return sqlQuery('SELECT * FROM form_eye_mag_materials WHERE form_id=? AND pid=?', [1,2]); };
check(eye_mag_material_ids($load()) === [10], 'Legacy NULL-list row loads');
eye_mag_save_materials(1,2,['MATERIALS_TO_PRINT'=>'Fixture instructions', 'MATERIALS_TEXT_INCLUDED'=>1, 'MATERIALS_HANDOUT_IDS'=>'[10,20,30,20,999]']);
check(eye_mag_material_ids($load()) === [10,20,30], 'Three PDFs survive database round-trip');
check($load()['content'] === 'Fixture instructions' && (int) $load()['include_text'] === 1, 'Text retained alongside PDFs');
eye_mag_save_materials(1,2,['MATERIALS_TO_PRINT'=>'Fixture instructions', 'MATERIALS_HANDOUT_ID'=>10]);
check(eye_mag_material_ids($load()) === [10,20,30], 'Old tabs cannot discard multiple PDFs');
eye_mag_save_materials(1,2,['MATERIALS_TO_PRINT'=>'Fixture instructions', 'MATERIALS_HANDOUT_IDS'=>'[10,30]']);
check(eye_mag_material_ids($load()) === [10,30], 'Removing one persists its siblings');
eye_mag_save_materials(1,2,['MATERIALS_TO_PRINT'=>'Fixture instructions', 'MATERIALS_HANDOUT_IDS'=>'[]']);
check(eye_mag_material_ids($load()) === [] && $load()['handout_document_id'] === null, 'Empty list and cleared legacy value persist');
$db->close(); // The temporary table is automatically discarded.
echo "PASS: real database save/reload, three PDFs, legacy compatibility and individual/all removal; temporary table only.\n";
