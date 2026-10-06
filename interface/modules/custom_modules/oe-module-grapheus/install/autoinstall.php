<?php

/**
 * Installs and enables Grapheus without clicking through Module Manager.
 * Used by the "OpenEMR + Grapheus" distribution on first start, and safe to
 * run again (it only adds what is missing).
 *
 *   php autoinstall.php [site]
 *
 * @package   Grapheus
 * @copyright Copyright (c) 2026 Exetazo Health
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

if (PHP_SAPI !== 'cli') {
    exit(1);
}
$_GET['site'] = $argv[1] ?? 'default';
$ignoreAuth = true;
require_once dirname(__DIR__, 4) . '/globals.php';

$dir = 'oe-module-grapheus';
$sql = file_get_contents(dirname(__DIR__) . '/table.sql');
$sql = preg_replace('/^\s*(#|--).*$/m', '', $sql);
foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
    sqlStatementNoLog($stmt);
}
$row = sqlQuery("SELECT mod_id FROM modules WHERE mod_directory = ?", [$dir]);
if (empty($row['mod_id'])) {
    sqlStatementNoLog(
        "INSERT INTO modules (mod_name, mod_directory, mod_parent, mod_type, mod_active, mod_ui_name, mod_relative_link, mod_ui_order, mod_ui_active, mod_description, mod_nick_name, mod_enc_menu, permissions_item_table, directory, date, sql_run, type, sql_version, acl_version)
         VALUES ('Grapheus AI Scribe', ?, '', '', 1, 'Grapheus', 'public/assistant.php', 0, 0, 'Grapheus by Exetazo', '', 'no', NULL, '', NOW(), 1, 0, '1', '')",
        [$dir]
    );
    echo "Grapheus registered, installed and enabled.\n";
} else {
    sqlStatementNoLog("UPDATE modules SET mod_active = 1, sql_run = 1 WHERE mod_id = ?", [$row['mod_id']]);
    echo "Grapheus already registered; enabled.\n";
}
