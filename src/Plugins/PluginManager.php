<?php

declare(strict_types=1);

namespace OpenEMR\Plugins;

use Firehed\Container\AutoDetect;
use OpenEMR\Plugin\CliPluginInterface;
use OpenEMR\Plugin\DatabasePluginInterface;
use OpenEMR\PluginInstaller\GeneratedInstalledPlugins;
use OpenEMR\PluginInstaller\Plugin;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;

class PluginManager
{
    public static function fromConfig(): self
    {
        /**
         * This is a little unusual:
         *
         * Plugin management needs to happen _really_ early in the application
         * lifecycle. Even before the main DI container is built, since plugins
         * influence the contents of the container. Rather than trying to push
         * more into an existing container after it's built, (fights the API,
         * would have negative performance consequences), the manager instead
         * gets its own little mini-container.
         *
         * Strictly speaking, this could be done inline just fine, but the env
         * switching for the `Activation\StateProvider` would be silly to
         * reinvent.
         *
         * Note: the environment must be configured properly prior to this
         * running. In dev envs, this means dotenv should have run already.
         */

        // This might also need to be a singleton? The logic to determine
        $builder = AutoDetect::getBuilder(compiledOutputPath: 'vendor/compiledModuleContainer.php');
        $builder->addDirectory(__DIR__ . '/config');
        $container = $builder->build();
        return $container->get(self::class);
    }

    public function __construct(
        private Activation\StateProvider $activationState,
    ) {
    }

    /**
     * @return iterable<Plugin>
     */
    private function getActivePlugins(): iterable
    {
        // TODO: cache/memoize this data?
        foreach (GeneratedInstalledPlugins::getPlugins() as $plugin) {
            if ($this->activationState->isActive($plugin->name)) {
                yield $plugin;
            }
        }
    }

    /**
     * @return list<string>
     */
    public function getConfigFiles(): array
    {
        $configFiles = [];
        foreach ($this->getActivePlugins() as $plugin) {
            foreach ($plugin->bootstrapClasses as $bootstrap) {
                foreach ($bootstrap::getConfigFiles() as $relativePath) {
                    $configFiles[] = $plugin->relativeInstallDirectory . '/' . $relativePath;
                }
            }
        }
        return $configFiles;
    }

    public function addCommands(Application $cli, ContainerInterface $container): void
    {
        // TODO: Figure out how to make these lazy-loaded
        foreach ($this->getActivePlugins() as $plugin) {
            foreach ($plugin->bootstrapClasses as $bootstrap) {
                if (!is_a($bootstrap, CliPluginInterface::class, allow_string: true)) {
                    continue;
                }
                foreach ($bootstrap::getCommandClasses() as $command) {
                    $cli->addCommand($container->get($command));
                }
            }
        }
    }

    public function getMigrationsPaths(): array
    {
        $out = [];
        foreach ($this->getActivePlugins() as $plugin) {
            foreach ($plugin->bootstrapClasses as $bootstrap) {
                if (!is_a($bootstrap, DatabasePluginInterface::class, allow_string: true)) {
                    continue;
                }
                foreach ($bootstrap::getMigrationsPaths() as $ns => $dir) {
                    $out[$ns] = $plugin->relativeInstallDirectory . '/' . $dir;
                };
            }
        }
        return $out;
    }
}
