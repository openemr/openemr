<?php

/**
 * RecallBoardToolbarEvent
 *
 * Dispatched when the recall board toolbar is rendered. Modules may listen
 * to this event to inject additional HTML (e.g. custom action buttons) into
 * the recall board header row without resorting to filesystem scanning.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2026 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Recall;

use Symfony\Contracts\EventDispatcher\Event;

class RecallBoardToolbarEvent extends Event
{
    const EVENT_NAME = 'recall.board.toolbar';

    /** @var string HTML contributed by listeners */
    private string $html = '';

    /**
     * Append HTML to the toolbar.
     *
     * @param string $html Trusted HTML string produced by the module
     */
    public function appendHtml(string $html): void
    {
        $this->html .= $html;
    }

    /**
     * Returns all HTML contributed by listeners.
     */
    public function getHtml(): string
    {
        return $this->html;
    }
}
