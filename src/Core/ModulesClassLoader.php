<?php

/**
 * ModulesClassLoader is made available in every custom_module's openemr.bootstrap.php file as the variable $classLoader.
 * Modules can use the ModulesClassLoader to register their namespace in order to auto load their classes.  This facilitates
 * modules being dropped into the filesystem without having to go through a composer install / dumping of the autoloader.
 *
 * The class loader will check to make sure a namespace has not already been registered in case the module is installed
 * via composer and is already in the namespace heirarchy.  Note this means each module needs to have its own DISTINCT
 * namespace and should not share a namespace as the very first module that registers with the namespace is the one
 * made available to OpenEMR.
 *
 * For performance reasons, if a module writer wishes to avoid hitting the filesystem for class discovery, they can use
 * the registerClassmap function to optimize their runtime performance.
 *
 * An example use in an openemr.bootstrap.php file is as follows:
 * <example>
 * // openemr.bootstrap.php
 * namespace Acme\OpenEMR\Modules\MyUniqueModule;
 * use OpenEMR\Core\ModulesClassLoader;
 * // @global ModulesClassLoader $classLoader
 * $classLoader->registerNamespaceIfNotExists('Acme\\OpenEMR\\Modules\\MyUniqueModule\\', __DIR__ . DIRECTORY_SEPARATOR . 'src');
 * // run any other custom module code needed here.
 * </example>
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2019 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Core;

use Composer\Autoload\ClassLoader;

class ModulesClassLoader
{
    /**
     * @var ClassLoader
     */
    private $classLoader;

    public function __construct($webRootPath)
    {
        $this->classLoader = require $webRootPath . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
    }

    /**
     * Registers a set of PSR-4 directories for a given namespace.  If the namespace already exists it skips registering
     * the namespace (such as if the module has been installed via the main composer.json file)
     *
     * @param string          $prefix  The prefix/namespace, with trailing '\\'
     * @param string[]|string $paths   The PSR-4 base directories to
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    public function registerNamespaceIfNotExists($namespace, $paths)
    {
        $prefixes = $this->classLoader->getPrefixesPsr4();
        if (empty($prefixes[$namespace])) {
            $this->classLoader->addPsr4($namespace, $paths);
            return true;
        }
        return false;
    }

    /**
     * @param string[] $classMap Class to filename map
     * @psalm-param array<string, string> $classMap
     *
     * @return void
     */
    public function registerClassmap($classMap)
    {
        $this->classLoader->addClassMap($classMap);
    }
}
