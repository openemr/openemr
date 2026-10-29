<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$constraint of method Doctrine\\\\DBAL\\\\Platforms\\\\AbstractPlatform\\:\\:getDropConstraintSQL\\(\\) has typehint with deprecated interface Doctrine\\\\DBAL\\\\Schema\\\\Constraint\\:
Use \\{@see ForeignKeyConstraint\\}, \\{@see Index\\} or \\{@see UniqueConstraint\\} instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Platforms/AbstractPlatform.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$attributeClassName of method Doctrine\\\\ORM\\\\Mapping\\\\Driver\\\\AttributeReader\\:\\:isRepeatable\\(\\) has typehint with deprecated interface Doctrine\\\\ORM\\\\Mapping\\\\Annotation\\:
Use \\{@see MappingAttribute\\} instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/orm/src/Mapping/Driver/AttributeReader.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$attributeName of method Doctrine\\\\ORM\\\\Mapping\\\\Driver\\\\AttributeReader\\:\\:getPropertyAttributeCollection\\(\\) has typehint with deprecated interface Doctrine\\\\ORM\\\\Mapping\\\\Annotation\\:
Use \\{@see MappingAttribute\\} instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/orm/src/Mapping/Driver/AttributeReader.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$attributeName of method Doctrine\\\\ORM\\\\Mapping\\\\Driver\\\\AttributeReader\\:\\:getPropertyAttribute\\(\\) has typehint with deprecated interface Doctrine\\\\ORM\\\\Mapping\\\\Annotation\\:
Use \\{@see MappingAttribute\\} instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/orm/src/Mapping/Driver/AttributeReader.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$generator of method Symfony\\\\Component\\\\DependencyInjection\\\\Loader\\\\PhpFileLoader\\:\\:__construct\\(\\) has typehint with deprecated interface Symfony\\\\Component\\\\Config\\\\Builder\\\\ConfigBuilderGeneratorInterface\\:
since Symfony 7\\.4$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/dependency-injection/Loader/PhpFileLoader.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
