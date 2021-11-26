<?php

/**
 * RenderEvent is used to launch different rendering action points that developers can output their own HTML content
 * during the portal's SPA page lifecycle.
 * frame.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\PatientPortal;

use Symfony\Component\EventDispatcher\GenericEvent;

class RenderEvent extends GenericEvent
{
    /**
     * Allows screen output after all of the sections have been rendered for the portal home Single Page Application (SPA)
     */
    const EVENT_SECTION_RENDER_POST = "home.section.render.post";

    /**
     * Allows screen output in the scripts section before any other scripts have been loaded
     */
    const EVENT_SCRIPTS_RENDER_PRE = 'home.scripts.render.pre';
}
