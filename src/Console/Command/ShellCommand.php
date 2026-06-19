<?php

/**
 * Wrap PsySH in a console command with some pre-configured variables.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <eric@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 * @see       https://physh.org
 */

declare(strict_types=1);

namespace OpenEMR\Console\Command;

use Doctrine\ORM\EntityManagerInterface;
use Psy\Shell;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

#[AsCommand(
    name: 'shell',
    description: 'Run a REPL in the bootstrapped application',
    aliases: ['repl'],
)]
class ShellCommand extends Command
{
    public function __construct(
        readonly private EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    public function __invoke(): int
    {
        $shell = new Shell();
        $shell->setScopeVariables([
            'em' => $this->em,
        ]);

        return $shell->run();
    }
}
