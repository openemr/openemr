<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Waryway\PhpTraitsLibrary\Hydrator;


/**
 * Class testHydrator
 * @covers Hydrator
 */
class HydratorTest extends TestCase
{
    /**
     * The object under test.
     *
     * @var object|Hydrator
     */
    private $traitObject;

    /**
     * Sets up the fixture.
     *
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        $this->traitObject = $this->getObjectForTrait(Hydrator::class);


    }

    public function testHydrate()
    {
        $this->assertTrue(trait_exists(Hydrator::class), 'Well, for the sake of php and this experiment, it had better be.');

        // Verifying non-strict interaction
        $stdClass = new stdClass();
        $stdClass->randomParameter = 'testValue';
        $this->traitObject->hydrate($stdClass);
        $this->assertEquals('testValue', $this->traitObject->randomParameter, 'Non-strict mode will set the parameter provided.');
        $this->assertFalse($this->traitObject->strict, 'Not expecting strict mode at this point.');
    }

    public function testHydrate_Strict(){
        // Verifying strict interaction
        $stdClass = new stdClass();
        $stdClass->randomParameter = 'testValue';

        try {
            $this->traitObject->hydrate($stdClass, true);
            $this->fail('Expected an exception when trying to pass an unexpected stdClass to hydrate.');
        } catch (Exception $e){
            $this->assertEquals('Strictly Speaking, the input was too much.' . print_r((array)$stdClass, true), $e->getMessage(), 'Any exception of any other message is still an exception. Make sure we got the right message');
        }

        $this->assertTrue($this->traitObject->strict, 'Expected strict mode at this point.');
    }
}
