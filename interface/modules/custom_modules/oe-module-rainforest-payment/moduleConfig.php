<?php

/**
 * Rainforest Payment Module Information
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Firehed
 * @copyright Copyright (c) 2025 Firehed
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

return [
    'name' => 'Rainforest Payment Gateway',
    'description' => 'Integration with Rainforest payment gateway for patient portal payments',
    'version' => '1.0.0',
    'author' => 'Firehed',
    'email' => '',
    'license' => 'GPL-3.0',
    'acl_category' => 'admin',
    'acl_section' => 'admin',

    // Module dependencies
    'require' => [
        'openemr' => '>=7.0.0',
    ],

    // Database tables created by this module (none currently)
    'tables' => [],

    // Menu items added by this module (none currently - configured via globals)
    'menu' => [],

    // Global settings added by this module
    'globals' => [
        [
            'name' => 'rainforest_payment_enabled',
            'type' => 'bool',
            'default' => '0',
            'description' => 'Enable Rainforest Payment Gateway',
        ],
        [
            'name' => 'rainforest_api_key',
            'type' => 'encrypted',
            'default' => '',
            'description' => 'Rainforest API Key',
        ],
        [
            'name' => 'rainforest_merchant_id',
            'type' => 'encrypted',
            'default' => '',
            'description' => 'Rainforest Merchant ID',
        ],
        [
            'name' => 'rainforest_platform_id',
            'type' => 'encrypted',
            'default' => '',
            'description' => 'Rainforest Platform ID',
        ],
    ],
];
