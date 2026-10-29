<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Return type of method Doctrine\\\\ORM\\\\Mapping\\\\Driver\\\\AttributeReader\\:\\:getPropertyAttributeCollection\\(\\) has typehint with deprecated interface Doctrine\\\\ORM\\\\Mapping\\\\Annotation\\:
Use \\{@see MappingAttribute\\} instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/orm/src/Mapping/Driver/AttributeReader.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type of method Doctrine\\\\ORM\\\\Mapping\\\\Driver\\\\AttributeReader\\:\\:getPropertyAttribute\\(\\) has typehint with deprecated interface Doctrine\\\\ORM\\\\Mapping\\\\Annotation\\:
Use \\{@see MappingAttribute\\} instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/orm/src/Mapping/Driver/AttributeReader.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type of method Doctrine\\\\ORM\\\\Proxy\\\\ProxyFactory\\:\\:getProxy\\(\\) has typehint with deprecated interface Doctrine\\\\Common\\\\Proxy\\\\Proxy\\:
The Proxy interface is deprecated since doctrine/common 3\\.5\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/orm/src/Proxy/ProxyFactory.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type of method Symfony\\\\Component\\\\DependencyInjection\\\\Loader\\\\PhpFileLoader\\:\\:configBuilder\\(\\) has typehint with deprecated interface Symfony\\\\Component\\\\Config\\\\Builder\\\\ConfigBuilderInterface\\:
since Symfony 7\\.4$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/dependency-injection/Loader/PhpFileLoader.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
