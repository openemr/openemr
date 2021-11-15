<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Waryway\PhpTraitsLibrary\Generator;


/**
 * Class testGenerator
 * @covers Generator
 */
class GeneratorTest extends TestCase
{
    /**
     * The object under test.
     *
     * @var object|Generator
     */
    private $traitObject;

    /**
     * @var array[string]
     */
    private $fileAsArray = [
        '1,header',
        '2,body start',
        '3,body end',
        '4,footer'
    ];

    /**
     * Sets up the fixture, basically, creating a file for testing
     *
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        $this->traitObject = $this->getObjectForTrait(Generator::class);
        try {
            file_put_contents(__DIR__ . 'testFile.txt', implode(PHP_EOL, $this->fileAsArray));
        } catch (Exception $e) {
            echo 'Unable to modify testFile.txt' . PHP_EOL;
            print_r($e);
        }
        $this->assertTrue(file_exists(__DIR__ . 'testFile.txt'), 'Make certain the file is present.');
    }

    /**
     * Basically, removes the file used for testing.
     */
    public function tearDown()
    {
        try {
            unlink(__DIR__ . 'testFile.txt');
        } catch (Exception $e) {
            echo 'Unable to remove testFile.txt' . PHP_EOL;
            print_r($e);
        }
        $this->assertFalse(file_exists(__DIR__ . 'testFile.txt'), 'Make certain the file is gone.');
    }

    /**
     * Silly check for the trait.
     */
    public function testCheckTraitExistence(){
        $this->assertTrue(trait_exists(Generator::class), 'Well, for the sake of php and this experiment, it had better be.');
    }

    /**
     * Check the 'strait' line return.
     */
    public function testFileLineGenerator_Default()
    {
        $reflectionObject = new ReflectionObject($this->traitObject);
        $reflectionMethod = $reflectionObject->getMethod('FileLineGenerator');
        $reflectionMethod->setAccessible(true);
        $generator = $reflectionMethod->invokeArgs($this->traitObject, [__DIR__ . 'testFile.txt', null]);
        foreach($generator as $i => $line) {
            $this->assertEquals($this->fileAsArray[$i], rtrim($line, PHP_EOL),'Expected a matching line');
        }
    }

    /**
     * Check the 'callable' line return.
     */
    public function testFileLineGenerator_Formatter()
    {
        $reflectionObject = new ReflectionObject($this->traitObject);
        $reflectionMethod = $reflectionObject->getMethod('FileLineGenerator');
        $reflectionMethod->setAccessible(true);
        $generator = $reflectionMethod->invokeArgs($this->traitObject, [__DIR__ . 'testFile.txt', function ($input){return explode(',',rtrim($input,PHP_EOL));}]);
        foreach($generator as $i => $line) {
            $this->assertEquals(explode(',', $this->fileAsArray[$i]), $line,'Expected a matching line');
        }
    }
}
