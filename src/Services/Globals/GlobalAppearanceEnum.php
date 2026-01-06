<?php

/*
 * GlobalAppearanceEnum.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Globals;

enum GlobalAppearanceEnum: string
{
    case SIMPLIFIED_PRESCRIPTIONS = 'simplified_prescriptions';

    // TODO: move the rest of the Connectors settings from globals.inc.php into this file

    // TODO: add methods for handling things like the descriptions and supported data types if needed
}
