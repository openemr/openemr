<?php
/**
 *  You can see the default application configuration options here
 *  as well as how the individual module.config.php files should operate.
 *  @see https://docs.zendframework.com/zend-mvc/services/
 *
 */
// The required application modules we need to load all the time are listed here.
$core_modules = [
    'Zend\Router', // this was separated from the MVC module into it's own module.  Handles the routing layers.
    'Zend\Validator',  // Handles validations....
    'Zend\Mvc\I18n', // TODO: since translations seem to be using the OPENEMR translations we may not need this anymore though we load it as a factory
    'Zend\Form', // needed for the formHelper view plugin.
    'Application', // Main application module starting point.
    'Installer', // Handles the dynamic adding / removing of modules in the system.
    'Acl', // Handles all of the permission checks in the system.
    'Carecoordination', // Handles import / export of CCR,CDA Immunization, Syndromicsurveillance
    'Ccr', // Module specific code for dealing with CCR import/export
    'Documents', // Handles the loading / creating of documents
    'Immunization',
    'Syndromicsurveillance',
    'Patientvalidation', // Validates patients for duplicate records
    'Multipledb', // Allows multiple database handlers within the module system
    'PrescriptionTemplates' // Handles the printing / displaying of prescriptions.
];

// TODO: if we ever want to load module definitions from the database we would do that there...
$plugin_modules = [];

return [
    'modules' =>  array_merge($core_modules, $plugin_modules)
    ,'module_listener_options' =>  [
        'config_glob_paths' =>  [
            realpath(__DIR__) . '/autoload/{,*.}{global,local}.php',
        ],
        'module_paths' =>  [
            './module',
                // yes this means you can install modules through composer... but you have to either include them into core modules
                // array up above or register them in the database.
            './vendor',
        ],
    ]
];
