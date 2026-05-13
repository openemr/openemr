<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Billing\\\\EdiHistory\\\\X12File\\:\\:__construct\\(\\) with return type void returns mixed but should not return anything\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/EdiHistory/X12File.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Billing\\\\EdiHistory\\\\X12File\\:\\:__construct\\(\\) with return type void returns true but should not return anything\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/EdiHistory/X12File.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\Core\\\\TemplatePageEvent\\:\\:setTwigVariables\\(\\) with return type void returns \\$this\\(OpenEMR\\\\Events\\\\Core\\\\TemplatePageEvent\\) but should not return anything\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Core/TemplatePageEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\UserInterface\\\\BaseActionButtonHelper\\:\\:getHref\\(\\) with return type void returns mixed but should not return anything\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/UserInterface/BaseActionButtonHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\Gacl\\:\\:__construct\\(\\) with return type void returns true but should not return anything\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/Gacl.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
