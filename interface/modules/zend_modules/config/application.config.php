<?php

/**
 *  You can see the default application configuration options here
 *  as well as how the individual module.config.php files should operate.
 *  @see https://docs.laminas.dev/laminas-mvc/services/
 *
 */

// The required application modules we need to load all the time are listed here.
$core_modules = [
    'Laminas\Router', // this was separated from the MVC module into it's own module.  Handles the routing layers.
    'Laminas\Validator',  // Handles validations....
    'Laminas\Mvc\I18n', // TODO: since translations seem to be using the OPENEMR translations we may not need this anymore though we load it as a factory
    'Laminas\Form', // needed for the formHelper view plugin.
    'Laminas\Mvc\Console', // Used for console commands. TODO: Deprecated and plan to remove in the future (plan to upgrade to zf-console)
    'Application', // Main application module starting point.
    'Installer', // Handles the dynamic adding / removing of modules in the system.
    'Acl', // Handles all of the permission checks in the system.
];

/**
 * Grabs the actively enabled modules from the database and injects them into the system.
 * For the list of active modules you can see them from the modules installer tab, or by querying the modules table
 * Otherwise the modules are found inside the modules/zend_modules folder.  The uninstalled script will dynamically find them
 * in the filesystem.
 */
function oemr_zend_load_modules_from_db()
{
    // we skip the audit log as it has no bearing on user activity and is core system related...
    $resultSet = sqlStatementNoLog($statement = "SELECT mod_name FROM modules WHERE mod_active = 1 AND type = 1 ORDER BY `mod_ui_order`, `date`");
    $db_modules = [];
    while ($row = sqlFetchArray($resultSet)) {
        $db_modules[] = $row["mod_name"];
    }
    return $db_modules;
}
$plugin_modules = oemr_zend_load_modules_from_db();
$vendor_path = !empty($GLOBALS['vendor_dir']) ? $GLOBALS['vendor_dir'] : (realpath(__DIR__) . '/../vendor');

return [
    'modules' =>  array_merge($core_modules, $plugin_modules)
    ,'module_listener_options' =>  [
        'config_glob_paths' =>  [
            realpath(__DIR__) . '/autoload/{,*.}{global,local}.php',
        ],
        'module_paths' =>  [
            realpath(__DIR__) . '/../module',
                // yes this means you can install modules through composer... but you have to either include them into core modules
                // array up above
            $vendor_path
        ],
    ],
    'service_manager' => []
];
