<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^PHPDoc type template_dir of property C_AbstractClickmap\\:\\:\\$template_dir is not the same as PHPDoc type array of overridden property Smarty\\:\\:\\$template_dir\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/clickmap/C_AbstractClickmap.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var for property Phreezable\\:\\:\\$PublicPropCache with type cache is incompatible with native type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var for property Reporter\\:\\:\\$PublicPropCache with type cache is incompatible with native type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Reporter.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc type for property OpenEMR\\\\Events\\\\AbstractBoundFilterEvent\\:\\:\\$boundFilter with type string is incompatible with native type OpenEMR\\\\Events\\\\BoundFilter\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/AbstractBoundFilterEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc type for property OpenEMR\\\\Events\\\\Globals\\\\GlobalsInitializedEvent\\:\\:\\$globalsService with type array is incompatible with native type OpenEMR\\\\Services\\\\Globals\\\\GlobalsService\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Globals/GlobalsInitializedEvent.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
