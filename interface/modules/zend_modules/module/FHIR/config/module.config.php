<?php

/**
 * Handles class, routing, views, and other configuration properties for the module.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\ZendModules\FHIR;

use Laminas\ServiceManager\Factory\InvokableFactory;
use OpenEMR\ZendModules\FHIR\Listener\UuidMappingEventsSubscriber;

return array(
    'service_manager' => array(
        'factories' => array(
            UuidMappingEventsSubscriber::class => InvokableFactory::class
        )
    )
);
