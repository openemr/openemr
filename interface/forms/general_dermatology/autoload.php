<?php
/**
 * Autoloader for General Dermatology module
 *
 * This file provides PSR-4 compatible autoloading for the General Dermatology module.
 */

// Define the module name constant for path construction
define('MODULE_NAME', 'general_dermatology');

/**
 * PSR-4 compatible autoloader for GeneralDermatology namespace
 */
spl_autoload_register(function ($class) {
    // Only handle classes in the GeneralDermatology namespace
    $prefix = 'GeneralDermatology\\';
    $len = strlen($prefix);
    
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    // Get the relative class name
    $relative_class = substr($class, $len);
    
    // Convert namespace separators to directory separators
    $file = __DIR__ . '/src/' . str_replace('\\', '/', $relative_class) . '.php';
    
    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});
