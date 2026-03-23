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
    // GACL
    public const TABLE_GACL_ACL = 'gacl_acl';
    public const TABLE_GACL_ACL_SECTIONS = 'gacl_acl_sections';
    public const TABLE_GACL_ACL_SEQ = 'gacl_acl_seq';
    public const TABLE_GACL_ACO = 'gacl_aco';
    public const TABLE_GACL_ACO_MAP = 'gacl_aco_map';
    public const TABLE_GACL_ACO_SECTIONS = 'gacl_aco_sections';
    public const TABLE_GACL_ACO_SECTIONS_SEQ = 'gacl_aco_sections_seq';
    public const TABLE_GACL_ACO_SEQ = 'gacl_aco_seq';
    public const TABLE_GACL_ARO = 'gacl_aro';
    public const TABLE_GACL_ARO_GROUPS = 'gacl_aro_groups';
    public const TABLE_GACL_ARO_GROUPS_ID_SEQ = 'gacl_aro_groups_id_seq';
    public const TABLE_GACL_ARO_GROUPS_MAP = 'gacl_aro_groups_map';
    public const TABLE_GACL_ARO_MAP = 'gacl_aro_map';
    public const TABLE_GACL_ARO_SECTIONS = 'gacl_aro_sections';
    public const TABLE_GACL_ARO_SECTIONS_SEQ = 'gacl_aro_sections_seq';
    public const TABLE_GACL_ARO_SEQ = 'gacl_aro_seq';
    public const TABLE_GACL_AXO = 'gacl_axo';
    public const TABLE_GACL_AXO_GROUPS = 'gacl_axo_groups';
    public const TABLE_GACL_AXO_GROUPS_MAP = 'gacl_axo_groups_map';
    public const TABLE_GACL_AXO_MAP = 'gacl_axo_map';
    public const TABLE_GACL_AXO_SECTIONS = 'gacl_axo_sections';
    public const TABLE_GACL_GROUPS_ARO_MAP = 'gacl_groups_aro_map';
    public const TABLE_GACL_GROUPS_AXO_MAP = 'gacl_groups_axo_map';
    public const TABLE_GACL_PHPGACL = 'gacl_phpgacl';

    public const TABLE_MODULE_ACL_GROUP_SETTINGS = 'module_acl_group_settings';
    public const TABLE_MODULE_ACL_SECTIONS = 'module_acl_sections';
    public const TABLE_MODULE_ACL_USER_SETTINGS = 'module_acl_user_settings';

    public const ACL = [
        self::TABLE_GACL_ACL,
        self::TABLE_GACL_ACL_SECTIONS,
        self::TABLE_GACL_ACL_SEQ,
        self::TABLE_GACL_ACO,
        self::TABLE_GACL_ACO_MAP,
        self::TABLE_GACL_ACO_SECTIONS,
        self::TABLE_GACL_ACO_SECTIONS_SEQ,
        self::TABLE_GACL_ACO_SEQ,
        self::TABLE_GACL_ARO,
        self::TABLE_GACL_ARO_GROUPS,
        self::TABLE_GACL_ARO_GROUPS_ID_SEQ,
        self::TABLE_GACL_ARO_GROUPS_MAP,
        self::TABLE_GACL_ARO_MAP,
        self::TABLE_GACL_ARO_SECTIONS,
        self::TABLE_GACL_ARO_SECTIONS_SEQ,
        self::TABLE_GACL_ARO_SEQ,
        self::TABLE_GACL_AXO,
        self::TABLE_GACL_AXO_GROUPS,
        self::TABLE_GACL_AXO_GROUPS_MAP,
        self::TABLE_GACL_AXO_MAP,
        self::TABLE_GACL_AXO_SECTIONS,
        self::TABLE_GACL_GROUPS_ARO_MAP,
        self::TABLE_GACL_GROUPS_AXO_MAP,
        self::TABLE_GACL_PHPGACL,

        self::TABLE_MODULE_ACL_GROUP_SETTINGS,
        self::TABLE_MODULE_ACL_SECTIONS,
        self::TABLE_MODULE_ACL_USER_SETTINGS,
    ];

    // Auth
    public const TABLE_GROUPS = 'groups';
    public const TABLE_OAUTH_CLIENTS = 'oauth_clients';
    public const TABLE_USERS = 'users';
    public const TABLE_USERS_SECURE = 'users_secure';

    public const AUTH = [
        self::TABLE_GROUPS,
        self::TABLE_OAUTH_CLIENTS,
        self::TABLE_USERS,
        self::TABLE_USERS_SECURE,
    ];

    // Settings
    public const TABLE_GLOBAL_SETTINGS = 'globals';

    public const TABLE_USER_SETTINGS = 'user_settings';

    public const TABLE_KEYS = 'keys';

    public const SETTINGS = [
        self::TABLE_GLOBAL_SETTINGS,
        self::TABLE_USER_SETTINGS,
        self::TABLE_KEYS,
    ];

    public const TABLE_CODE_TYPES = 'code_types';

    public const TABLE_LANGUAGES = 'lang_languages';

    public const TABLE_POSTCALENDAR_CATEGORIES = 'openemr_postcalendar_categories';

    /**
     * All tables we have purgers data for
     *
     * - ACL (except AXO, as we're not using it)
     * - Auth
     */
    public const PURGEABLE = [
        self::TABLE_GACL_ACL,
        self::TABLE_GACL_ACL_SECTIONS,
        self::TABLE_GACL_ACL_SEQ,
        self::TABLE_GACL_ACO,
        self::TABLE_GACL_ACO_MAP,
        self::TABLE_GACL_ACO_SECTIONS,
        self::TABLE_GACL_ACO_SECTIONS_SEQ,
        self::TABLE_GACL_ACO_SEQ,
        self::TABLE_GACL_ARO,
        self::TABLE_GACL_ARO_GROUPS,
        self::TABLE_GACL_ARO_GROUPS_ID_SEQ,
        self::TABLE_GACL_ARO_GROUPS_MAP,
        self::TABLE_GACL_ARO_MAP,
        self::TABLE_GACL_ARO_SECTIONS,
        self::TABLE_GACL_ARO_SECTIONS_SEQ,
        self::TABLE_GACL_ARO_SEQ,
        self::TABLE_GACL_GROUPS_ARO_MAP,

        self::TABLE_MODULE_ACL_GROUP_SETTINGS,
        self::TABLE_MODULE_ACL_SECTIONS,
        self::TABLE_MODULE_ACL_USER_SETTINGS,

        self::TABLE_GROUPS,
        self::TABLE_OAUTH_CLIENTS,
        self::TABLE_USERS,
        self::TABLE_USERS_SECURE,
    ];

//    public const ALL_TABLES = [
//        self::TABLE_GLOBAL_SETTINGS,
//        self::TABLE_USER_SETTINGS,
//    ];
}
