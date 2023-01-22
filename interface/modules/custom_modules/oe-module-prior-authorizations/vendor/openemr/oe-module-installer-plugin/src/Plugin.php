<?php

namespace OpenEMR\Composer\ModuleInstallerPlugin;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

class Plugin implements PluginInterface {
    public function activate(Composer $composer, IOInterface $io)
    {
        $installer = new CustomModuleInstaller($io, $composer);
        $composer->getInstallationManager()->addInstaller($installer);

        $installer = new ZendModuleInstaller($io, $composer);
        $composer->getInstallationManager()->addInstaller($installer);
    }
}
