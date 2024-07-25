<?php
require_once('/var/www/localhost/htdocs/openemr/vendor/autoload.php');
// Set up default configuration settings
$installSettings = array();
$installSettings['iuser']                    = 'admin';
$installSettings['iuname']                   = 'Administrator';
$installSettings['iuserpass']                = 'pass';
$installSettings['igroup']                   = 'Default';
$installSettings['server']                   = 'localhost'; // mysql server
$installSettings['loginhost']                = 'localhost'; // php/apache server
$installSettings['port']                     = '3306';
$installSettings['root']                     = 'root';
$installSettings['rootpass']                 = 'BLANK';
$installSettings['login']                    = 'openemr';
$installSettings['pass']                     = 'openemr';
$installSettings['dbname']                   = 'openemr';
$installSettings['collate']                  = 'utf8mb4_general_ci';
$installSettings['site']                     = 'default';
$installSettings['source_site_id']           = 'BLANK';
$installSettings['clone_database']           = 'BLANK';
$installSettings['no_root_db_access']        = 'BLANK';
$installSettings['development_translations'] = 'BLANK';
// Collect parameters(if exist) for installation configuration settings
for ($i=1; $i < count($argv); $i++) {
    $indexandvalue = explode("=", $argv[$i]);
    $index = $indexandvalue[0];
    $value = $indexandvalue[1] ?? '';
    $installSettings[$index] = $value;
}
// Convert BLANK settings to empty
$tempInstallSettings = array();
foreach ($installSettings as $setting => $value) {
    if ($value == "BLANK") {
        $value = '';
    }
    $tempInstallSettings[$setting] = $value;
}
$installSettings = $tempInstallSettings;
// Install and configure OpenEMR using the Installer class
$installer = new Installer($installSettings);
if (! $installer->quick_install()) {
  // Failed, report error
    throw new Exception("ERROR: " . $installer->error_message . "\n");
} else {
  // Successful
    echo $installer->debug_message . "\n";
}
