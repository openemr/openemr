<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Return type of method Application\\\\Controller\\\\IndexController\\:\\:ajaxZxlAction\\(\\) has typehint with deprecated class Laminas\\\\View\\\\Model\\\\JsonModel\\:
Since 2\\.40\\.0\\. This class will be removed in 3\\.0 without replacement\\. Laminas\\\\View will no longer support
            rendering strategies in 3\\.0$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Controller/IndexController.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type of method Application\\\\Helper\\\\SendToHieHelper\\:\\:setServiceLocator\\(\\) has typehint with deprecated class Laminas\\\\View\\\\Helper\\\\AbstractHelper\\:
Since 2\\.40\\.0\\. This class will be remove in 3\\.0 without replacement\\. View helpers should be constructed
            with their dependencies, therefore the setters and getters here become irrelevant\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Helper/SendToHieHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type of method Installer\\\\Controller\\\\InstallerController\\:\\:DeleteAclAction\\(\\) has typehint with deprecated class Laminas\\\\View\\\\Model\\\\JsonModel\\:
Since 2\\.40\\.0\\. This class will be removed in 3\\.0 without replacement\\. Laminas\\\\View will no longer support
            rendering strategies in 3\\.0$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type of method Installer\\\\Controller\\\\InstallerController\\:\\:DeleteHooksAction\\(\\) has typehint with deprecated class Laminas\\\\View\\\\Model\\\\JsonModel\\:
Since 2\\.40\\.0\\. This class will be removed in 3\\.0 without replacement\\. Laminas\\\\View will no longer support
            rendering strategies in 3\\.0$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type of method Installer\\\\Controller\\\\InstallerController\\:\\:SaveHooksAction\\(\\) has typehint with deprecated class Laminas\\\\View\\\\Model\\\\JsonModel\\:
Since 2\\.40\\.0\\. This class will be removed in 3\\.0 without replacement\\. Laminas\\\\View will no longer support
            rendering strategies in 3\\.0$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type of method Installer\\\\Controller\\\\InstallerController\\:\\:saveConfigAction\\(\\) has typehint with deprecated class Laminas\\\\View\\\\Model\\\\JsonModel\\:
Since 2\\.40\\.0\\. This class will be removed in 3\\.0 without replacement\\. Laminas\\\\View will no longer support
            rendering strategies in 3\\.0$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
