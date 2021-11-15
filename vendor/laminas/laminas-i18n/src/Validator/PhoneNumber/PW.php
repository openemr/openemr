<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '680',
    'patterns' => [
        'national' => [
            'general' => '/^[2-8]\\d{6}$/',
            'fixed' => '/^(?:2552255|(?:277|345|488|5(?:35|44|87)|6(?:22|54|79)|7(?:33|47)|8(?:24|55|76))\\d{4})$/',
            'mobile' => '/^(?:6[234689]0|77[45789])\\d{4}$/',
            'emergency' => '/^911$/',
        ],
        'possible' => [
            'general' => '/^\\d{7}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
