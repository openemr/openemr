<?php declare(strict_types = 1);

$ignoreErrors = [];
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
return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
