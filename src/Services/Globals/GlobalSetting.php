<?php

/**
 * This file is part of OpenEMR.
 *
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Globals;

/**
 * Represents a global setting
 *
 * @package OpenEMR\Services
 * @subpackage Globals
 * @author Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2019 Ken Chapple <ken@mi-squared.com>
 */
class GlobalSetting
{
    // true false value represented as a boolean
    const DATA_TYPE_BOOL = "bool";
    // color picker setting
    const DATA_TYPE_COLOR_CODE = "color_code";
    // displays a password field box
    const DATA_TYPE_PASS = "pass";
    // used for encrypted field values
    const DATA_TYPE_ENCRYPTED = "encrypted";
    // used for encrypted hash field values
    const DATA_TYPE_ENCRYPTED_HASH = "encrypted_hash";
    // generates a random uuid if the value is an empty string
    const DATA_TYPE_DEFAULT_RANDOM_UUID = "if_empty_create_random_uuid";
    // 15 character maximum number string
    const DATA_TYPE_NUMBER = "num";

    const DATA_TYPE_CODE_TYPES = "all_code_types";

    // single select language
    const DATA_TYPE_LANGUAGE = "lang";

    // multiple select language selector
    const DATA_TYPE_MULTI_LANGUAGE_SELECT = "m_lang";

    // list of default visits in OpenEMR
    const DATA_TYPE_DEFAULT_VISIT_CATEGORY = "default_visit_category";
    // CSS Theme selector
    const DATA_TYPE_CSS = "css";
    // selector for types of theme.
    const DATA_TYPE_TABS_CSS = "tabs_css";
    // hour selector
    const DATA_TYPE_HOUR = "hour";
    // textbox
    const DATA_TYPE_TEXT = "text";

    protected $label = null;
    /**
     * @var string The field type that this value can be.  Valid options include 'bool', 'color_code',
     */
    protected $dataType = null;
    protected $default = null;
    protected $description = null;
    protected $isUserSetting = false;

    public function __construct($label, $dataType, $default, $description, $isUserSetting = false)
    {
        $this->label = $label;
        // TODO: do we want to validate the data type here?  Could slow down modules and anyone modifying globals...
        $this->dataType = $dataType;
        $this->default = $default;
        $this->description = $description;
        $this->isUserSetting = $isUserSetting;
    }

    public function format()
    {
        return [
            $this->label,
            $this->dataType,
            $this->default,
            $this->description
        ];
    }

    public function isUserSetting()
    {
        return $this->isUserSetting;
    }
}
