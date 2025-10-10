<?php

/*
 * GlobalInterfaceCommandTrait.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Command\Trait;

use OpenEMR\Common\Command\IGlobalsAwareCommand;
use OpenEMR\Core\OEGlobalsBag;

trait GlobalInterfaceCommandTrait
{
    private OEGlobalsBag $globalsBag;

    public function setGlobalsBag(OEGlobalsBag $globalsBag): void
    {
        $this->globalsBag = $globalsBag;
    }

    public function getGlobalsBag(): OEGlobalsBag
    {
        return $this->globalsBag;
    }
}
