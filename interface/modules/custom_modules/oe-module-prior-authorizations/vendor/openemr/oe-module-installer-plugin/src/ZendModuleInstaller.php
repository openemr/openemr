<?php

namespace OpenEMR\Composer\ModuleInstallerPlugin;

use Composer\Package\PackageInterface;
use Composer\Installer\LibraryInstaller;

class ZendModuleInstaller extends LibraryInstaller
{
    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package)
    {
	$packageName = $package->getPrettyName();
	$folderPaths = explode('/', $packageName);
	$moduleName = end($folderPaths);
        return 'interface' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'zend' 
		. DIRECTORY_SEPARATOR  . 'modules' . DIRECTORY_SEPARATOR . $moduleName;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return 'openemr-zend-module' === $packageType;
    }
}
