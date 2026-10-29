<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Access to constant on deprecated interface Doctrine\\\\ORM\\\\Mapping\\\\Annotation\\:
Use \\{@see MappingAttribute\\} instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/orm/src/Mapping/Driver/AttributeReader.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to constant on deprecated interface phpDocumentor\\\\Reflection\\\\DocBlock\\\\Tags\\\\Factory\\\\StaticMethod\\:
This contract is totally covered by Tag contract\\. Every class using StaticMethod also use Tag$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/property-info/Extractor/PhpDocExtractor.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to constant on deprecated interface Symfony\\\\Component\\\\Config\\\\Builder\\\\ConfigBuilderInterface\\:
since Symfony 7\\.4$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/dependency-injection/Loader/PhpFileLoader.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
