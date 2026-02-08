<?php

/*
 * IGlobalsAware.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Core\OEGlobalsBag;

interface IGlobalsAware
{
    /**
     * @param OEGlobalsBag $globalsBag The globals bag that will be used in the object.
     * @return void
     */
    function setGlobalsBag(OEGlobalsBag $globalsBag): void;

    /**
     * @return OEGlobalsBag|null Returns the globals bag if it has been set, null otherwise
     */
    function getGlobalsBag(): ?OEGlobalsBag;
}
