<?php

declare(strict_types=1);

namespace OpenEMR\Modules;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;

class ModuleManager
{
    public function __construct(
        private Activation\StateProvider $activationState,
    ) {
    }

    /**
     * @template T of ModuleInterface
     * @param class-string<T> $filter
     * @return iterable<class-string<T>>
     */
    private function getActiveModules(string $filter = ModuleInterface::class): iterable
    {
        // TODO: cache/memoize this data?
        $moduleData = \OpenEMR\PluginInstaller\GeneratedInstalledPlugins::PLUGIN_DATA;
        foreach ($moduleData as $package => $data) {
            if (!$this->activationState->isActive($package)) {
                continue;
            }
            foreach ($data['bootstraps'] as $bootstrap) {
                if (is_a($bootstrap, $filter, allow_string: true)) {
                    yield $bootstrap;
                }
            }
        }
    }

    public function addCommands(Application $cli, ContainerInterface $container): void
    {
        // TODO: Figure out how to make these lazy-loaded
        foreach ($this->getActiveModules(CliModuleInterface::class) as $bootstrap) {
            $commands = $bootstrap::getCommandClasses();
            foreach ($commands as $command) {
                $cli->addCommand($container->get($command));
            }
        }
    }
}
