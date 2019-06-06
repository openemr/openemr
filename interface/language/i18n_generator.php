<?php

require_once dirname(__FILE__) . "/../../vendor/autoload.php";

require_once(dirname(__FILE__) . "/../../library/sqlconf.php");
require_once(dirname(__FILE__) . "/../../library/sql.inc");

$languages = sqlStatement("SELECT * FROM `lang_languages`");

if (!empty($languages) && !is_dir(dirname(__FILE__) . '/i18n/')) {
    mkdir(dirname(__FILE__) . '/i18n/');
}

while ($lang = SqlFetchArray($languages)) {

    if (!is_file(dirname(__FILE__)) . '/i18n/' . $lang['lang_code'] . '.json') {
        touch(dirname(__FILE__) . '/i18n/' . $lang['lang_code'] . '.json');
    }
    $sql = "SELECT c.constant_name, d.definition FROM lang_definitions as d  
            JOIN lang_constants AS c ON d.cons_id = c.cons_id 
            WHERE d.lang_id = ?";
    $tarns = sqlStatement($sql, $lang['lang_id']);
    $json = array();
    while ($row = SqlFetchArray($tarns)) {
        $json[$row['constant_name']] = $row['definition'];
    }
    file_put_contents(dirname(__FILE__) . '/i18n/' . $lang['lang_code'] . '.json', json_encode($json, JSON_UNESCAPED_UNICODE));

}

#update globals for refresh cache
sqlStatement("REPLACE INTO `globals` (`gl_name`, `gl_index`, `gl_value`) VALUES ('i18n_updated', '0', '1')");