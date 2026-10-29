<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Class Doctrine\\\\DBAL\\\\Driver\\\\AbstractDB2Driver implements deprecated interface Doctrine\\\\DBAL\\\\VersionAwarePlatformDriver\\:
All drivers will have to be aware of the server version in the next major release\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Driver/AbstractDB2Driver.php',
];
$ignoreErrors[] = [
    'message' => '#^Class Doctrine\\\\DBAL\\\\Driver\\\\AbstractMySQLDriver implements deprecated interface Doctrine\\\\DBAL\\\\VersionAwarePlatformDriver\\:
All drivers will have to be aware of the server version in the next major release\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Driver/AbstractMySQLDriver.php',
];
$ignoreErrors[] = [
    'message' => '#^Class Doctrine\\\\DBAL\\\\Driver\\\\AbstractPostgreSQLDriver implements deprecated interface Doctrine\\\\DBAL\\\\VersionAwarePlatformDriver\\:
All drivers will have to be aware of the server version in the next major release\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Driver/AbstractPostgreSQLDriver.php',
];
$ignoreErrors[] = [
    'message' => '#^Class Doctrine\\\\DBAL\\\\Driver\\\\IBMDB2\\\\Connection implements deprecated interface Doctrine\\\\DBAL\\\\Driver\\\\ServerInfoAwareConnection\\:
The methods defined in this interface will be made part of the \\{@see Driver\\} interface
in the next major release\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Driver/IBMDB2/Connection.php',
];
$ignoreErrors[] = [
    'message' => '#^Class Doctrine\\\\DBAL\\\\Driver\\\\Middleware\\\\AbstractConnectionMiddleware implements deprecated interface Doctrine\\\\DBAL\\\\Driver\\\\ServerInfoAwareConnection\\:
The methods defined in this interface will be made part of the \\{@see Driver\\} interface
in the next major release\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Driver/Middleware/AbstractConnectionMiddleware.php',
];
$ignoreErrors[] = [
    'message' => '#^Class Doctrine\\\\DBAL\\\\Driver\\\\Middleware\\\\AbstractDriverMiddleware implements deprecated interface Doctrine\\\\DBAL\\\\VersionAwarePlatformDriver\\:
All drivers will have to be aware of the server version in the next major release\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Driver/Middleware/AbstractDriverMiddleware.php',
];
$ignoreErrors[] = [
    'message' => '#^Class Doctrine\\\\DBAL\\\\Driver\\\\Mysqli\\\\Connection implements deprecated interface Doctrine\\\\DBAL\\\\Driver\\\\ServerInfoAwareConnection\\:
The methods defined in this interface will be made part of the \\{@see Driver\\} interface
in the next major release\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Driver/Mysqli/Connection.php',
];
$ignoreErrors[] = [
    'message' => '#^Class Doctrine\\\\DBAL\\\\Driver\\\\OCI8\\\\Connection implements deprecated interface Doctrine\\\\DBAL\\\\Driver\\\\ServerInfoAwareConnection\\:
The methods defined in this interface will be made part of the \\{@see Driver\\} interface
in the next major release\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Driver/OCI8/Connection.php',
];
$ignoreErrors[] = [
    'message' => '#^Class Doctrine\\\\DBAL\\\\Driver\\\\PDO\\\\Connection implements deprecated interface Doctrine\\\\DBAL\\\\Driver\\\\ServerInfoAwareConnection\\:
The methods defined in this interface will be made part of the \\{@see Driver\\} interface
in the next major release\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Driver/PDO/Connection.php',
];
$ignoreErrors[] = [
    'message' => '#^Class Doctrine\\\\DBAL\\\\Driver\\\\PgSQL\\\\Connection implements deprecated interface Doctrine\\\\DBAL\\\\Driver\\\\ServerInfoAwareConnection\\:
The methods defined in this interface will be made part of the \\{@see Driver\\} interface
in the next major release\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Driver/PgSQL/Connection.php',
];
$ignoreErrors[] = [
    'message' => '#^Class Doctrine\\\\DBAL\\\\Driver\\\\SQLSrv\\\\Connection implements deprecated interface Doctrine\\\\DBAL\\\\Driver\\\\ServerInfoAwareConnection\\:
The methods defined in this interface will be made part of the \\{@see Driver\\} interface
in the next major release\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Driver/SQLSrv/Connection.php',
];
$ignoreErrors[] = [
    'message' => '#^Class Doctrine\\\\DBAL\\\\Driver\\\\SQLite3\\\\Connection implements deprecated interface Doctrine\\\\DBAL\\\\Driver\\\\ServerInfoAwareConnection\\:
The methods defined in this interface will be made part of the \\{@see Driver\\} interface
in the next major release\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Driver/SQLite3/Connection.php',
];
$ignoreErrors[] = [
    'message' => '#^Class Doctrine\\\\DBAL\\\\Schema\\\\ForeignKeyConstraint implements deprecated interface Doctrine\\\\DBAL\\\\Schema\\\\Constraint\\:
Use \\{@see ForeignKeyConstraint\\}, \\{@see Index\\} or \\{@see UniqueConstraint\\} instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Schema/ForeignKeyConstraint.php',
];
$ignoreErrors[] = [
    'message' => '#^Class Doctrine\\\\DBAL\\\\Schema\\\\Index implements deprecated interface Doctrine\\\\DBAL\\\\Schema\\\\Constraint\\:
Use \\{@see ForeignKeyConstraint\\}, \\{@see Index\\} or \\{@see UniqueConstraint\\} instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Schema/Index.php',
];
$ignoreErrors[] = [
    'message' => '#^Class Doctrine\\\\DBAL\\\\Schema\\\\UniqueConstraint implements deprecated interface Doctrine\\\\DBAL\\\\Schema\\\\Constraint\\:
Use \\{@see ForeignKeyConstraint\\}, \\{@see Index\\} or \\{@see UniqueConstraint\\} instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Schema/UniqueConstraint.php',
];
$ignoreErrors[] = [
    'message' => '#^Class phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Author implements deprecated interface phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Factory\\\\StaticMethod\\:
This contract is totally covered by Tag contract\\. Every class using StaticMethod also use Tag$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/phpdocumentor/reflection-docblock/src/DocBlock/Tags/Author.php',
];
$ignoreErrors[] = [
    'message' => '#^Class phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Covers implements deprecated interface phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Factory\\\\StaticMethod\\:
This contract is totally covered by Tag contract\\. Every class using StaticMethod also use Tag$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/phpdocumentor/reflection-docblock/src/DocBlock/Tags/Covers.php',
];
$ignoreErrors[] = [
    'message' => '#^Class phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Deprecated implements deprecated interface phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Factory\\\\StaticMethod\\:
This contract is totally covered by Tag contract\\. Every class using StaticMethod also use Tag$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/phpdocumentor/reflection-docblock/src/DocBlock/Tags/Deprecated.php',
];
$ignoreErrors[] = [
    'message' => '#^Class phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Example implements deprecated interface phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Factory\\\\StaticMethod\\:
This contract is totally covered by Tag contract\\. Every class using StaticMethod also use Tag$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/phpdocumentor/reflection-docblock/src/DocBlock/Tags/Example.php',
];
$ignoreErrors[] = [
    'message' => '#^Class phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Generic implements deprecated interface phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Factory\\\\StaticMethod\\:
This contract is totally covered by Tag contract\\. Every class using StaticMethod also use Tag$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/phpdocumentor/reflection-docblock/src/DocBlock/Tags/Generic.php',
];
$ignoreErrors[] = [
    'message' => '#^Class phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Link implements deprecated interface phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Factory\\\\StaticMethod\\:
This contract is totally covered by Tag contract\\. Every class using StaticMethod also use Tag$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/phpdocumentor/reflection-docblock/src/DocBlock/Tags/Link.php',
];
$ignoreErrors[] = [
    'message' => '#^Class phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Method implements deprecated interface phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Factory\\\\StaticMethod\\:
This contract is totally covered by Tag contract\\. Every class using StaticMethod also use Tag$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/phpdocumentor/reflection-docblock/src/DocBlock/Tags/Method.php',
];
$ignoreErrors[] = [
    'message' => '#^Class phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Mixin implements deprecated interface phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Factory\\\\StaticMethod\\:
This contract is totally covered by Tag contract\\. Every class using StaticMethod also use Tag$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/phpdocumentor/reflection-docblock/src/DocBlock/Tags/Mixin.php',
];
$ignoreErrors[] = [
    'message' => '#^Class phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Param implements deprecated interface phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Factory\\\\StaticMethod\\:
This contract is totally covered by Tag contract\\. Every class using StaticMethod also use Tag$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/phpdocumentor/reflection-docblock/src/DocBlock/Tags/Param.php',
];
$ignoreErrors[] = [
    'message' => '#^Class phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Property implements deprecated interface phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Factory\\\\StaticMethod\\:
This contract is totally covered by Tag contract\\. Every class using StaticMethod also use Tag$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/phpdocumentor/reflection-docblock/src/DocBlock/Tags/Property.php',
];
$ignoreErrors[] = [
    'message' => '#^Class phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\PropertyRead implements deprecated interface phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Factory\\\\StaticMethod\\:
This contract is totally covered by Tag contract\\. Every class using StaticMethod also use Tag$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/phpdocumentor/reflection-docblock/src/DocBlock/Tags/PropertyRead.php',
];
$ignoreErrors[] = [
    'message' => '#^Class phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\PropertyWrite implements deprecated interface phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Factory\\\\StaticMethod\\:
This contract is totally covered by Tag contract\\. Every class using StaticMethod also use Tag$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/phpdocumentor/reflection-docblock/src/DocBlock/Tags/PropertyWrite.php',
];
$ignoreErrors[] = [
    'message' => '#^Class phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Return_ implements deprecated interface phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Factory\\\\StaticMethod\\:
This contract is totally covered by Tag contract\\. Every class using StaticMethod also use Tag$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/phpdocumentor/reflection-docblock/src/DocBlock/Tags/Return_.php',
];
$ignoreErrors[] = [
    'message' => '#^Class phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\See implements deprecated interface phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Factory\\\\StaticMethod\\:
This contract is totally covered by Tag contract\\. Every class using StaticMethod also use Tag$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/phpdocumentor/reflection-docblock/src/DocBlock/Tags/See.php',
];
$ignoreErrors[] = [
    'message' => '#^Class phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Since implements deprecated interface phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Factory\\\\StaticMethod\\:
This contract is totally covered by Tag contract\\. Every class using StaticMethod also use Tag$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/phpdocumentor/reflection-docblock/src/DocBlock/Tags/Since.php',
];
$ignoreErrors[] = [
    'message' => '#^Class phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Source implements deprecated interface phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Factory\\\\StaticMethod\\:
This contract is totally covered by Tag contract\\. Every class using StaticMethod also use Tag$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/phpdocumentor/reflection-docblock/src/DocBlock/Tags/Source.php',
];
$ignoreErrors[] = [
    'message' => '#^Class phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\TemplateCovariant implements deprecated interface phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Factory\\\\StaticMethod\\:
This contract is totally covered by Tag contract\\. Every class using StaticMethod also use Tag$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/phpdocumentor/reflection-docblock/src/DocBlock/Tags/TemplateCovariant.php',
];
$ignoreErrors[] = [
    'message' => '#^Class phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Throws implements deprecated interface phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Factory\\\\StaticMethod\\:
This contract is totally covered by Tag contract\\. Every class using StaticMethod also use Tag$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/phpdocumentor/reflection-docblock/src/DocBlock/Tags/Throws.php',
];
$ignoreErrors[] = [
    'message' => '#^Class phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Uses implements deprecated interface phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Factory\\\\StaticMethod\\:
This contract is totally covered by Tag contract\\. Every class using StaticMethod also use Tag$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/phpdocumentor/reflection-docblock/src/DocBlock/Tags/Uses.php',
];
$ignoreErrors[] = [
    'message' => '#^Class phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Var_ implements deprecated interface phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Factory\\\\StaticMethod\\:
This contract is totally covered by Tag contract\\. Every class using StaticMethod also use Tag$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/phpdocumentor/reflection-docblock/src/DocBlock/Tags/Var_.php',
];
$ignoreErrors[] = [
    'message' => '#^Class phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Version implements deprecated interface phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Factory\\\\StaticMethod\\:
This contract is totally covered by Tag contract\\. Every class using StaticMethod also use Tag$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/phpdocumentor/reflection-docblock/src/DocBlock/Tags/Version.php',
];
$ignoreErrors[] = [
    'message' => '#^Class Symfony\\\\Component\\\\EventDispatcher\\\\Debug\\\\TraceableEventDispatcher implements deprecated interface Symfony\\\\Component\\\\EventDispatcher\\\\Debug\\\\TraceableEventDispatcherInterface\\:
since Symfony 4\\.1$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module/vendor/symfony/event-dispatcher/Debug/TraceableEventDispatcher.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
