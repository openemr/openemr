<?php

/**
 * ActionButtonInterface defines the requirements to display an action button on pages that support OemrUI pageHeading().
 *
 * @package OpenEMR
 * @subpackage Events
 * @author Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2023 Robert Down
 * @copyright Copyright (c) 2023 Providence Healthtech
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\UserInterface;

interface ActionButtonInterface
{

    /**
     * Get the ID of the element, used to populate the ID attribute of the anchor element
     *
     * @return string
     */
    public function getID();

    /**
     * Populate the title attribute of the element
     *
     * @return string
     */
    public function getTitle();

    /**
     * Populate the text dosplayed to the user
     *
     * @return string
     */
    public function getDisplayText();

    /**
     * Get the class to render the icon, generally a font awesome class
     *
     * @return string
     */
    public function getIconClass();

    /**
     * Get any data-attributes required.
     *
     * Should return a string in the form of 'name="value"'
     *
     * @return array
     */
    public function getAttributes();

    /**
     * Get the path of the template to be loaded to manage javascript code interacting with the button
     *
     * @return string|null
     */
    public function getJavascriptTemplatePath();

    /**
     * Return a simple array of class names that will be added to the anchor's class attribute
     *
     * @return string|null
     */
    public function getAnchorClasses();

    /**
     * Get the href attribute
     *
     * @return void
     */
    public function getHref();

}
