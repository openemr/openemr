<?php

declare(strict_types=1);

namespace OpenEMR\Modules\Manager;

use Composer\InstalledVersions;
use OpenEMR\Modules\ModuleInterface;
use Psr\Log\LoggerInterface;

class ModuleFinder
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function listAllAvailable(): array
    {
        /*
        $modules = [];
        $iv = InstalledVersions::getAllRawData();
        foreach ($iv as $loader) {
            foreach ($loader['versions'] as $name => $packageInfo) {
                // print_r($packageInfo);
                if ($packageInfo['dev_requirement'] === true) {
                    $this->logger->debug('Package {name} is a dev dependency', ['name' => $name]);
                    continue;
                }


                $type = ($packageInfo['type'] ?? 'lib');
                if ($type !== ModuleInterface::COMPOSER_TYPE) {
                    $this->logger->debug('Package {name} is a {type} not a {module}, skipping', [
                        'name' => $name,
                        'type' => $type,
                        'module' => ModuleInterface::COMPOSER_TYPE,
                    ]);
                    continue;
                }

                $this->logger->debug('Discovered module {name}', ['name' => $name]);
                $modules[] = $name;
            }
        }

        return $modules;
         */

        return InstalledVersions::getInstalledPackagesByType(ModuleInterface::COMPOSER_TYPE);
    }
}
