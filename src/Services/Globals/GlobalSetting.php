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

    // multiple select dashboard cards
    const DATA_TYPE_MULTI_DASHBOARD_CARDS = "m_dashboard_cards";

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

    // html display section
    const DATA_TYPE_HTML_DISPLAY_SECTION = "html_display_section";

    /**
     * Multiple list box with a dropdown selector to add list items.  Items can be re-arranged in order.  Selected
     * list items save the options property of the list into the globals setting.  Multiple values are separated by a
     * semi-colon (;).  Pass in a field option of 'list_id' => '<list-name-goes-here>' to the setting to choose the list
     */
    const DATA_TYPE_MULTI_SORTED_LIST_SELECTOR = "multi_sorted_list_selector";

    /**
     * Add to this list if the field supports options
     */
    const DATA_TYPES_WITH_OPTIONS = [self::DATA_TYPE_MULTI_SORTED_LIST_SELECTOR, self::DATA_TYPE_HTML_DISPLAY_SECTION];

    /**
     * Mappings of the data types and the options they support
     */
    const DATA_TYPE_FIELD_OPTIONS_SUPPORTED = [
        self::DATA_TYPE_MULTI_SORTED_LIST_SELECTOR => [
            self::DATA_TYPE_OPTION_LIST_ID
        ]
        ,self::DATA_TYPE_HTML_DISPLAY_SECTION => [
            self::DATA_TYPE_OPTION_RENDER_CALLBACK
        ]
    ];

    const DATA_TYPE_OPTION_LIST_ID = 'list_id';

    const DATA_TYPE_OPTION_RENDER_CALLBACK = 'render_callback';

    const DATA_TYPE_ADDRESS_BOOK = 'address_book';

    protected $label = null;
    /**
     * @var string The field type that this value can be.  Valid options include 'bool', 'color_code',
     */
    protected $dataType = null;
    protected $default = null;
    protected $description = null;
    protected $isUserSetting = false;

    /**
     * @var array Any specific field options such as
     */
    protected $fieldOptions = [];

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
        $result = [
            $this->label,
            $this->dataType,
            $this->default,
            $this->description,
        ];
        if (!empty($this->fieldOptions)) {
            $result[] = $this->fieldOptions;
        }
        return $result;
    }

    public function isUserSetting()
    {
        return $this->isUserSetting;
    }

    /**
     * @return array
     */
    public function getFieldOptions(): array
    {
        return $this->fieldOptions;
    }

    public function addFieldOption($key, $option)
    {
        // here we can do any validation that we want.  For now we only support list_id with
        // the DATA_TYPE_MULTI_SORTED_LIST_SELECTOR
        if (!$this->dataTypeSupportsOptions($this->dataType)) {
            throw new \InvalidArgumentException("Data type does not support field options");
        }
        if (!$this->dataTypeSupportsOptionKey($this->dataType, $key)) {
            throw new \InvalidArgumentException("Data type does not support field option key " . $key);
        }
        $this->fieldOptions[$key] = $option;
    }

    public function dataTypeSupportsOptions($datatype)
    {
        return in_array($datatype, self::DATA_TYPES_WITH_OPTIONS);
    }

    public function dataTypeSupportsOptionKey($datatype, $key)
    {
        if ($this->dataTypeSupportsOptions($datatype)) {
            return in_array($key, self::DATA_TYPE_FIELD_OPTIONS_SUPPORTED[$datatype]);
        }
        return false;
    }
}
