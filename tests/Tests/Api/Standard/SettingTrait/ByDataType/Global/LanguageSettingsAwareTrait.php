<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Api\Standard\SettingTrait\ByDataType\Global;

trait LanguageSettingsAwareTrait
{
    protected static function getLanguageDataProviderChunks(): iterable
    {
        yield [
            'locale',
            'language_default',
            [
                'setting_key' => 'language_default',
                'setting_name' => 'Default Language',
                'setting_description' => 'Default language if no other is allowed or chosen.',
                'setting_default_value' => 'English (Standard)',
                'setting_is_default_value' => true,
                'setting_value' => 'English (Standard)',
                'setting_value_options' => [
                    [ 'option_value' => 'Albanian', 'option_label' => 'Albanian' ],
                    [ 'option_value' => 'Amharic', 'option_label' => 'Amharic' ],
                    [ 'option_value' => 'Arabic', 'option_label' => 'Arabic' ],
                    [ 'option_value' => 'Armenian', 'option_label' => 'Armenian' ],
                    [ 'option_value' => 'Bahasa Indonesia', 'option_label' => 'Bahasa Indonesia' ],
                    [ 'option_value' => 'Bengali', 'option_label' => 'Bengali' ],
                    [ 'option_value' => 'Bosnian', 'option_label' => 'Bosnian' ],
                    [ 'option_value' => 'Bulgarian', 'option_label' => 'Bulgarian' ],
                    [ 'option_value' => 'Chinese (Simplified)', 'option_label' => 'Chinese (Simplified)' ],
                    [ 'option_value' => 'Chinese (Traditional)', 'option_label' => 'Chinese (Traditional)' ],
                    [ 'option_value' => 'Croatian', 'option_label' => 'Croatian' ],
                    [ 'option_value' => 'Czech', 'option_label' => 'Czech' ],
                    [ 'option_value' => 'Danish', 'option_label' => 'Danish' ],
                    [ 'option_value' => 'dummy', 'option_label' => 'dummy' ],
                    [ 'option_value' => 'Dutch', 'option_label' => 'Dutch' ],
                    [ 'option_value' => 'English (Australian)', 'option_label' => 'English (Australian)' ],
                    [ 'option_value' => 'English (Indian)', 'option_label' => 'English (Indian)' ],
                    [ 'option_value' => 'English (Standard)', 'option_label' => 'English (Standard)' ],
                    [ 'option_value' => 'Estonian', 'option_label' => 'Estonian' ],
                    [ 'option_value' => 'Filipino', 'option_label' => 'Filipino' ],
                    [ 'option_value' => 'Finnish', 'option_label' => 'Finnish' ],
                    [ 'option_value' => 'French (Canadian)', 'option_label' => 'French (Canadian)' ],
                    [ 'option_value' => 'French (Standard)', 'option_label' => 'French (Standard)' ],
                    [ 'option_value' => 'Georgian', 'option_label' => 'Georgian' ],
                    [ 'option_value' => 'German', 'option_label' => 'German' ],
                    [ 'option_value' => 'Greek', 'option_label' => 'Greek' ],
                    [ 'option_value' => 'Gujarati', 'option_label' => 'Gujarati' ],
                    [ 'option_value' => 'Hebrew', 'option_label' => 'Hebrew' ],
                    [ 'option_value' => 'Hindi', 'option_label' => 'Hindi' ],
                    [ 'option_value' => 'Hungarian', 'option_label' => 'Hungarian' ],
                    [ 'option_value' => 'Italian', 'option_label' => 'Italian' ],
                    [ 'option_value' => 'Japanese', 'option_label' => 'Japanese' ],
                    [ 'option_value' => 'Korean', 'option_label' => 'Korean' ],
                    [ 'option_value' => 'Lao', 'option_label' => 'Lao' ],
                    [ 'option_value' => 'Lithuanian', 'option_label' => 'Lithuanian' ],
                    [ 'option_value' => 'Marathi', 'option_label' => 'Marathi' ],
                    [ 'option_value' => 'Mongolian', 'option_label' => 'Mongolian' ],
                    [ 'option_value' => 'Norwegian', 'option_label' => 'Norwegian' ],
                    [ 'option_value' => 'Persian', 'option_label' => 'Persian' ],
                    [ 'option_value' => 'Polish', 'option_label' => 'Polish' ],
                    [ 'option_value' => 'Portuguese (Angolan)', 'option_label' => 'Portuguese (Angolan)' ],
                    [ 'option_value' => 'Portuguese (Brazilian)', 'option_label' => 'Portuguese (Brazilian)' ],
                    [ 'option_value' => 'Portuguese (European)', 'option_label' => 'Portuguese (European)' ],
                    [ 'option_value' => 'Romanian', 'option_label' => 'Romanian' ],
                    [ 'option_value' => 'Russian', 'option_label' => 'Russian' ],
                    [ 'option_value' => 'Serbian', 'option_label' => 'Serbian' ],
                    [ 'option_value' => 'Sinhala', 'option_label' => 'Sinhala' ],
                    [ 'option_value' => 'Slovak', 'option_label' => 'Slovak' ],
                    [ 'option_value' => 'Somali', 'option_label' => 'Somali' ],
                    [ 'option_value' => 'Spanish (Latin American)', 'option_label' => 'Spanish (Latin American)' ],
                    [ 'option_value' => 'Spanish (Spain)', 'option_label' => 'Spanish (Spain)' ],
                    [ 'option_value' => 'Swedish', 'option_label' => 'Swedish' ],
                    [ 'option_value' => 'Tamil', 'option_label' => 'Tamil' ],
                    [ 'option_value' => 'Telugu', 'option_label' => 'Telugu' ],
                    [ 'option_value' => 'Thai', 'option_label' => 'Thai' ],
                    [ 'option_value' => 'Turkish', 'option_label' => 'Turkish' ],
                    [ 'option_value' => 'Ukrainian', 'option_label' => 'Ukrainian' ],
                    [ 'option_value' => 'Urdu', 'option_label' => 'Urdu' ],
                    [ 'option_value' => 'Vietnamese', 'option_label' => 'Vietnamese' ]
                ],
            ]
        ];
    }
}
