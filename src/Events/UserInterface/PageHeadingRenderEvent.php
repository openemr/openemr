<?php

/**
 * PageHeadingRenderEvent class is fired from the OemrUI class prior to rendering the page-level action buttons, allowing
 * event listeners to render action buttons into the list with limited UI flexability.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2023 Providence Healthtech
 * @copyright Copyright (c) 2023 Robert Down <robertdown@live.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\UserInterface;

class PageHeadingRenderEvent
{
    const EVENT_PAGE_HEADING_RENDER = 'oemrui.page.header.render';

    /**
     * The PageID being rendered
     *
     * @var string
     */
    private string $page_id;

    private array $actions;

    /**
     * UserEditRenderEvent constructor.
     * @param string $pageName
     * @param int|null $userId The userid that is being edited, null if this is a brand new user
     * @param array $context
     */
    public function __construct(string $page_id)
    {
        $this->page_id = $page_id;
        $this->actions = [];
    }

    /**
     * Return the page ID being rendered
     *
     * @return string
     */
    public function getPageId(): string
    {
        return $this->page_id;
    }

    /**
     * @return array|null
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * @param array
     * @return UserEditRenderEvent
     */
    public function setActions(array $actions): PageHeadingRenderEvent
    {
        foreach ($actions as $action) {
            if (!($action instanceof ActionButtonInterface)) {
                throw new \Exception("{$action} must implement ActionButtonInterface");
            }
        }

        $this->actions = $actions;
        return $this;
    }
}
