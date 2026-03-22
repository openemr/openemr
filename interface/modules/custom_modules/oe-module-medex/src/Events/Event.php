<?php

/**
 * Base Event class - Simple event class without Symfony dependency
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2026 MedEx
 * @license   Proprietary - All Rights Reserved
 */

namespace OpenEMR\Modules\MedEx\Events;

/**
 * Simple Event base class without Symfony dependencies
 */
if (!class_exists('OpenEMR\\Modules\\MedEx\\Events\\Event')) {
    class Event
    {
        // Empty base class for event inheritance
    }
}
