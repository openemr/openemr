<?php

/**
 * Used for adding / modifying / removing commands that are used in the core OpenEMR command runner
 * IE php bin/console.  @see https://symfony.com/doc/current/console.html for documentation on how
 * the command runner is run.  As a convention module writers should prefix their commands with a namespace name
 * and NOT use openemr:<command-name> to clearly differentiate a module's commands from the core command API.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Command;

use Symfony\Component\Console\Command\Command;

class CommandRunnerFilterEvent
{
    const EVENT_NAME = "openemr.command-runner.filter";
    private $commands = [];

    public function __construct()
    {
        $this->commands = [];
    }

    public function getCommands(): array
    {
        return $this->commands; // creates a copy of the arrays
    }

    public function setCommand($fqdn, Command $command)
    {
        $this->commands[$fqdn] = $command;
    }
    public function hasCommand($fqdn): bool
    {
        return isset($this->commands[$fqdn]);
    }

    public function removeCommand($command)
    {
        $commandFQDN = $command;
        if ($command instanceof Command) {
            $commandFQDN = $command::class;
        }

        if ($this->hasCommand($commandFQDN)) {
            unset($this->commands[$commandFQDN]);
        } else {
            throw new \InvalidArgumentException("Passed in argument is not in list of commands");
        }
    }
}
