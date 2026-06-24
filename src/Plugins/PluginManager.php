<?php

declare(strict_types=1);

namespace OpenEMR\Plugins;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;

class PluginManager
{
    public function __construct(
        private Activation\StateProvider $activationState,
    ) {
    }

    /**
     * @template T of PluginInterface
     * @param class-string<T> $filter
     * @return iterable<class-string<T>>
     */
    private function getActivePlugins(string $filter = PluginInterface::class): iterable
    {
        // TODO: cache/memoize this data?
        $pluginData = \OpenEMR\PluginInstaller\GeneratedInstalledPlugins::PLUGIN_DATA;
        foreach ($pluginData as $package => $data) {
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
        foreach ($this->getActivePlugins(CliPluginInterface::class) as $bootstrap) {
            $commands = $bootstrap::getCommandClasses();
            foreach ($commands as $command) {
                $cli->addCommand($container->get($command));
            }
        }
    }
}
