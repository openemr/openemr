<?php

/**
 * Wraps around the symfony console command runner and allows module writers to add commands that are
 * in the system.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Command;

use OpenEMR\Common\Command\Runner\AclModify;
use OpenEMR\Common\Command\Runner\CcdaImport;
use OpenEMR\Common\Command\Runner\CcdaNewpatient;
use OpenEMR\Common\Command\Runner\CcdaNewpatientImport;
use OpenEMR\Common\Command\Runner\IOpenEMRCommand;
use OpenEMR\Common\Command\Runner\Register;
use OpenEMR\Common\Command\Runner\ZfcModule;
use OpenEMR\Events\Command\CommandRunnerFilterEvent;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Finder\Finder;

class SymfonyCommandRunner
{
    private $eventDispatcher;

    public function __construct()
    {
    }
    public function setEventDispatcher(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
    public function getEventDispatcher(): EventDispatcher
    {
        if (!isset($this->eventDispatcher)) {
            $this->eventDispatcher = $GLOBALS['kernel']->getEventDispatcher();
        }
        return $this->eventDispatcher;
    }

    public function run()
    {
        $commands = $this->findCommands();
        $app = new Application();
        foreach ($commands as $command) {
            $app->add($command);
        }
        $app->run();
    }

    /**
     * @return Command[]
     */
    private function findCommands(): array
    {
        try {
            $finder = new Finder();
            $files = $finder->files()->in(__DIR__)->name("*.php");
            $filterCommand = new CommandRunnerFilterEvent();
            foreach ($files as $file) {
                $fileName = $file->getFilenameWithoutExtension();
                $fqn = __NAMESPACE__ . "\\" . $fileName;
                if (empty($fileName) || $fqn == self::class) {
                    continue; // skip over ourselves
                }
                if (class_exists($fqn)) {
                    $command = new $fqn();
                    if ($command instanceof Command) {
                        $filterCommand->setCommand($command::class, $command);
                    }
                }
            }
            // dispatch an event so modules can also add commands
            $this->getEventDispatcher()->dispatch($filterCommand, CommandRunnerFilterEvent::EVENT_NAME);
            return $filterCommand->getCommands();
        } catch (\Exception $ex) {
            echo "Error in attempting to find commands " . $ex->getMessage() . "\n";
            die();
        }
    }
}
