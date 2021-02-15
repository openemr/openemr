<?php

/**
 * IOpenEMRCommand is the interface that all OpenEMR cli commands must implement in order to be executed by the OpenEMR
 * command line runner.  Commands that implement this interface MUST have the 'Command' suffix.
 *
 * Commands are executed by the following command-runner call
 * 'command-runner -c CLASS_NAME <additional arguments>'
 *
 * @see /bin/command-runner
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Command;

use OpenEMR\Common\Command\Runner\CommandContext;

interface IOpenEMRCommand
{
    /**
     * Prints the instructions on how to use this command
     * @param CommandContext $context All the context about the command environment.
     */
    public function printUsage(CommandContext $context);

    /**
     * Returns a description of the command
     * @return string
     */
    public function getDescription(CommandContext $context): string;

    /**
     * Execute the command and spit any output to STDOUT and errors to STDERR
     * @param CommandContext $context All the context information needed for the CLI Command to execute
     */
    public function execute(CommandContext $context);
}
