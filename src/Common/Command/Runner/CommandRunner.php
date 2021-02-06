<?php

/**
 * CommandRunner finds and executes OpenEMR cli commands that are in the OpenEMR Command namespace.  It will search
 * inside directory of the COMMAND_NAMESPACE for any classes that implement the IOpenEMRCommand interface.  This provides
 * OpenEMR developers and administrators the ability to run cli commands to execute commands and administer their OpenEMR
 * installation.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Command\Runner;

use MongoDB\Driver\Command;
use OpenEMR\Common\Command\IOpenEMRCommand;

class CommandRunner
{
    /**
     * The OpenEMR root installation folder
     * @var string
     */
    private $rootPath;

    /**
     * The name of the script that is calling the command runner
     * @var string
     */
    private $scriptName;

    const COMMAND_NAMESPACE = 'OpenEMR\\Common\\Command\\';
    const COMMAND_SUFFIX = "Command";

    public function __construct($rootPath, $scriptName)
    {
        $this->rootPath = $rootPath;
        $this->scriptName = $scriptName;
    }

    public function run()
    {
        $shortOpts = "c:hl";
        $options = getopt($shortOpts);

        $context = new CommandContext();
        $context->setRootPath($this->rootPath);
        $context->setScriptName($this->scriptName);

        if (
            isset($options['l'])
            || (isset($options['h']) && !isset($options['c']))
        ) {
            $this->listCommands($context);
            return;
        }

        if (!isset($options['c'])) {
            echo "Missing argument expected usage: " . $context->getScriptName() . " -c command\n";
            echo "For help run: " . $context->getScriptName() . " -h\n";
            die();
        }
        $commandName = $options['c'] !== false ? $options['c'] : $options['command'];
        $classPath = self::COMMAND_NAMESPACE . $commandName . self::COMMAND_SUFFIX;
        if (!class_exists($classPath)) {
            echo 'command could not be found at ' . $classPath . "\n";
            die();
        }

        $command = new $classPath();
        if (!$command instanceof IOpenEMRCommand) {
            echo 'Command does not implement the IOpenEMRCommand interface';
            die();
        }
        if (isset($options['h'])) {
            $command->printUsage($context);
            exit;
        }

        echo "Executing command '" . $commandName . "'\n";
        $command->execute($context);
        exit;
    }

    private function listCommands(CommandContext $context)
    {
        $commands = $this->findCommands();
        echo "Commands List\n";
        echo "Command Usage: " . $context->getScriptName() . " -c <Command> <CommandArguments>\n";
        echo "To get help with a command pass the -h flag after the command name\n\n";

        echo "Command Name - Description\n";
        foreach ($commands as $commandName => $command) {
            echo $commandName . " - " . $command->getDescription($context) . "\n";
        }
        echo "\n";
    }

    /**
     * @return IOpenEMRCommand[]
     * @throws \ReflectionException
     */
    private function findCommands(): array
    {
        $availableCommands = [];

        try {
            $interface = new \ReflectionClass(IOpenEMRCommand::class);
            $commandPath = dirname($path = $interface->getFileName());
            $files = scandir($commandPath);
            foreach ($files as $file) {
                $fileName = pathinfo($file, PATHINFO_FILENAME);
                // skip this
                if ($fileName === $interface->getName()) {
                    continue;
                }

                $fqn = self::COMMAND_NAMESPACE . $fileName;
                if (class_exists($fqn)) {
                    $command = new $fqn();
                    if ($command instanceof IOpenEMRCommand) {
                        $suffixPos = strrpos($fileName, self::COMMAND_SUFFIX);

                        if ($suffixPos !== false) {
                            $fileName = substr($fileName, 0, $suffixPos);
                        }
                        $availableCommands[$fileName] = $command;
                    }
                }
            }
            return $availableCommands;
        } catch (\Exception $ex) {
            echo "Error in attempting to find commands " . $ex->getMessage() . "\n";
            die();
        }
    }
}
