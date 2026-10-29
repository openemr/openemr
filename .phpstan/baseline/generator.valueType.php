<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Generator expects value type Symfony\\\\Component\\\\Console\\\\Helper\\\\TreeNode, mixed given\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/console/Helper/TreeNode.php',
];
$ignoreErrors[] = [
    'message' => '#^Generator expects value type Symfony\\\\Component\\\\Routing\\\\Annotation\\\\Route, object given\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/routing/Loader/AnnotationClassLoader.php',
];
$ignoreErrors[] = [
    'message' => '#^Generator expects value type Symfony\\\\Component\\\\Console\\\\Helper\\\\TreeNode, mixed given\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-file-integrity/vendor/symfony/console/Helper/TreeNode.php',
];
$ignoreErrors[] = [
    'message' => '#^Generator expects value type array\\{int, string, array\\{id\\: string, type\\: string\\}\\|null\\}, array\\{string, array\\{id\\: string, type\\: string\\}\\|null\\} given\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-healthlink/src/Processing/BatchParseResult.php',
];
$ignoreErrors[] = [
    'message' => '#^Generator expects value type string, mixed given\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/process/InputStream.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
