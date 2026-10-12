<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Function coding_carburetor\\(\\) should return array\\<array\\<string, mixed\\>\\> but empty return statement found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function display_QP\\(\\) should return string\\|false\\|null but empty return statement found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function priors_select\\(\\) should return string but empty return statement found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnModAPILoad\\(\\) should return true but empty return statement found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnModAvailable\\(\\) should return true but empty return statement found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnModLoad\\(\\) should return string\\|false\\|null but empty return statement found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Carecoordination\\\\Model\\\\EncounterccdadispatchTable\\:\\:getAuthor\\(\\) should return string but empty return statement found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Savant3_Plugin_date\\:\\:date\\(\\) should return string but empty return statement found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/savant/Savant3/resources/Savant3_Plugin_date.php',
];
$ignoreErrors[] = [
    'message' => '#^Method RequestUtil\\:\\:GetFileUpload\\(\\) should return FileUpload but empty return statement found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/HTTP/RequestUtil.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
