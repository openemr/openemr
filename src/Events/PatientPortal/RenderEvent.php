<?php

/**
 * RenderEvent is used to launch different rendering action points that developers can output their own HTML content
 * during the portal's SPA page lifecycle.
 * frame.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\PatientPortal;

use Symfony\Component\EventDispatcher\GenericEvent;

class RenderEvent extends GenericEvent
{
    /**
     * Allows screen output after all the sections have been rendered for the portal home Single Page Application (SPA)
     */
    const EVENT_SECTION_RENDER_POST = "home.section.render.post";

    /**
     * Allows screen output in the scripts section before any other scripts have been loaded
     */
    const EVENT_SCRIPTS_RENDER_PRE = 'home.scripts.render.pre';

    /**
     * Inject a new BS card in portal Dashboard.
     * Example card.
     * <div class="card d-flex mr-1 mb-1">
     *   <div class="card-body">
     *     <h4 class="card-title"><i class="fa fa-file- mr-1"></i>{{ 'Card Name' | xlt }}</h4>
     *     <a id="id it" class="btn btn-success" href="{{ web_root }} /...URL?pid={{ pid | attr_url }} ">{{ 'Name of anchor task' | xlt }}</a>
     *   </div>
     * </div>
     */
    const EVENT_DASHBOARD_INJECT_CARD = "home.dashboard.inject.card";

    /**
     * Inject supporting JavaScript if needed.
     */
    const EVENT_DASHBOARD_RENDER_SCRIPTS = "home.dashboard.render.scripts";
}
