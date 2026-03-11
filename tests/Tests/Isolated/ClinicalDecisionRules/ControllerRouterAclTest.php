<?php

namespace OpenEMR\Tests\Isolated\ClinicalDecisionRules;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use OpenEMR\ClinicalDecisionRules\Interface\ControllerRouter;

class ControllerRouterAclTest extends TestCase
{
    /**
     * Controllers that handle their own ACL and skip the router-level admin check.
     *
     * @return array<string, array{string}>
     */
    public static function selfProtectingControllerProvider(): array
    {
        return [
            'review' => ['review'],
            'log' => ['log'],
        ];
    }

    /**
     * Controllers that require admin/super ACL at the router level.
     *
     * @return array<string, array{string}>
     */
    public static function adminProtectedControllerProvider(): array
    {
        return [
            'alerts' => ['alerts'],
            'ajax' => ['ajax'],
            'edit' => ['edit'],
            'add' => ['add'],
            'detail' => ['detail'],
            'browse' => ['browse'],
        ];
    }

    #[DataProvider('selfProtectingControllerProvider')]
    public function testShouldSkipAdminAclReturnsTrueForSelfProtectingControllers(string $controller): void
    {
        $router = new ControllerRouter();
        $this->assertTrue($router->shouldSkipAdminAcl($controller));
    }

    #[DataProvider('adminProtectedControllerProvider')]
    public function testShouldSkipAdminAclReturnsFalseForAdminProtectedControllers(string $controller): void
    {
        $router = new ControllerRouter();
        $this->assertFalse($router->shouldSkipAdminAcl($controller));
    }
}
