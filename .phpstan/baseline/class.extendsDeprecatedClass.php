<?php declare(strict_types = 1);

// total 4 errors

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Class Application\\\\Helper\\\\Javascript extends deprecated class Laminas\\\\View\\\\Helper\\\\AbstractHelper\\:
Since 2\\.40\\.0\\. This class will be remove in 3\\.0 without replacement\\. View helpers should be constructed
            with their dependencies, therefore the setters and getters here become irrelevant\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Helper/Javascript.php',
];
$ignoreErrors[] = [
    'message' => '#^Class Application\\\\Helper\\\\SendToHieHelper extends deprecated class Laminas\\\\View\\\\Helper\\\\AbstractHelper\\:
Since 2\\.40\\.0\\. This class will be remove in 3\\.0 without replacement\\. View helpers should be constructed
            with their dependencies, therefore the setters and getters here become irrelevant\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Helper/SendToHieHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Class Application\\\\Helper\\\\TranslatorViewHelper extends deprecated class Laminas\\\\View\\\\Helper\\\\AbstractHelper\\:
Since 2\\.40\\.0\\. This class will be remove in 3\\.0 without replacement\\. View helpers should be constructed
            with their dependencies, therefore the setters and getters here become irrelevant\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Helper/TranslatorViewHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Class OpenEMR\\\\Tests\\\\Services\\\\DocumentTest extends deprecated class Monolog\\\\Test\\\\TestCase\\:
use MonologTestCase instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/DocumentTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
