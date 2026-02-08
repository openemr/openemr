<?php

/*
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Kofi Appiah <kkappiah@medsov.com>
 * @copyright Copyright (c) 2024 omegasystemsgroup <info@omegasystemsgroup.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

namespace OpenEMR\Events\PatientDemographics;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event object for know what type of appointment has been set on the calendar
 *
 * @package OpenEMR\Events
 * @subpackage PatientDemographics
 *
 */
class RenderPharmacySectionEvent extends Event
{
    /**
     * This event is triggered in javascript when rendering demographics
     */
    const RENDER_JAVASCRIPT = 'patientDemographics.render.javascript';

    /**
     * This event is triggered after rendering the default openemr pharmacy selector
     */
    const RENDER_AFTER_PHARMACY_SECTION = 'patientDemographics.render.section.after.pharmacy';

    /**
     * This event is triggered after rendering the selected openemr pharmacy
     * in the demographics section
     */
    const RENDER_AFTER_SELECTED_PHARMACY_SECTION = 'patientDemographics.render.after.selected.pharmacy';

    public function __construct()
    {}
}
