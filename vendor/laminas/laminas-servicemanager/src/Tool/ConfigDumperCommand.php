<?php

declare(strict_types=1);

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ServiceManager\Tool;

use Laminas\ServiceManager\Exception;
use Laminas\Stdlib\ConsoleHelper;

use function array_shift;
use function class_exists;
use function dirname;
use function file_exists;
use function file_put_contents;
use function in_array;
use function is_array;
use function is_writable;
use function sprintf;

class ConfigDumperCommand
{
    const COMMAND_DUMP = 'dump';
    const COMMAND_ERROR = 'error';
    const COMMAND_HELP = 'help';

    const DEFAULT_SCRIPT_NAME = __CLASS__;

    const HELP_TEMPLATE = <<< EOH
<info>Usage:</info>

  %s [-h|--help|help] [-i|--ignore-unresolved] <configFile> <className>

<info>Arguments:</info>

  <info>-h|--help|help</info>          This usage message
  <info>-i|--ignore-unresolved</info>  Ignore classes with unresolved direct dependencies.
  <info><configFile></info>            Path to a config file for which to generate
                          configuration. If the file does not exist, it will
                          be created. If it does exist, it must return an
                          array, and the file will be updated with new
                          configuration.
  <info><className></info>             Name of the class to reflect and for which to
                          generate dependency configuration.

Reads the provided configuration file (creating it if it does not exist),
and injects it with ConfigAbstractFactory dependency configuration for
the provided class name, writing the changes back to the file.
EOH;

    /**
     * @var ConsoleHelper
     */
    private $helper;

    /**
     * @var string
     */
    private $scriptName;

    /**
     * @param string $scriptName
     */
    public function __construct($scriptName = self::DEFAULT_SCRIPT_NAME, ConsoleHelper $helper = null)
    {
        $this->scriptName = $scriptName;
        $this->helper = $helper ?: new ConsoleHelper();
    }

    /**
     * @param array $args Argument list, minus script name
     * @return int Exit status
     */
    public function __invoke(array $args)
    {
        $arguments = $this->parseArgs($args);

        switch ($arguments->command) {
            case self::COMMAND_HELP:
                $this->help();
                return 0;
            case self::COMMAND_ERROR:
                $this->helper->writeErrorMessage($arguments->message);
                $this->help(STDERR);
                return 1;
            case self::COMMAND_DUMP:
                // fall-through
            default:
                break;
        }

        $dumper = new ConfigDumper();
        try {
            $config = $dumper->createDependencyConfig(
                $arguments->config,
                $arguments->class,
                $arguments->ignoreUnresolved
            );
        } catch (Exception\InvalidArgumentException $e) {
            $this->helper->writeErrorMessage(sprintf(
                'Unable to create config for "%s": %s',
                $arguments->class,
                $e->getMessage()
            ));
            $this->help(STDERR);
            return 1;
        }

        file_put_contents($arguments->configFile, $dumper->dumpConfigFile($config));

        $this->helper->writeLine(sprintf(
            '<info>[DONE]</info> Changes written to %s',
            $arguments->configFile
        ));
        return 0;
    }

    /**
     * @param array $args
     * @return \stdClass
     */
    private function parseArgs(array $args)
    {
        if (! $args) {
            return $this->createHelpArgument();
        }

        $arg1 = array_shift($args);

        if (in_array($arg1, ['-h', '--help', 'help'], true)) {
            return $this->createHelpArgument();
        }

        $ignoreUnresolved = false;
        if (in_array($arg1, ['-i', '--ignore-unresolved'], true)) {
            $ignoreUnresolved = true;
            $arg1 = array_shift($args);
        }

        if (! $args) {
            return $this->createErrorArgument('Missing class name');
        }

        $configFile = $arg1;
        switch (file_exists($configFile)) {
            case true:
                $config = require $configFile;

                if (! is_array($config)) {
                    return $this->createErrorArgument(sprintf(
                        'Configuration at path "%s" does not return an array.',
                        $configFile
                    ));
                }

                break;
            case false:
                // fall-through
            default:
                if (! is_writable(dirname($configFile))) {
                    return $this->createErrorArgument(sprintf(
                        'Cannot create configuration at path "%s"; not writable.',
                        $configFile
                    ));
                }

                $config = [];
                break;
        }

        $class = array_shift($args);

        if (! class_exists($class)) {
            return $this->createErrorArgument(sprintf(
                'Class "%s" does not exist or could not be autoloaded.',
                $class
            ));
        }

        return $this->createArguments(self::COMMAND_DUMP, $configFile, $config, $class, $ignoreUnresolved);
    }

    /**
     * @param resource $resource Defaults to STDOUT
     * @return void
     */
    private function help($resource = STDOUT)
    {
        $this->helper->writeLine(sprintf(
            self::HELP_TEMPLATE,
            $this->scriptName
        ), true, $resource);
    }

    /**
     * @param string $command
     * @param string $configFile File from which config originates, and to
     *     which it will be written.
     * @param array $config Parsed configuration.
     * @param string $class Name of class to reflect.
     * @param bool $ignoreUnresolved If to ignore classes with unresolved direct dependencies.
     * @return \stdClass
     */
    private function createArguments($command, $configFile, $config, $class, $ignoreUnresolved)
    {
        return (object) [
            'command'          => $command,
            'configFile'       => $configFile,
            'config'           => $config,
            'class'            => $class,
            'ignoreUnresolved' => $ignoreUnresolved,
        ];
    }

    /**
     * @param string $message
     * @return \stdClass
     */
    private function createErrorArgument($message)
    {
        return (object) [
            'command' => self::COMMAND_ERROR,
            'message' => $message,
        ];
    }

    /**
     * @return \stdClass
     */
    private function createHelpArgument()
    {
        return (object) [
            'command' => self::COMMAND_HELP,
        ];
    }
}
