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

namespace OpenEMR\Common\Database;

/**
 * Using constants from this class allow quicker navigation and search
 * places where we work with some particular tables for further refactoring
 */
final class DatabaseTables
{
    public const TABLE_GLOBAL_SETTINGS = 'globals';

    public const TABLE_USER_SETTINGS = 'user_settings';

    public const ALL_TABLES = [
        self::TABLE_GLOBAL_SETTINGS,
        self::TABLE_USER_SETTINGS,
    ];
}
