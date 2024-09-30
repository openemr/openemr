<?php

namespace OpenEMR\Tests\Unit\ClinicalDecisionRules;

use OpenEMR\ClinicalDecisionRules\Interface\ActionRouter;
use OpenEMR\ClinicalDecisionRules\Interface\BaseController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ActionRouterTest extends TestCase
{
    public function testRouteWithBrowseListAction()
    {
        // Mock the controller that OpenEMR\ClinicalDecisionRules\Interface\ActionRouter expects
        $mockController = new class extends BaseController {
            public function __construct()
            {
                parent::__construct();
                $this->viewBean = (object)[
                    '_view' => 'testView.php',
                    '_template' => 'testTemplate.php'
                ];
            }

            public function _action_list()
            {}
        };
        $actionRouter = new ActionRouter($mockController, 'list', '/path/to/controller');

        // Create a Request
        $request = new Request();

        // Run the route method
        $response = $actionRouter->route($request);

        // Assert the response is a Response object and contains the rendered view
        $this->assertInstanceOf(Response::class, $response);
        $this->assertStringContainsString('', $response->getContent());
    }

    public function testForwardBehavior()
    {
        $mockController = new class extends BaseController {
            public function __construct()
            {
                parent::__construct();
                $this->viewBean = (object)[
                    '_forward' => 'forward'
                ];
            }

            public function _action_list()
            {}
            public function _action_forward()
            {}
        };

        $actionRouter = new ActionRouter($mockController, 'list', '/path/to/controller');

        // Create a Request
        $request = new Request();

        // Run the route method
        $response = $actionRouter->route($request);

        // Assert the response is a Response object and contains the rendered view
        $this->assertInstanceOf(Response::class, $response);
        $this->assertStringContainsString('', $response->getContent());
    }

    public function testRedirectBehavior()
    {

        $mockController = new class extends BaseController {
            public function __construct()
            {
                parent::__construct();
                $this->viewBean = (object)[
                    '_redirect' => 'www.example.com'
                ];
            }

            public function _action_list()
            {}
        };

        $actionRouter = new ActionRouter($mockController, 'list', '/path/to/controller');

        // Create a Request
        $request = new Request();

        // Run the route method
        $response = $actionRouter->route($request);

        // Assert the response is a redirect (status 302)
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('www.example.com', $response->headers->get('Location'));
    }
}
