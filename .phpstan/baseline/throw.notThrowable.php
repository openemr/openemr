<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Invalid type mixed to throw\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/cache/LockRegistry.php',
];
$ignoreErrors[] = [
    'message' => '#^Invalid type Exception\\|null to throw\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/console/Helper/QuestionHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Invalid type mixed to throw\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/routing/Matcher/Dumper/CompiledUrlMatcherDumper.php',
];
$ignoreErrors[] = [
    'message' => '#^Invalid type Exception\\|null to throw\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-file-integrity/vendor/symfony/console/Helper/QuestionHelper.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
