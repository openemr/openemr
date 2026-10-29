<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Parameter &\\$gui @param\\-out type of method OpenEMR\\\\Modules\\\\IHI\\\\Commands\\\\UatPopulateCommand\\:\\:browserCreator\\(\\) expects OpenEMR\\\\Modules\\\\IHI\\\\Service\\\\GuiPatientDriver, OpenEMR\\\\Modules\\\\IHI\\\\Service\\\\GuiPatientDriver\\|null given\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/src/Commands/UatPopulateCommand.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
