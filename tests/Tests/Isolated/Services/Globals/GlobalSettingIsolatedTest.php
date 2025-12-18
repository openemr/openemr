<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Services\Globals;

use OpenEMR\Services\Globals\GlobalSetting;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

#[Group('isolated')]
#[Group('setting')]
#[CoversClass(GlobalSetting::class)]
#[CoversMethod(GlobalSetting::class, 'addFieldOption')]
#[CoversMethod(GlobalSetting::class, 'getFieldOption')]
class GlobalSettingIsolatedTest extends TestCase
{
    #[Test]
    #[DataProvider('addFieldOptionSucceededDataProvider')]
    public function addFieldOptionSucceededTest(
        string|array $dataType,
        string $optionKey,
        $optionValue,
        ?array $expectedOptions = null
    ): void {
        $setting = new GlobalSetting('Label', $dataType, '', 'Description');
        $setting->addFieldOption($optionKey, $optionValue);

        if (null !== $expectedOptions) {
            $this->assertEquals(
                $expectedOptions,
                $setting->getFieldOptions(),
            );

            return;
        }

        // Not having exception here is success
        $this->assertTrue(true);
    }

    public static function addFieldOptionSucceededDataProvider(): iterable
    {
        yield 'Enum values - Deprecated format - Numeric keys - Starting from 0' => [
            [
                0 => 'Option 1',
                1 => 'Option 2',
            ],
            GlobalSetting::DATA_TYPE_OPTION_ENUM_VALUES,
            [
                2 => 'Option 3',
                3 => 'Option 4',
            ],
            [
                GlobalSetting::DATA_TYPE_OPTION_ENUM_VALUES => [
                    0 => 'Option 1',
                    1 => 'Option 2',
                    2 => 'Option 3',
                    3 => 'Option 4',
                ],
            ],
        ];

        yield 'Enum values - Deprecated format - Numeric keys - Starting from 1' => [
            [
                1 => 'Option 1',
                2 => 'Option 2',
            ],
            GlobalSetting::DATA_TYPE_OPTION_ENUM_VALUES,
            [
                3 => 'Option 3',
                4 => 'Option 4',
            ],
            [
                GlobalSetting::DATA_TYPE_OPTION_ENUM_VALUES => [
                    1 => 'Option 1',
                    2 => 'Option 2',
                    3 => 'Option 3',
                    4 => 'Option 4',
                ],
            ],
        ];

        yield 'Enum values - Deprecated format - String keys' => [
            [
                'option-1' => 'Option 1',
                'option-2' => 'Option 2',
            ],
            GlobalSetting::DATA_TYPE_OPTION_ENUM_VALUES,
            [
                'option-3' => 'Option 3',
                'option-4' => 'Option 4',
            ],
            [
                GlobalSetting::DATA_TYPE_OPTION_ENUM_VALUES => [
                    'option-1' => 'Option 1',
                    'option-2' => 'Option 2',
                    'option-3' => 'Option 3',
                    'option-4' => 'Option 4',
                ],
            ],
        ];

        yield 'Enum values - New format - Numeric keys' => [
            GlobalSetting::DATA_TYPE_ENUM,
            GlobalSetting::DATA_TYPE_OPTION_ENUM_VALUES,
            [
                0 => 'Option 1',
                1 => 'Option 2',
            ],
            [
                GlobalSetting::DATA_TYPE_OPTION_ENUM_VALUES => [
                    0 => 'Option 1',
                    1 => 'Option 2',
                ],
            ],
        ];

        yield 'Enum values - New format - String keys' => [
            GlobalSetting::DATA_TYPE_ENUM,
            GlobalSetting::DATA_TYPE_OPTION_ENUM_VALUES,
            [
                'enum-option-1' => 'Option 1',
                'enum-option-2' => 'Option 2',
            ],
            [
                GlobalSetting::DATA_TYPE_OPTION_ENUM_VALUES => [
                    'enum-option-1' => 'Option 1',
                    'enum-option-2' => 'Option 2',
                ],
            ],
        ];

        yield 'Sorted list' => [
            GlobalSetting::DATA_TYPE_MULTI_SORTED_LIST_SELECTOR,
            GlobalSetting::DATA_TYPE_OPTION_LIST_ID,
            'ccda-sections',
            [
                GlobalSetting::DATA_TYPE_OPTION_LIST_ID => 'ccda-sections',
            ]
        ];

        yield 'Callback' => [
            GlobalSetting::DATA_TYPE_HTML_DISPLAY_SECTION,
            GlobalSetting::DATA_TYPE_OPTION_RENDER_CALLBACK,
            fn (): string => '',
        ];
    }

    #[Test]
    #[DataProvider('addFieldOptionFailedDataProvider')]
    public function addFieldOptionFailedTest(
        GlobalSetting $setting,
        string $optionKey,
        $optionValue,
        string $expectedExceptionMessage,
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $setting->addFieldOption($optionKey, $optionValue);
    }

    /**
     * Adding field option other than GlobalSetting::DATA_TYPE_OPTION_LIST_ID
     * to dataType GlobalSetting::DATA_TYPE_MULTI_SORTED_LIST_SELECTOR should fail
     *
     * Adding field option other than GlobalSetting::DATA_TYPE_OPTION_RENDER_CALLBACK
     * to dataType GlobalSetting::DATA_TYPE_HTML_DISPLAY_SECTION should fail
     */
    public static function addFieldOptionFailedDataProvider(): iterable
    {
        foreach (GlobalSetting::ALL_DATA_TYPES as $dataType) {
            foreach (GlobalSetting::ALL_DATA_TYPE_OPTIONS as $optionKey) {
                if (array_key_exists($dataType, GlobalSetting::DATA_TYPE_FIELD_OPTIONS_SUPPORTED)) {
                    continue;
                }

                yield sprintf('%s x %s', $dataType, $optionKey) => [
                    new GlobalSetting('Label', $dataType, '', 'Description'),
                    $optionKey,
                    GlobalSetting::DATA_TYPE_OPTION_RENDER_CALLBACK === $optionKey ? (fn (): string => '') : '',
                    sprintf(
                        'Data type %s does not support field options',
                        $dataType,
                    ),
                ];
            }
        }

        yield 'Incorrect option passed to multi_sorted_list_selector data type' => [
            new GlobalSetting('Label', GlobalSetting::DATA_TYPE_MULTI_SORTED_LIST_SELECTOR, '', 'Description'),
            GlobalSetting::DATA_TYPE_OPTION_RENDER_CALLBACK,
            '',
            'Data type multi_sorted_list_selector does not support field option key render_callback',
        ];

        yield 'Incorrect option passed to html_display_section data type' => [
            new GlobalSetting('Label', GlobalSetting::DATA_TYPE_HTML_DISPLAY_SECTION, '', 'Description'),
            GlobalSetting::DATA_TYPE_OPTION_LIST_ID,
            '',
            'Data type html_display_section does not support field option key list_id',
        ];

        yield 'Not callable option passed to render_callback option' => [
            new GlobalSetting('Label', GlobalSetting::DATA_TYPE_HTML_DISPLAY_SECTION, '', 'Description'),
            GlobalSetting::DATA_TYPE_OPTION_RENDER_CALLBACK,
            'not-a-callable',
            'Option render_callback for data type html_display_section should be callable',
        ];
    }
}
