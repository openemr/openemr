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
     * Return an array of key/value pairs where key is the name of the attribute and value is the value of the attribute.
     * Will be escaped at render time.
     *
     * An important note about injecting your own attributes, the value of the attribute is escaped using attr()
     * which will cause problems if you are attempting to use javascript in the value of your attribute
     * (i.e. an onclick attribute). If you need javascript, see getClickHandlerFunctionName() and getClickHandlerTemplateName().
     *
     * @return array
     */
    public function getAttributes();

    /**
     * Get the name of the function handling click events.
     *
     * This is the name of the function that is attached to the click event of the button. Do not include `()` in the name.
     *
     * @return string|null
     */
    public function getClickHandlerFunctionName();

    /**
     * Get the path of the file containing the click handler function.
     *
     * This is the path to a twig file which should contain only the function referenced in getClickHandlerFunctionName()
     * that processes whatever action is required when the user clicks the button. do not include `<script>` tags in the
     * template.
     *
     * @return void
     */
    public function getClickHandlerTemplateName();

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
