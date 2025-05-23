<?php

/**
 * Patient Summary Card Interface
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2022 Robert Down <robertdown@live.com
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Patient\Summary\Card;

/**
 * Define the required components of a Patient Summary Card to be used by modules
 */
interface CardInterface
{
    /**
     * Return the class name used to manage the background color of the entire card
     *
     * @return string
     */
    public function getBackgroundColorClass(): string;

    /**
     * Return the class name used to manage the text-color of the entire card
     *
     * @return string
     */
    public function getTextColorClass(): string;

    /**
     * Return the title of the card
     *
     * @return string
     */
    public function getTitle(): string;

    /**
     * Return the unique identifier of the card
     *
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * Indexed array of Category and Subcategory to check access
     *
     * $array[0] = Category
     * $array[1] = Subcategory
     *
     * @return array
     */
    public function getAcl(): array;

    /**
     * Return a boolean indicating whether the card is initially collapsed
     *
     * Implementing class should consider the user defined preference when setting this value.
     *
     * @return boolean
     */
    public function isInitiallyCollapsed(): bool;

    /**
     * Return a boolean indicating if an "add" button should be rendered in the card header
     *
     * @return boolean
     */
    public function canAdd(): bool;

    /**
     * Return a boolean indicating if an "edit" button should be rendered in the card header
     *
     * @return boolean
     */
    public function canEdit(): bool;

    /**
     * Return a boolean indicating if the card can be collapsed
     *
     * This should almost always return true as it removed the ability of the
     * user to set their own preference.
     *
     * @return boolean
     */
    public function canCollapse(): bool;

    /**
     * Return the filename of the template to render
     *
     * @return string
     */
    public function getTemplateFile(): string;

    /**
     * Return the array of variables to be rendered in the template
     *
     * Should return an empty array if no variables are needed.
     *
     * @return array
     */
    public function getTemplateVariables(): array;
}
