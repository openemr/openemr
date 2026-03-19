<?php declare(strict_types = 1);

// total 3 errors

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Call to method getView\\(\\) of deprecated class Laminas\\\\View\\\\Helper\\\\AbstractHelper\\:
Since 2\\.40\\.0\\. This class will be remove in 3\\.0 without replacement\\. View helpers should be constructed
            with their dependencies, therefore the setters and getters here become irrelevant\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Helper/SendToHieHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method __construct\\(\\) of deprecated class Ramsey\\\\Uuid\\\\Generator\\\\CombGenerator\\:
Please migrate to \\{@link https\\://uuid\\.ramsey\\.dev/en/stable/rfc4122/version7\\.html Version 7, Unix Epoch Time UUIDs\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Uuid/UuidRegistry.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
