<?php

/**
 * Routes
 * (All REST routes)
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Yash Raj Bothra <yashrajbothra786@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018-2020 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019-2021 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Yash Raj Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenApi\Annotations as OA;
use OpenEMR\RestControllers\Config\RestConfig;

// Lets keep our controller classes with the routes.
//

// Note some Http clients may not send auth as json so a function
// is implemented to determine and parse encoding on auth route's.

// Note that the api route is only for users role
//  (there is a mechanism in place to ensure only user role can access the api route)

//RestConfig::$ROUTE_MAP = array_merge(
////    require_once __DIR__ . "/apis/routes/_rest_routes_standard.inc.php",
////
////    require_once __DIR__ . '/apis/routes/standard/_rest_routes_standard_common.inc.php',
////
////    require_once __DIR__ . '/apis/routes/standard/user/_rest_routes_standard_user_setting.inc.php',
////    require_once __DIR__ . '/apis/routes/standard/user/_rest_routes_standard_user.inc.php', // @todo Decide
////
////    require_once __DIR__ . '/apis/routes/standard/admin/_rest_routes_standard_admin_global_setting.inc.php',
////    require_once __DIR__ . '/apis/routes/standard/admin/user/_rest_routes_standard_admin_user_setting.inc.php',
////    require_once __DIR__ . '/apis/routes/standard/admin/user/_rest_routes_standard_admin_user.inc.php',
////
////    require_once __DIR__ . '/apis/routes/standard/admin/acl/_rest_routes_standard_admin_acl_section.inc.php',
////    require_once __DIR__ . '/apis/routes/standard/admin/acl/_rest_routes_standard_admin_acl_group_member.inc.php',
////    require_once __DIR__ . '/apis/routes/standard/admin/acl/_rest_routes_standard_admin_acl_user_setting.inc.php',
////    require_once __DIR__ . '/apis/routes/standard/admin/acl/_rest_routes_standard_admin_acl_group_setting.inc.php',
////    require_once __DIR__ . '/apis/routes/standard/admin/acl/_rest_routes_standard_admin_acl_group.inc.php',
//);
//
//RestConfig::$FHIR_ROUTE_MAP = require_once __DIR__ . "/apis/routes/_rest_routes_fhir_r4_us_core_3_1_0.inc.php";
//
//RestConfig::$PORTAL_ROUTE_MAP = require_once __DIR__ . "/apis/routes/_rest_routes_portal.inc.php";
