<?php

/**
 * A helper class for simple interaction with the PageHeadingRenderEvent in an interface-compliant manner
 *
 * This is usefull for the most basic of requirements. If your code requires any type of business logic, it should be
 * handled with its own class, returnin a ActionButtonInterface-compliant class. This class sets up the basics to get
 * rapidly inject a button into the page heading but does not handle ACL or advanced logic.
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

class BaseActionButtonHelper implements ActionButtonInterface
{
    private $id = "";
    private $title = "";
    private $displayText = "";
    private $iconClass = "";
    private $attributes = [];
    private $clickHandlerTemplateName;
    private $clickHandlerFunctionName;
    private $anchorClasses = [];
    private $href = "#";

    /**
     * Populate the critical parts of an action button.
     *
     * Valid keys are:
     * * id
     * * title
     * * displayText
     * * iconClass
     * * attributes
     * * anchorClasses
     * * jsTemplatePath
     * * href
     *
     * The attributes key requires an array value with key/value equating to the name of the attribute and the value of
     * the attribute. It is designed to capture data attributes, but could be used to put any attribute on the element.
     * If attributes contain the following keys, they are dropped prior to rendering: id, title, class, href
     *
     * If any allowed keys are not set they will be set to an empty string.
     *
     * @param [type] $opts
     */
    public function __construct($opts)
    {
        $allowedKeys = [
            'id',
            'title',
            'displayText',
            'iconClass',
            'attributes',
            'anchorClasses',
            'clickHandlerTemplateName',
            'clickHandlerFunctionName',
            'href',
        ];

        foreach ($opts as $k => $v) {
            if (!in_array($k, $allowedKeys)) {
                continue;
            }

            $this->$k = $v;
        }
    }

    public function getID(): string
    {
        return $this->id;
    }

    public function getAnchorClasses()
    {
        return $this->anchorClasses;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDisplayText(): string
    {
        return $this->displayText;
    }

    public function getIconClass(): string
    {
        return $this->iconClass;
    }

    /**
     * Convert the key/value array of attributes into a single, quoted string
     *
     * Better to manage the data as an array until the last minute
     *
     * @return string|null
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getClickHandlerTemplateName(): string|null
    {
        return $this->clickHandlerTemplateName;
    }

    public function getClickHandlerFunctionName(): string|null
    {
        return $this->clickHandlerFunctionName;
    }

    public function getHref()
    {
        return $this->href;
    }
}
