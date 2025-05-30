<?php
/**
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sun PC Solutions LLC
 * @copyright Copyright (c) 2025 Sun PC Solutions LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
 * Standard Zend Framework Module class for openemrAudio2Note.
 * This is the primary entry point for the Zend ModuleManager.
 */

namespace OpenEMR\Modules\OpenemrAudio2Note;

use Zend\ModuleManager\ModuleManager;

class Module
{
    /**
     * Initialize method.
     * Called by the Zend ModuleManager during the application bootstrap process.
     * This is the place to register event listeners.
     *
     * @param ModuleManager $moduleManager
     */
    public function init(ModuleManager $moduleManager)
    {
        // This method is called by the Zend ModuleManager during application bootstrap.
        // Event listeners or other initialization logic can be placed here.
    }

    /**
     * Returns module configuration.
     * This configuration is merged with other modules' configurations.
     *
     * @return array
     */
    public function getConfig()
    {
        // Configuration for this module can be loaded from a file (e.g., config/module.config.php)
        // or defined directly here. For this module, configuration is primarily handled
        // by the `config.php` at the module's root and `moduleConfig.php` for settings.
        return [];
    }

    // Additional methods like getAutoloaderConfig() or getServiceConfig()
    // can be implemented if the module requires more complex service management or autoloading.
}