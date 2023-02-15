<?php

/**
 * The MenuItemInterface, ensuring provided menu items can successfully be rendered
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2023 Providence Healthtech
 * @copyright Copyright (c) 2023 Robert Down <robertdown@live.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Menu;

interface MenuItemInterface
{
    /**
     * Return the text rendered to the user
     *
     * @return string
     */
    public function getDisplayText(): string;

    /**
     * Return the ID attribute of the menu item, should be unique in a given Menu
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Return the target tab to open the link in
     *
     * @return string
     */
    public function getTarget(): string;

    /**
     * Return the URL of the link
     *
     * @return string
     */
    public function getUrl(): string;

    /**
     * Return a MenuItems object for any children menu items
     *
     * @return MenuItems
     */
    public function getChildren(): MenuItems;

    /**
     * Return the requirement integer
     *
     * @return integer
     */
    public function getRequirements(): int;

    /**
     * Return either an 2 element array with the ACL or an array of arrays for multiple solutions
     *
     * Example:
     * Only 1 ACL requirement:
     * ```
     * ["admin", "drugs"]
     * ```
     *
     * Two or more ACL requirements:
     * ```
     * [
     *     ["admin", "drugs"],
     *     ["inventory", "lots"],
     *     ["inventory", "reporting"]
     * ]
     * ```
     *
     */
    public function getAcl(): array;

    /**
     * Return global requirements
     *
     * Example: ["!disable_calendar", "!ippf_sepcific"]
     * @return array
     */
    public function getGlobalReqStrict(): array;

    /**
     * Return a global requirements array or string
     *
     * @return array|string
     */
    public function getGlobalReq(): array|string;

    /**
     * Return any content to render before Display Text.
     *
     * @return string
     */
    public function getPreTextContent(): string|bool;

    /**
     * Return any content to render after the Display Text
     *
     * @return string
     */
    public function getPostTextContent(): string|bool;

    /**
     * Return an array of class names for the element.
     *
     * The required classes to render the menu will be injected automatically,
     * meaning there is no need to add "nav-link" to every entry. This function
     * should be used in special circumstances when extra classes need to be
     * injected, such as a custom bg color. This function will apply to the
     * actual link element, either an `<a>` or `<button>` element. Use
     * getLinkContainerClassList() to modify the surrounding `<div>` or `<li>`
     * elements.
     *
     * @return array
     */
    public function getLinkClassList(): array;

    /**
     * Return an array of class names for the link element's container.
     *
     * The required classes to render the menu will be injected automatically,
     * meaning there is no need to add "nav-item" to every entry. This function
     * should be used in special circumstances when extra classes need to be
     * injected, such as a custom bg color. This function will apply to the
     * link container element, either an `<div>` or `<li>` element. Use
     * getLinkClassList() to modify the actual link `<a>` or `<button>` elements.
     *
     * @return array
     */
    public function getLinkContainerClassList(): array;

    /**
     * Return an associative array of items to render as attribute elements of the link.
     *
     * Example: ["data-custom" => "my_value"] will render <a ... data-custom="my_value">
     * Note - any elements defined here that are explicitley defined elsewhere
     * (such as ID or URL) will not be rendered, use the more specific functions
     * for those elements.
     *
     * @return array
     */
    public function getAttributes(): array;
}
