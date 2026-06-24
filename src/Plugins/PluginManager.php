<?php

declare(strict_types=1);

namespace OpenEMR\Plugins;

use Firehed\Container\AutoDetect;
use OpenEMR\Plugin\CliPluginInterface;
use OpenEMR\PluginInstaller\GeneratedInstalledPlugins;
use OpenEMR\PluginInstaller\Plugin;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;

class PluginManager
{
    public static function fromConfig(): self
    {
        // This might also need to be a singleton?
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
}
