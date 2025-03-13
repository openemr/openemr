<?php
/**
 *
 */

use Google\Service\Books;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Modules\FormTemplates\Bootstrap;
use OpenEMR\Modules\FormTemplates\Controller;
use Symfony\Component\HttpFoundation\Request;
use Twig\Extension\DebugExtension;

require_once "../../../globals.php";
require_once "../../../../library/registry.inc.php";

// Control access
if (!AclMain::aclCheckCore('patients', 'demo')) {
    echo xlt('Not Authorized');
    exit;
}

$templateDir = Bootstrap::getTemplatePath();
$twigContainer = new TwigContainer($templateDir);
$twig = $twigContainer->getTwig();
$twig->addExtension(new DebugExtension());
$twig->enableDebug();

$request = Request::createFromGlobals();

$r_controller = $request->get('controller', 'default');
$r_action = $request->get('action', 'index');

$routes = [
    'default'       => 'OpenEMR\Modules\FormTemplates\Controller\ConfigurationController',
    'configuration' => 'OpenEMR\Modules\FormTemplates\Controller\ConfigurationController',
];

if (array_key_exists($r_controller, $routes) == true) {
    $reflection = new ReflectionClass($routes[$r_controller]);

    if ($reflection->hasMethod($r_action)) {
        /**
         * @var Controller
         */
        $instance = $reflection->newInstance();
        $results = call_user_func([$instance, $r_action]);
        $results['mod_index'] = Bootstrap::MODULE_INSTALLATION_PATH . Bootstrap::MODULE_MACHINE_NAME . '/index.php';
        echo $twig->render($instance->getTemplateName(), $results);
    }

}
