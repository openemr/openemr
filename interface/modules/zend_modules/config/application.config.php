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
    'Application', // Main application module starting point.
    'Installer', // Handles the dynamic adding / removing of modules in the system.
    'Acl', // Handles all of the permission checks in the system.
    'FHIR', // Handles FHIR mapped uuid population and other FHIR utility functions
    'CodeTypes', // Handles CodeType mappings and anything else to do with the system of dealing with code types
    'PatientFlowBoard', // Handle any functionality needed for the patient flow board
];

// $zendConfigurationPath is loaded using ModulesApplication.php from globals.php
$plugin_modules = \OpenEMR\Core\ModulesApplication::oemr_zend_load_modules_from_db(
    $webRootPath ?? '',
    $zendConfigurationPath ?? ''
);
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
