<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 *
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2019 Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Globals;

use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

/**
 * Represents a global setting
 *
 * @phpstan-type TSettingMetadata = array{
 *     0: string,
 *     1: string,
 *     2: string,
 *     3: string,
 *     4?: array,
 * }
 */
class GlobalSetting
{
    public const DATA_TYPE_ENUM = 'enum';

    // true false value represented as a boolean
    public const DATA_TYPE_BOOL = 'bool';

    // color picker setting
    public const DATA_TYPE_COLOR_CODE = 'color_code';

    // displays a password field box
    public const DATA_TYPE_PASS = 'pass';

    // used for encrypted field values
    public const DATA_TYPE_ENCRYPTED = 'encrypted';

    // used for encrypted hash field values
    public const DATA_TYPE_ENCRYPTED_HASH = 'encrypted_hash';

    // generates a random uuid if the value is an empty string
    public const DATA_TYPE_DEFAULT_RANDOM_UUID = 'if_empty_create_random_uuid';

    // 15 character maximum number string
    public const DATA_TYPE_NUMBER = 'num';

    public const DATA_TYPE_CODE_TYPES = 'all_code_types';

    // single select language
    public const DATA_TYPE_LANGUAGE = 'lang';

    // multiple select language selector
    public const DATA_TYPE_MULTI_LANGUAGE_SELECT = 'm_lang';

    // multiple select dashboard cards
    public const DATA_TYPE_MULTI_DASHBOARD_CARDS = 'm_dashboard_cards';

    // list of default visits in OpenEMR
    public const DATA_TYPE_DEFAULT_VISIT_CATEGORY = 'default_visit_category';

    // CSS Theme selector
    public const DATA_TYPE_CSS = 'css';

    // selector for types of theme.
    public const DATA_TYPE_TABS_CSS = 'tabs_css';

    // hour selector
    public const DATA_TYPE_HOUR = 'hour';

    // Textbox
    public const DATA_TYPE_TEXT = 'text';

    // HTML display section
    public const DATA_TYPE_HTML_DISPLAY_SECTION = 'html_display_section';

    public const DATA_TYPE_ADDRESS_BOOK = 'address_book';

    /**
     * Multiple list box with a dropdown selector to add list items.
     *
     * Items can be re-arranged in order.
     * Selected list items save the options property of the list into the globals setting.
     * Multiple values are separated by a semicolon (;).
     * Pass in a field option of 'list_id' => '<list-name-goes-here>' to the setting to choose the list.
     */
    public const DATA_TYPE_MULTI_SORTED_LIST_SELECTOR = 'multi_sorted_list_selector';

    /**
     * All possible values for $dataType.
     * Lines sorted.
     */
    public const ALL_DATA_TYPES = [
        self::DATA_TYPE_ADDRESS_BOOK,
        self::DATA_TYPE_BOOL,
        self::DATA_TYPE_CODE_TYPES,
        self::DATA_TYPE_COLOR_CODE,
        self::DATA_TYPE_CSS,
        self::DATA_TYPE_DEFAULT_RANDOM_UUID,
        self::DATA_TYPE_DEFAULT_VISIT_CATEGORY,
        self::DATA_TYPE_ENCRYPTED,
        self::DATA_TYPE_ENCRYPTED_HASH,
        self::DATA_TYPE_ENUM,
        self::DATA_TYPE_HOUR,
        self::DATA_TYPE_HTML_DISPLAY_SECTION,
        self::DATA_TYPE_LANGUAGE,
        self::DATA_TYPE_MULTI_DASHBOARD_CARDS,
        self::DATA_TYPE_MULTI_LANGUAGE_SELECT,
        self::DATA_TYPE_MULTI_SORTED_LIST_SELECTOR,
        self::DATA_TYPE_NUMBER,
        self::DATA_TYPE_PASS,
        self::DATA_TYPE_TABS_CSS,
        self::DATA_TYPE_TEXT,
    ];
    public const ALL_MULTI_DATA_TYPES = [
        self::DATA_TYPE_MULTI_LANGUAGE_SELECT,
        self::DATA_TYPE_MULTI_DASHBOARD_CARDS,
    ];

    /**
     * Mappings of the data types and the options they support
     */
    public const DATA_TYPE_FIELD_OPTIONS_SUPPORTED = [
        self::DATA_TYPE_ENUM => [
            self::DATA_TYPE_OPTION_ENUM_VALUES,
        ],
        self::DATA_TYPE_MULTI_SORTED_LIST_SELECTOR => [
            self::DATA_TYPE_OPTION_LIST_ID,
        ],
        self::DATA_TYPE_HTML_DISPLAY_SECTION => [
            self::DATA_TYPE_OPTION_RENDER_CALLBACK,
        ],
    ];

    public const DATA_TYPE_OPTION_ENUM_VALUES = 'enum_values';

    public const DATA_TYPE_OPTION_LIST_ID = 'list_id';

    public const DATA_TYPE_OPTION_RENDER_CALLBACK = 'render_callback';

    public const ALL_DATA_TYPE_OPTIONS = [
        self::DATA_TYPE_OPTION_ENUM_VALUES,
        self::DATA_TYPE_OPTION_LIST_ID,
        self::DATA_TYPE_OPTION_RENDER_CALLBACK,
    ];

    public const INDEX_NAME = 0;

    public const INDEX_DATA_TYPE = 1;

    public const INDEX_DEFAULT = 2;

    public const INDEX_DESCRIPTION = 3;

    public const INDEX_FIELD_OPTIONS = 4;

    protected string $dataType;

    /**
     * @var array Any specific field options
     */
    protected array $fieldOptions = [];

    /**
     * @param string|array<string|int, string> $dataType One of self::ALL_DATA_TYPES or array of key=>value options for select
     * @param null|bool|int|string $default
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        protected readonly string $name,
        string|array $dataType,
        protected readonly null|bool|int|string $default,
        protected readonly string $description,
        protected readonly bool $isUserSetting = false,
    ) {
        if (is_array($dataType)) {
            trigger_deprecation('openemr', '7.0.5', sprintf(
                'Passing array as 2nd parameter to GlobalSetting::__construct is deprecated. Change it to \'enum\' and specify option \'enum\' for setting %s.',
                $name,
            ));

            // Migrate to new format
            $this->dataType = self::DATA_TYPE_ENUM;
            $this->addFieldOption(self::DATA_TYPE_OPTION_ENUM_VALUES, $dataType);
        } else {
            $this->dataType = $dataType;
        }

        Assert::true(
            in_array($this->dataType, self::ALL_DATA_TYPES, true),
            sprintf(
                'Invalid data type %s',
                $this->dataType,
            )
        );
    }

    /**
     * @phpstan-return TSettingMetadata
     */
    public function format(): array
    {
        $result = [
            self::INDEX_NAME => $this->name,
            self::INDEX_DATA_TYPE => $this->dataType,
            self::INDEX_DEFAULT => $this->default,
            self::INDEX_DESCRIPTION => $this->description,
        ];

        if (!empty($this->fieldOptions)) {
            $result[] = $this->fieldOptions;
        }

        return $result;
    }

    public function isUserSetting(): bool
    {
        return $this->isUserSetting;
    }

    public function getFieldOptions(): array
    {
        return $this->fieldOptions;
    }

    public function getFieldOption(string $optionKey): array
    {
        Assert::true(
            isset(self::DATA_TYPE_FIELD_OPTIONS_SUPPORTED[$this->dataType]),
            sprintf(
                'Data type %s does not support field options',
                $this->dataType,
            )
        );

        Assert::true(
            in_array($optionKey, self::DATA_TYPE_FIELD_OPTIONS_SUPPORTED[$this->dataType], true),
            sprintf(
                'Data type %s does not support field option key %s',
                $this->dataType,
                $optionKey,
            )
        );

        return $this->fieldOptions[$optionKey];
    }

    /**
     * For now, we only support:
     * - list_id with the DATA_TYPE_MULTI_SORTED_LIST_SELECTOR
     * - render_callback with the DATA_TYPE_HTML_DISPLAY_SECTION
     *
     * @param string|array|callable $optionValue
     *
     * @throws InvalidArgumentException
     */
    public function addFieldOption(string $optionKey, $optionValue): void
    {
        Assert::true(
            isset(self::DATA_TYPE_FIELD_OPTIONS_SUPPORTED[$this->dataType]),
            sprintf(
                'Data type %s does not support field options',
                $this->dataType,
            )
        );

        Assert::true(
            in_array($optionKey, self::DATA_TYPE_FIELD_OPTIONS_SUPPORTED[$this->dataType], true),
            sprintf(
                'Data type %s does not support field option key %s',
                $this->dataType,
                $optionKey,
            )
        );

        if (
            self::DATA_TYPE_HTML_DISPLAY_SECTION === $this->dataType
            && self::DATA_TYPE_OPTION_RENDER_CALLBACK === $optionKey
        ) {
            Assert::isCallable($optionValue, sprintf(
                'Option %s for data type %s should be callable',
                $optionKey,
                $this->dataType
            ));
        }

        if (
            self::DATA_TYPE_ENUM === $this->dataType
            && self::DATA_TYPE_OPTION_ENUM_VALUES === $optionKey
        ) {
            Assert::isArray($optionValue, sprintf(
                'Option %s for data type %s should be array',
                $optionKey,
                $this->dataType
            ));
        }

        if (!isset($this->fieldOptions[$optionKey])) {
            $this->fieldOptions[$optionKey] = $optionValue;

            return;
        }

        $this->fieldOptions[$optionKey] = array_replace(
            $this->fieldOptions[$optionKey],
            $optionValue,
        );
    }
}
