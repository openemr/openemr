<?php

/**
 * ControllerRoutingTest class
 *
 * Tests that Controller::dispatch() routing is order-independent.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\RestControllers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Integration test for Controller routing.
 *
 * Tests that the dispatch() method routes correctly regardless of parameter order.
 * This addresses the root cause of issue #11151 where URLSearchParams produced
 * parameters in a different order than act() expected.
 *
 * @see https://github.com/openemr/openemr/issues/11162
 */
class ControllerRoutingTest extends TestCase
{
    /**
     * Test that dispatch() extracts controller and action regardless of parameter order.
     *
     * The legacy act() method is sensitive to parameter order (first key = controller,
     * second key = action). The new dispatch() method uses explicit 'controller' and
     * 'action' parameters, making it order-independent.
     */
    /**
     * @param array<string, string> $params
     */
    #[DataProvider('parameterOrderProvider')]
    #[Test]
    public function testDispatchExtractsControllerRegardlessOfOrder(array $params): void
    {
        // Use reflection to test the parameter extraction logic without
        // actually loading controller files or requiring authentication
        $controller = $this->createPartialMock(\Controller::class, ['i_once']);

        // Mock i_once to return false (simulates controller file not found)
        // This lets us verify the extracted controller name without side effects
        $controller->method('i_once')->willReturn(false);

        // The exception message should reference 'Document' controller regardless of param order
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Document');

        $controller->dispatch($params);
    }

    /**
     * Provide parameter arrays with controller/action in different positions.
     *
     * @return array<string, array{array<string, string>}>
     */
    public static function parameterOrderProvider(): array
    {
        return [
            'controller first' => [
                ['controller' => 'document', 'action' => 'list', 'patient_id' => '1'],
            ],
            'action first' => [
                ['action' => 'list', 'controller' => 'document', 'patient_id' => '1'],
            ],
            'patient_id first' => [
                ['patient_id' => '1', 'controller' => 'document', 'action' => 'list'],
            ],
            'controller last' => [
                ['patient_id' => '1', 'action' => 'list', 'controller' => 'document'],
            ],
            'mixed order' => [
                ['action' => 'view', 'patient_id' => '1', 'doc_id' => '123', 'controller' => 'document'],
            ],
        ];
    }

    /**
     * Test that legacy act() fails when parameters are in wrong order.
     *
     * This documents the bug that dispatch() fixes. When the first parameter
     * is not the controller name, act() tries to load a non-existent controller.
     */
    #[Test]
    public function testActFailsWithWrongParameterOrder(): void
    {
        $controller = $this->createPartialMock(\Controller::class, ['i_once']);
        $controller->method('i_once')->willReturn(false);

        // Wrong order: patient_id first means act() will try to load C_PatientId
        $params = ['patient_id' => '1', 'document' => '', 'list' => ''];

        // act() incorrectly interprets 'patient_id' as the controller name,
        // which is not in the VALID_CONTROLLERS whitelist
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage("Missing or invalid 'controller' parameter");

        $controller->act($params);
    }

    /**
     * Test that dispatch() requires explicit controller parameter.
     */
    #[Test]
    public function testDispatchRequiresControllerParameter(): void
    {
        $controller = new \Controller();

        // Missing controller parameter
        $params = ['action' => 'list', 'patient_id' => '1'];

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage("Missing or invalid 'controller' parameter");

        $controller->dispatch($params);
    }

    /**
     * Test that dispatch() moves 'process' param from GET to POST.
     */
    #[Test]
    public function testDispatchMovesProcessParamToPost(): void
    {
        $controller = $this->createPartialMock(\Controller::class, ['i_once']);
        $controller->method('i_once')->willReturn(false);

        $params = ['controller' => 'pharmacy', 'action' => 'list', 'process' => 'true'];
        $_GET['process'] = 'true';

        try {
            $controller->dispatch($params);
        } catch (NotFoundHttpException) {
            // Expected — i_once returns false
        }

        $this->assertArrayNotHasKey('process', $_GET);
        $this->assertSame('true', $_POST['process'] ?? null);
    }

    /**
     * Test that act() preserves explicit 'action' param as sub_action for delegation.
     *
     * URLs like /controller.php?practice_settings&pharmacy&action=list use a
     * sub-controller pattern where:
     * - practice_settings = controller
     * - pharmacy = action (calls pharmacy_action())
     * - action=list = sub-action (passed as argument to pharmacy_action)
     *
     * The explicit 'action=list' must not be lost due to key collision with the
     * positional action parameter.
     */
    #[Test]
    public function testActPreservesExplicitActionAsSubAction(): void
    {
        /** @var array<string, mixed> $dispatchParams */
        $dispatchParams = [];
        $controller = $this->createPartialMock(\Controller::class, ['dispatch']);
        $controller->method('dispatch')->willReturnCallback(function (array $params) use (&$dispatchParams) {
            $dispatchParams = $params;
            return '';
        });

        // Simulate URL: ?practice_settings&pharmacy&action=list
        $qarray = ['practice_settings' => '', 'pharmacy' => '', 'action' => 'list'];
        $controller->act($qarray); // @phpstan-ignore method.deprecated (testing deprecated method)

        // Verify controller and action from positional params
        $this->assertSame('practice_settings', $dispatchParams['controller']);
        $this->assertSame('pharmacy', $dispatchParams['action']);

        // Verify explicit 'action=list' is preserved as 'sub_action'
        $this->assertArrayHasKey('sub_action', $dispatchParams);
        $this->assertSame('list', $dispatchParams['sub_action']);
    }

    /**
     * Controller extends Smarty, and Smarty's __call catches any undefined
     * method call. This makes is_callable() return true for every method
     * name on a Controller instance, even methods that don't actually exist.
     *
     * The methodExists() guard must combine is_callable() with method_exists()
     * to distinguish genuinely-defined methods from phantom calls that would
     * otherwise be dispatched into Smarty's extension handler (which throws
     * "undefined extension class Smarty_Internal_Method_*" errors).
     */
    #[Test]
    public function testMethodExistsGuardsAgainstSmartyPhantomMethods(): void
    {
        $smartyDescendant = new class extends \Controller {
        };

        $dispatcher = new \Controller();

        $methodExists = new \ReflectionMethod(\Controller::class, 'methodExists');

        // Document the trap: is_callable() alone returns true for any method
        // name on a Smarty descendant because Smarty's __call catches all calls.
        // PHPStan can't model __call, so it concludes statically that
        // is_callable() must be false — which is exactly the runtime trap
        // this test documents and methodExists() guards against.
        /** @phpstan-ignore-next-line function.impossibleType */
        $isCallable = is_callable([$smartyDescendant, 'nonexistent_phantom_method']);
        /** @phpstan-ignore-next-line method.impossibleType */
        $this->assertTrue(
            $isCallable,
            'Expected is_callable() to return true due to Smarty __call ' .
            '(this assertion documents the trap that methodExists() guards against).'
        );

        // The guard must return false for phantom methods that only __call catches.
        $this->assertFalse(
            $methodExists->invoke($dispatcher, $smartyDescendant, 'nonexistent_phantom_method'),
            'methodExists() must return false for phantom methods caught only by __call.'
        );

        // Positive case: a genuinely-defined method should return true.
        $this->assertTrue(
            $methodExists->invoke($dispatcher, $smartyDescendant, 'process_action'),
            'methodExists() must return true for genuinely-defined methods.'
        );
    }
}
