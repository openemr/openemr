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

namespace OpenEMR\Setting\Manager;

use OpenEMR\Services\Globals\GlobalSetting;

/**
 * Manager for HTML display section settings.
 *
 * @extends ScalarSettingManager<string>
 */
class HtmlDisplaySectionSettingManager extends ScalarSettingManager
{
    public function isDataTypeSupported(string $dataType): bool
    {
        return $dataType === GlobalSetting::DATA_TYPE_HTML_DISPLAY_SECTION;
    }
}
