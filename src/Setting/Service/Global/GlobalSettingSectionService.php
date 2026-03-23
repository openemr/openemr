<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Setting\Service\Global;

use OpenEMR\Setting\Service\AbstractSettingSectionService;

class GlobalSettingSectionService extends AbstractSettingSectionService
{
    /**
     * Sorts Section names in ascending order before returning.
     */
    public function getSectionNames(): array
    {
        $sectionNames = $this->globalsService->getAllSections();

        sort($sectionNames);

        return $sectionNames;
    }
}
