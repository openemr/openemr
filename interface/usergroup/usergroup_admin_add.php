<?php

require_once("../globals.php");
require_once("../../library/acl.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/erx_javascript.inc.php");

$facilityService = new \services\FacilityService();

$use_validate_js = 1;
$viewVars['userValidateJs'] = $use_validate_js;

// @TODO
//require_once($GLOBALS['srcdir'] . "/validation/validation_script.js.php");//Gets validation rules from Page Validation list.

//Note that for technical reasons, we are bypassing the standard validateUsingPageRules() call.
$collectthis = collectValidationPageRules("/interface/usergroup/usergroup_admin_add.php");
if (empty($collectthis)) {
    $collectthis = "undefined";
} else {
    $collectthis = $collectthis["new_user"]["rules"];
}
$viewVars['collectThis'] = $collectthis;

$serviceLocationRes = $facilityService->getAllServiceLocations();
$serviceLocations = [];
if ($serviceLocationRes) {
    foreach ($serviceLocations as $location) {
        $serviceLocations[] = $location;
    }
}

$groups = [];
if (empty($GLOBALS['disable_non_default_groups'])) {
    $groupsRes = sqlStatement("SELECT DISTINCT name FROM groups ORDER BY name");
    while ($group = sqlFetchArray($groupsRes)) {
        $groups[] = $group;
    }
}

$list_acl_groups = acl_get_group_title_list();
$default_acl_group = 'Administrators';
$aclList = [];
foreach ($list_acl_groups as $value) {
    $tmp = ['value' => $value, 'display' => xl_gacl_group($value)];
    if ($default_acl_group == $value) {
        $tmp['selected'] = true;
    }
    $aclList[] = $tmp;
}

$userRes = sqlStatement("select distinct username from users where username != ''");
$users = [];
while ($row = sqlFetchArray($userRes)) {
    $users[] = $row;
}

$viewVars = [
    'alertmsg' => '',
    'physician_select_box' => generate_select_list("physician_type", "physician_type", '','',xl('Select Type'),'form-control'),
    'newcrop_erx_role_select_box' => generate_select_list("erxrole", "newcrop_erx_role", '','','--Select Role--','form-control'),
    'default_warehouse_select_box' => generate_select_list('default_warehouse', 'warehouse', '', '', '', 'form-control'),
    'irnpool_select_box' => generate_select_list('irnpool', 'irnpool', '', xl('Invoice reference number pool, if used', '', '', 'form-control')),
    'authorizations' => [1 => xl("None"), 2 => xl("Only Mine"), 3 => xl("All"),],
    'facilities' => $facilityService->getAll(),
    'serviceLocations' => $serviceLocations,
    'groups' => $groups,
    'users' => $users,
    'aclList' => $aclList,
];

echo $GLOBALS['twig']->render("admin/usergroup/add_user.html.twig", $viewVars);
