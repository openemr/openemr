<?php

declare(strict_types=1);

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ServiceManager\Test;

use Laminas\ServiceManager\Exception\InvalidServiceException;
use ReflectionClass;
use ReflectionProperty;

use function get_class;
use function method_exists;

/**
 * Trait for testing plugin managers for v2-v3 compatibility
 *
 * To use this trait:
 *   * implement the `getPluginManager()` method to return your plugin manager
 *   * implement the `getV2InvalidPluginException()` method to return the class `validatePlugin()` throws under v2
 */
trait CommonPluginManagerTrait
{
    public function testInstanceOfMatches()
    {
        $manager = $this->getPluginManager();
        $reflection = new ReflectionProperty($manager, 'instanceOf');
        $reflection->setAccessible(true);
        $this->assertEquals($this->getInstanceOf(), $reflection->getValue($manager), 'instanceOf does not match');
    }

    public function testShareByDefaultAndSharedByDefault()
    {
        $manager = $this->getPluginManager();
        $reflection = new ReflectionClass($manager);
        $shareByDefault = $sharedByDefault = true;

        foreach ($reflection->getProperties() as $prop) {
            if ($prop->getName() == 'shareByDefault') {
                $prop->setAccessible(true);
                $shareByDefault = $prop->getValue($manager);
            }
            if ($prop->getName() == 'sharedByDefault') {
                $prop->setAccessible(true);
                $sharedByDefault = $prop->getValue($manager);
            }
        }

        $this->assertTrue(
            $shareByDefault == $sharedByDefault,
            'Values of shareByDefault and sharedByDefault do not match'
        );
    }

    public function testRegisteringInvalidElementRaisesException()
    {
        $this->expectException($this->getServiceNotFoundException());
        $this->getPluginManager()->setService('test', $this);
    }

    public function testLoadingInvalidElementRaisesException()
    {
        $manager = $this->getPluginManager();
        $manager->setInvokableClass('test', get_class($this));
        $this->expectException($this->getServiceNotFoundException());
        $manager->get('test');
    }

    /**
     * @dataProvider aliasProvider
     */
    public function testPluginAliasesResolve($alias, $expected)
    {
        $this->assertInstanceOf($expected, $this->getPluginManager()->get($alias), "Alias '$alias' does not resolve'");
    }

    public function aliasProvider()
    {
        $manager = $this->getPluginManager();
        $reflection = new ReflectionProperty($manager, 'aliases');
        $reflection->setAccessible(true);
        $data = [];
        foreach ($reflection->getValue($manager) as $alias => $expected) {
            $data[] = [$alias, $expected];
        }
        return $data;
    }

    protected function getServiceNotFoundException()
    {
        $manager = $this->getPluginManager();
        if (method_exists($manager, 'configure')) {
            return InvalidServiceException::class;
        }
        return $this->getV2InvalidPluginException();
    }

    /**
     * Returns the plugin manager to test
     * @return \Laminas\ServiceManager\AbstractPluginManager
     */
    abstract protected function getPluginManager();

    /**
     * Returns the FQCN of the exception thrown under v2 by `validatePlugin()`
     * @return mixed
     */
    abstract protected function getV2InvalidPluginException();

    /**
     * Returns the value the instanceOf property has been set to
     * @return string
     */
    abstract protected function getInstanceOf();
}
