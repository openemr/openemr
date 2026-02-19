<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Call to static method PHPMailer\\\\PHPMailer\\\\PHPMailer\\:\\:validateAddress\\(\\) with incorrect case\\: ValidateAddress$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccr/createCCR.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to static method OpenEMR\\\\Common\\\\Database\\\\QueryUtils\\:\\:rollbackTransaction\\(\\) with incorrect case\\: rollBackTransaction$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/history_sdoh_health_concerns.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method ADODB_mysqli\\:\\:execute\\(\\) with incorrect case\\: Execute$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/ADODB_mysqli_log.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to static method OpenEMR\\\\Billing\\\\BillingReport\\:\\:generateTheQueryPart\\(\\) with incorrect case\\: GenerateTheQueryPart$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/daysheet.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to static method RequestUtil\\:\\:GetCurrentURL\\(\\) with incorrect case\\: GetCurrentUrl$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/ActionRouter.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to static method VerySimpleStringUtil\\:\\:UTF8ToHTML\\(\\) with incorrect case\\: UTF8ToHtml$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/String/VerySimpleStringUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to static method OpenEMR\\\\RestControllers\\\\Config\\\\RestConfig\\:\\:SetPaths\\(\\) with incorrect case\\: setPaths$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Config/RestConfig.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
