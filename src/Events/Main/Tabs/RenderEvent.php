<?php

/**
 * RenderEvent is used to launch different rendering action points that developers can output their own HTML content
 * during the main tabs page.  It allows HTML content to be rendered that will stick around through every sub tab content
 * frame.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Main\Tabs;

use Symfony\Contracts\EventDispatcher\Event;

class RenderEvent extends Event
{
    const EVENT_BODY_RENDER_PRE = "main.body.render.pre";
    const EVENT_BODY_RENDER_POST = "main.body.render.post";
}
