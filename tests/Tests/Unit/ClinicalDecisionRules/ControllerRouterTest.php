<?php

namespace OpenEMR\Tests\Unit\ClinicalDecisionRules;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenEMR\ClinicalDecisionRules\Interface\ControllerRouter;
use OpenEMR\ClinicalDecisionRules\Interface\ActionRouter;

class ControllerRouterTest extends TestCase
{
    public function testRouteWithBrowseListAction()
    {
        // Mocking the OpenEMR\ClinicalDecisionRules\Interface\ActionRouter
        $mockActionRouter = $this->createMock(ActionRouter::class);

        // Expect that route() will be called and it returns a Response
        $mockActionRouter->expects($this->once())
            ->method('route')
            ->willReturn(new Response('OpenEMR\ClinicalDecisionRules\Interface\ActionRouter response content'));

        // Mock the OpenEMR\ClinicalDecisionRules\Interface\ActionRouter class creation in OpenEMR\ClinicalDecisionRules\Interface\ControllerRouter
        $controllerRouter = $this->getMockBuilder(ControllerRouter::class)
            ->onlyMethods(['createActionRouter'])
            ->getMock();

        $controllerRouter->expects($this->once())
            ->method('createActionRouter')
            ->willReturn($mockActionRouter);

        // Creating a Request with the query parameter "action=browse!list"
        $request = new Request(['action' => 'browse!list']);

        // Run the route method
        $response = $controllerRouter->route($request);

        // Assert the response
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('OpenEMR\ClinicalDecisionRules\Interface\ActionRouter response content', $response->getContent());
    }

    protected function createActionRouter($controller, $action, $controllerDir)
    {
        return new ActionRouter($controller, $action, $controllerDir);
    }
}
