<?php

/**
 * Facilitates adding a debug flag and a cli input/output styler useful for debugging commands.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author   Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2024 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Command\Trait;

use Symfony\Component\Console\Style\SymfonyStyle;

trait CommandLineDebugStylerTrait
{
    /**
     * @var SymfonyStyle CLI styler used for debug mode to help in identifying issues during the import process.
     */
    protected SymfonyStyle $styler;

    /**
     * @var bool Whether to add additional logging / debug output to the system
     */
    protected bool $cliDebug = false;

    public function setCommandLineStyler(SymfonyStyle $symfonyStyler)
    {
        $this->cliDebug = true;
        $this->styler = $symfonyStyler;
    }

    public function isCliDebug()
    {
        return $this->cliDebug;
    }

    public function getCommandLineStyler(): SymfonyStyle
    {
        return $this->styler;
    }
}
