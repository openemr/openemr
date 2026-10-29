<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Template type T is declared as covariant, but occurs in invariant position in property Doctrine\\\\DBAL\\\\Schema\\\\AbstractSchemaManager\\:\\:\\$_platform\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Schema/AbstractSchemaManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Template type T is declared as covariant, but occurs in invariant position in property Doctrine\\\\ORM\\\\Mapping\\\\ClassMetadataInfo\\:\\:\\$name\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/orm/src/Mapping/ClassMetadataInfo.php',
];
$ignoreErrors[] = [
    'message' => '#^Template type TObjectManager is declared as covariant, but occurs in invariant position in property Doctrine\\\\Persistence\\\\ObjectManagerDecorator\\:\\:\\$wrapped\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/persistence/src/Persistence/ObjectManagerDecorator.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
