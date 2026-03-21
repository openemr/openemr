<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Method pnHTML\\:\\:__construct\\(\\) with return type void returns true but should not return anything\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnHTML.php',
];
$ignoreErrors[] = [
    'message' => '#^Method edih_x12_file\\:\\:__construct\\(\\) with return type void returns mixed but should not return anything\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_x12file_class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method edih_x12_file\\:\\:__construct\\(\\) with return type void returns true but should not return anything\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_x12file_class.php',
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
