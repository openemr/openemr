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

use OpenEMR\Menu\MenuItems;
use OpenEMR\Menu\MenuItemInterface;

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
     * The MenuItems container for the Primary Menu section
     *
     * @var MenuItems
     */
    private MenuItems $primary_menu;

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
        $this->primary_menu = new MenuItems();
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

    public function getPrimaryMenu(): MenuItems
    {
        return $this->primary_menu;
    }

    /**
     * Add a new MenuItem to the Primary menu
     *
     * @param MenuItemInterface $item The item to add
     * @param integer|null $position The position to push the item too. Optional, default behavior appends. Must be position
     * @return PageHeadingRenderEvent
     */
    public function setPrimaryMenuItem(MenuItemInterface $item, ?int $position = null): PageHeadingRenderEvent
    {
        if (!$position || ($this->primary_menu->offsetExists($position) == false)) {
            // No $position, or $position is not a valid position in array, just append
            $this->primary_menu->append($item);
        } else {
            $_tmp = $this->primary_menu->getArrayCopy();

            // Get all the elements before the requested position of new element
            $_prev = array_slice($_tmp, 0, $position);

            // Get all elements starting at new position to the end of array
            $_post = array_slice($_tmp, $position, (count($_tmp) - $position));

            // Append the requested element, merge the partials together, update the array
            $_prev[] = $item;
            $_new = array_merge($_prev, $_post);
            $this->primary_menu->exchangeArray($_new);
        }

        return $this;
    }

    public function getPrimaryMenuItems(): MenuItems
    {
        return $this->primary_menu;
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
