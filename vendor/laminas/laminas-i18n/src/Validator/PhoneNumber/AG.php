<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '1',
    'patterns' => [
        'national' => [
            'general' => '/^[2589]\d{9}$/',
            'fixed' => '/^268(?:4(?:6[0-38]|84)|56[0-2])\d{4}$/',
            'mobile' => '/^268(?:464|7(?:2[0-9]|64|7[0-689]|8[02-68]))\d{4}$/',
            'pager' => '/^26840[69]\d{4}$/',
            'tollfree' => '/^8(?:00|55|66|77|88)[2-9]\d{6}$/',
            'premium' => '/^900[2-9]\d{6}$/',
            'personal' => '/^5(?:00|33|44)[2-9]\d{6}$/',
            'voip' => '/^26848[01]\d{4}$/',
            'emergency' => '/^9(?:11|99)$/',
        ],
        'possible' => [
            'general' => '/^\d{7}(?:\d{3})?$/',
            'mobile' => '/^\d{10}$/',
            'pager' => '/^\d{10}$/',
            'tollfree' => '/^\d{10}$/',
            'premium' => '/^\d{10}$/',
            'personal' => '/^\d{10}$/',
            'voip' => '/^\d{10}$/',
            'emergency' => '/^\d{3}$/',
        ],
    ],
];
