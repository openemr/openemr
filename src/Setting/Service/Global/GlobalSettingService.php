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

use OpenEMR\Setting\Service\AbstractSettingService;

class GlobalSettingService extends AbstractSettingService
{
    protected function getSettingKeysBySectionName(string $sectionName): array
    {
        return array_keys(
            $this->globalsService->getMetadataBySectionName($sectionName),
        );
    }
}
