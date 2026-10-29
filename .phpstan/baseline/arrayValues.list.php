<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$array \\(list\\<mixed\\>\\) of array_values is already a list, call has no effect\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/orm/src/Cache/Persister/Collection/AbstractCollectionPersister.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$array \\(list\\<string\\>\\) of array_values is already a list, call has no effect\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/orm/src/Mapping/ClassMetadataInfo.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$array \\(list\\<array\\>\\) of array_values is already a list, call has no effect\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/console/Descriptor/TextDescriptor.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$array \\(list\\<array\\{function\\: string, line\\?\\: int, file\\?\\: string, class\\?\\: class\\-string, type\\?\\: \'\\-\\>\'\\|\'\\:\\:\', args\\?\\: list\\<mixed\\>, object\\?\\: object\\}\\>\\) of array_values is already a list, call has no effect\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-file-integrity/vendor/phpunit/phpunit/src/Runner/ErrorHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$array \\(list\\<array\\>\\) of array_values is already a list, call has no effect\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-file-integrity/vendor/symfony/console/Descriptor/TextDescriptor.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$array \\(list\\<array\\{function\\: string, line\\?\\: int, file\\?\\: string, class\\?\\: class\\-string, type\\?\\: \'\\-\\>\'\\|\'\\:\\:\', args\\?\\: list\\<mixed\\>, object\\?\\: object\\}\\>\\) of array_values is already a list, call has no effect\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-healthlink/vendor/phpunit/phpunit/src/Runner/ErrorHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$array \\(list\\<OpenEMR\\\\Modules\\\\IHI\\\\Service\\\\UatTestDataItem\\>\\) of array_values is already a list, call has no effect\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/src/Service/UatTestDataPlanBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$array \\(list\\<array\\{function\\: string, line\\?\\: int, file\\?\\: string, class\\?\\: class\\-string, type\\?\\: \'\\-\\>\'\\|\'\\:\\:\', args\\?\\: list\\<mixed\\>, object\\?\\: object\\}\\>\\) of array_values is already a list, call has no effect\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/phpunit/phpunit/src/Runner/ErrorHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$array \\(list\\<array\\{file\\: string, line\\: int\\|null, class\\?\\: class\\-string, function\\?\\: string, type\\: string\\}\\>\\) of array_values is already a list, call has no effect\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module/vendor/phpunit/phpunit/src/Runner/ErrorHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$array \\(list\\<array\\{function\\: string, line\\?\\: int, file\\?\\: string, class\\?\\: class\\-string, type\\?\\: \'\\-\\>\'\\|\'\\:\\:\', args\\?\\: list\\<mixed\\>, object\\?\\: object\\}\\>\\) of array_values is already a list, call has no effect\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-opit-compatibility/vendor/phpunit/phpunit/src/Runner/ErrorHandler.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
