<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Unsafe access to private constant Symfony\\\\Component\\\\Cache\\\\Adapter\\\\CouchbaseBucketAdapter\\:\\:KEY_NOT_FOUND through static\\:\\:\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/cache/Adapter/CouchbaseBucketAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Unsafe access to private constant Symfony\\\\Component\\\\Cache\\\\Adapter\\\\CouchbaseBucketAdapter\\:\\:MAX_KEY_LENGTH through static\\:\\:\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/cache/Adapter/CouchbaseBucketAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Unsafe access to private constant Symfony\\\\Component\\\\Cache\\\\Adapter\\\\CouchbaseBucketAdapter\\:\\:THIRTY_DAYS_IN_SECONDS through static\\:\\:\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/cache/Adapter/CouchbaseBucketAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Unsafe access to private constant Symfony\\\\Component\\\\Cache\\\\Adapter\\\\CouchbaseBucketAdapter\\:\\:VALID_DSN_OPTIONS through static\\:\\:\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/cache/Adapter/CouchbaseBucketAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Unsafe access to private constant Symfony\\\\Component\\\\Cache\\\\Adapter\\\\CouchbaseCollectionAdapter\\:\\:MAX_KEY_LENGTH through static\\:\\:\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/cache/Adapter/CouchbaseCollectionAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Unsafe access to private constant Symfony\\\\Component\\\\HttpKernel\\\\EventListener\\\\DisallowRobotsIndexingListener\\:\\:HEADER_NAME through static\\:\\:\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/http-kernel/EventListener/DisallowRobotsIndexingListener.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
